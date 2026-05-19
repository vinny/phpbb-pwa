<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\extension\manager;
use phpbb\language\language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class sw
{
	/** @var config */
	protected $config;

	/** @var helper */
	protected $helper;

	/** @var manager */
	protected $ext_manager;

	/** @var language */
	protected $language;

	public function __construct(
		config $config,
		helper $helper,
		manager $ext_manager,
		language $language
	) {
		$this->config = $config;
		$this->helper = $helper;
		$this->ext_manager = $ext_manager;
		$this->language = $language;
	}

	public function display()
	{
		$cache_version = (int) $this->config['pwa_cache_version'];
		$offline_url = $this->helper->route(
			'vinny_pwa_offline',
			[],
			true,
			false,
			UrlGeneratorInterface::ABSOLUTE_URL
		);

		if (($qs_pos = strpos($offline_url, '?')) !== false)
		{
			$offline_url = substr($offline_url, 0, $qs_pos);
		}

		$webpush_import = '';
		if ($this->ext_manager->is_enabled('phpbb/webpushnotifications'))
		{
			$push_worker_url = $this->helper->route('phpbb_webpushnotifications_ucp_push_worker_controller', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
			$webpush_import = 'importScripts(' . json_encode($push_worker_url) . ");\n";
		}

		$cache_name = 'phpbb-pwa-v' . $cache_version;
		$cache_name_js = json_encode($cache_name);
		$offline_url_js = json_encode($offline_url);
		$this->language->add_lang('pwa', 'vinny/pwa');
		$offline_fallback_js = json_encode($this->language->lang('PWA_OFFLINE_FALLBACK'));

		$sw_js = <<<JS
'use strict';
{$webpush_import}
const CACHE_NAME = {$cache_name_js};
const OFFLINE_URL = {$offline_url_js};
const MAX_CACHE_ITEMS = 120;
const STATIC_ASSET_PATTERN = /\\.(?:css|js|png|jpg|jpeg|gif|svg|webp|woff2?|ttf|ico|webmanifest)$/i;
const SENSITIVE_QUERY_KEYS = ['sid', 'hash', 'mode'];

function hasSensitiveQuery(url) {
	return SENSITIVE_QUERY_KEYS.some(key => url.searchParams.has(key));
}

function isSameOriginHttpRequest(url) {
	return url.origin === self.location.origin && /^https?:$/.test(url.protocol);
}

async function trimCache(cache) {
	const keys = await cache.keys();
	if (keys.length <= MAX_CACHE_ITEMS) {
		return;
	}

	await Promise.all(keys.slice(0, keys.length - MAX_CACHE_ITEMS).map(key => cache.delete(key)));
}

self.addEventListener('install', event => {
	self.skipWaiting();
	event.waitUntil(
		caches.open(CACHE_NAME).then(cache => cache.add(OFFLINE_URL))
	);
});

self.addEventListener('activate', event => {
	event.waitUntil(
		caches.keys()
			.then(names => Promise.all(
				names.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
			))
			.then(() => self.clients.claim())
	);
});

self.addEventListener('message', event => {
	if (event.data && event.data.type === 'SKIP_WAITING') {
		self.skipWaiting();
	}
});

self.addEventListener('fetch', event => {
	if (event.request.method !== 'GET') {
		return;
	}

	const url = new URL(event.request.url);
	if (!isSameOriginHttpRequest(url) || hasSensitiveQuery(url)) {
		return;
	}

	if (STATIC_ASSET_PATTERN.test(url.pathname)) {
		event.respondWith((async () => {
			const cached = await caches.match(event.request);
			if (cached) {
				return cached;
			}

			try {
				const response = await fetch(event.request);
				if (!response || response.status !== 200 || response.type !== 'basic') {
					return response;
				}

				const cache = await caches.open(CACHE_NAME);
				await cache.put(event.request, response.clone());
				await trimCache(cache);

				return response;
			} catch (e) {
				return new Response('', {
					status: 504,
					statusText: 'Gateway Timeout'
				});
			}
		})());
		return;
	}

	event.respondWith(
		fetch(event.request).catch(() => {
			if (event.request.mode === 'navigate') {
				return caches.match(OFFLINE_URL);
			}

			return new Response({$offline_fallback_js}, {
				status: 503,
				headers: { 'Content-Type': 'text/plain' }
			});
		})
	);
});
JS;

		$response = new Response($sw_js, 200);
		$response->headers->set('Content-Type', 'application/javascript; charset=utf-8');
		$response->headers->set('Service-Worker-Allowed', '/');
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
		$response->headers->set('X-Robots-Tag', 'noindex');

		return $response;
	}
}
