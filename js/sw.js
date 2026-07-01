var CACHE_NAME = 'pmpro-reports-dashboard-v2-rc1';
var urlsToCache = [
	'/pmpro-reports-dashboard/',
	'/pmpro-reports-dashboard/manifest.js',
	'/wp-includes/js/jquery/jquery.js',
	'/wp-content/plugins/pmpro-reports-dashboard/js/pmpro-reports-dashboard.js'
];

// Set up the cache.
self.addEventListener('install', function(event) {
	// Perform install steps
	event.waitUntil(
	caches.open(CACHE_NAME)
		.then(function(cache) {
		console.log('Opened cache');
		return cache.addAll(urlsToCache);
		})
	);
});

// Cache and return requests.
self.addEventListener('fetch', function(event) {
	// The dashboard shell itself always goes to the network first, caching
	// whatever comes back, and only falls back to the cached copy if the
	// network request fails (e.g. offline). Cache-first here would mean any
	// future edit to the shell needs a CACHE_NAME bump to ever reach visitors
	// who already have this service worker installed - this avoids that trap.
	if (event.request.mode === 'navigate') {
		event.respondWith(
			fetch(event.request).then(function(response) {
				var responseToCache = response.clone();
				caches.open(CACHE_NAME).then(function(cache) {
					cache.put(event.request, responseToCache);
				});
				return response;
			}).catch(function() {
				return caches.match(event.request);
			})
		);
		return;
	}

	event.respondWith(
		caches.match(event.request)
			.then(function(response) {
				// Cache hit - return response
				if (response) {
					return response;
				}

				return fetch(event.request).then(
					function(response) {
						// Check if we received a valid response
						if(!response || response.status !== 200 || response.type !== 'basic') {
							return response;
						}

						// IMPORTANT: Clone the response. A response is a stream
						// and because we want the browser to consume the response
						// as well as the cache consuming the response, we need
						// to clone it so we have two streams.
						var responseToCache = response.clone();

						caches.open(CACHE_NAME)
							.then(function(cache) {
								cache.put(event.request, responseToCache);
							});

						return response;
					}
				);
			})
		);
});

// Delete old caches.
self.addEventListener('activate', function(event) {
	event.waitUntil(
		caches.keys().then(function(keys) {
			return Promise.all(keys
				.filter(key => key !== CACHE_NAME)
				.map(key => caches.delete(key))
			)
		})
	)
});