var CACHE_NAME = 'pmpro-reports-dashboard-v1-rc1';
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