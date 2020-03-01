var CACHE_NAME = 'pmpro-reports-dashboard-v1';
var urlsToCache = [
  '/pmpro-reports-dashboard/',
  '/pmpro-reports-dashboard/manifest.js',
  '/wp-includes/js/jquery/jquery.js',
  '/wp-content/plugins/pmpro-reports-dashboard/js/pmpro-reports-dashboard.js',
  '//fonts.googleapis.com/css?family=Lato:400,700&ver=4.3.1'
];

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