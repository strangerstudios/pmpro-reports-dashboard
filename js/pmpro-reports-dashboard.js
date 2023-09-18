if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
      if(jQuery('body').hasClass('logged-in')) {
        jQuery('.ajax-reports-pwa').append(jQuery('<button/>').addClass('btn btn-primary refresh-all').text('Refresh All'));
        Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
      } else {
        jQuery('.ajax-reports-pwa').append(jQuery('<div/>').text('Non logged users cannot see reports'), 
        jQuery('<div/>').html('Please ' + '<a href="/login">' + ' login ' + '<a/>' + ' to view them.'));
      }
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
      name: name,
      success: function (data) {
        if(data) {
          if(jQuery('#pmpro_report_' + this.name).length == 0)  {
            jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').html(this.title), data,
            jQuery('<button/>')
            .addClass('btn btn-primary refresh-button').text('Refresh')
            .attr('data-title',this.title).attr('data-name', this.name));
          } else {
            jQuery('#pmpro_report_' + this.name).replaceWith(data);
          }
        }
      },error: function (xhr, ajaxOptions, thrownError) {
        jQuery('.ajax-reports-pwa').empty().append(xhr.responseText);
      }
    });
  }
  jQuery(document).ready(function($) {
    $('.ajax-reports-pwa').on('click', '.refresh-button', function() {
      fetchReports($(this).data('name'), $(this).data('title'));
    });

    $('body').on('click', '.refresh-all',  function() {
      Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
    });
  });
}