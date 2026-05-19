(function() {
	'use strict';

	var options = window.phpbbPwaOptions || {};
	var deferredPrompt = null;
	var dismissedKey = 'pwa_banner_dismissed';

	function isStandalone() {
		return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
	}

	function setPwaModeCookie() {
		if (!options.pwaModeCookieName) {
			return;
		}

		var expireDate = new Date();
		expireDate.setTime(expireDate.getTime() + (365 * 24 * 60 * 60 * 1000));
		document.cookie = encodeURIComponent(options.pwaModeCookieName) + '=1; expires=' + expireDate.toUTCString() + '; path=/; SameSite=Lax';
	}

	function localStorageGet(key) {
		try {
			return window.localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function localStorageSet(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch (e) {
			// Private browsing may block localStorage.
		}
	}

	function hideInstallBanner(banner) {
		if (banner) {
			banner.hidden = true;
		}
	}

	function removeDuplicateManifests() {
		if (!options.manifestUrl) {
			return;
		}

		document.querySelectorAll('link[rel="manifest"]').forEach(function(element) {
			if (element.href.indexOf(options.manifestUrl) === -1 && element.parentNode) {
				element.parentNode.removeChild(element);
			}
		});
	}

	function registerServiceWorker() {
		if (!options.serviceWorkerUrl || !('serviceWorker' in navigator) || window.isSecureContext !== true) {
			return;
		}

		window.addEventListener('load', function() {
			navigator.serviceWorker.register(options.serviceWorkerUrl).then(function(registration) {
				if (registration.waiting) {
					registration.waiting.postMessage({ type: 'SKIP_WAITING' });
				}

				registration.addEventListener('updatefound', function() {
					var installingWorker = registration.installing;
					if (!installingWorker) {
						return;
					}

					installingWorker.addEventListener('statechange', function() {
						if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
							installingWorker.postMessage({ type: 'SKIP_WAITING' });
						}
					});
				});
			}).catch(function(error) {
				if (window.console && window.console.info) {
					window.console.info('PWA Service Worker registration failed:', error);
				}
			});
		});
	}

	function setupInstallBanner() {
		var banner = document.getElementById('pwa-install-banner');
		var installButton = document.getElementById('pwa-btn-install');
		var dismissButton = document.getElementById('pwa-btn-dismiss');

		if (!options.showInstallBanner || !options.isMobile || !banner || !installButton || !dismissButton || isStandalone()) {
			hideInstallBanner(banner);
			return;
		}

		if (localStorageGet(dismissedKey) === '1') {
			hideInstallBanner(banner);
			return;
		}

		window.addEventListener('beforeinstallprompt', function(event) {
			event.preventDefault();
			deferredPrompt = event;
			banner.hidden = false;
		});

		installButton.addEventListener('click', function() {
			if (!deferredPrompt) {
				hideInstallBanner(banner);
				return;
			}

			deferredPrompt.prompt();
			deferredPrompt.userChoice.then(function(choice) {
				if (choice.outcome === 'accepted') {
					hideInstallBanner(banner);
				}

				deferredPrompt = null;
			});
		});

		dismissButton.addEventListener('click', function() {
			hideInstallBanner(banner);
			localStorageSet(dismissedKey, '1');
		});

		window.addEventListener('appinstalled', function() {
			hideInstallBanner(banner);
			localStorageSet(dismissedKey, '1');
			deferredPrompt = null;
		});
	}

	if (isStandalone()) {
		setPwaModeCookie();
	}

	removeDuplicateManifests();
	registerServiceWorker();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupInstallBanner);
	} else {
		setupInstallBanner();
	}
})();
