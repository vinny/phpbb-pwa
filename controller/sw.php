<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class sw
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config     $config
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\extension\manager $ext_manager
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\extension\manager $ext_manager
	) {
		$this->config = $config;
		$this->helper = $helper;
		$this->ext_manager = $ext_manager;
	}

	/**
	 * Return the Service Worker JavaScript.
	 *
	 * @return Response
	 */
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

			return new Response('You are offline.', {
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
