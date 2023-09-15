if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
      Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
    }, function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
  function fetchReports(name, title) {
    jQuery.ajax({
      url: '/wp-admin/admin-ajax.php',
      type: 'GET',
      data: { 'report_name': name, 'action':'pmpro_reports_ajax'},
      dataType: 'html',
      cache: false,
      title: title,
      success: function (data) {
        jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').html(this.title), data);
      },error: function (xhr, ajaxOptions, thrownError) {
        console.log('error');
      }
    });
  }
}