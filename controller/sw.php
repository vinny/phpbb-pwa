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
     * @param \phpbb\config\config      $config
     * @param \phpbb\controller\helper  $helper
     * @param \phpbb\extension\manager  $ext_manager
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
     * The response must be served with Content-Type: application/javascript.
     * phpBB's controller helper must NOT wrap this in its HTML layout; we
     * build and return a raw Symfony Response to avoid that.
     *
     * @return Response
     */
    public function display()
    {
        $cache_version = (int) $this->config['pwa_cache_version'];
        $offline_url   = $this->helper->route(
            'vinny_pwa_offline',
            [],
            true,
            false,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Strip any query-string phpBB may append (e.g. ?style=1) so the
        // cached URL matches what the browser sends during navigation fallback.
        if (($qs_pos = strpos($offline_url, '?')) !== false) {
            $offline_url = substr($offline_url, 0, $qs_pos);
        }
        
        $webpush_import = '';
        if ($this->ext_manager->is_enabled('phpbb/webpushnotifications')) {
            $push_worker_url = $this->helper->route('phpbb_webpushnotifications_ucp_push_worker_controller', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
            $webpush_import = "importScripts('{$push_worker_url}');\n";
        }

        $sw_js = <<<JS
'use strict';
{$webpush_import}
const CACHE_NAME = 'phpbb-pwa-v{$cache_version}';
const OFFLINE_URL = '{$offline_url}';

// Pre-cache the offline page on install
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.add(OFFLINE_URL))
    );
});

// Remove outdated caches on activation
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(names => Promise.all(
                names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Cache-first for static assets (CSS, JS, images, fonts, icons)
    if (/\.(css|js|png|jpg|jpeg|gif|svg|webp|woff2?|ttf|ico|webmanifest)$/i.test(url.pathname)) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;

                return fetch(event.request).then(res => {
                    if (!res || res.status !== 200 || res.type !== 'basic') return res;

                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return res;
                }).catch(() => { /* ignore asset fetch failures */ });
            })
        );
        return;
    }

    // Network-first for everything else — we never cache phpBB HTML pages to
    // avoid serving stale CSRF tokens or session-bound content.
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
