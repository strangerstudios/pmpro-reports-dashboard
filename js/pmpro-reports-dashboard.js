if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
            
      // Need to pause a second for logins?
      let timetowait = 10;
      let urlParams = new URLSearchParams(window.location.search);      
      if( urlParams.has('waitforlogin' ) ) {
        timetowait = 1000;
      }
      setTimeout( function() { checkLoginAndLoadContent(); }, timetowait );      
            
    }, function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
  function fetchReports(name, title) {
    // Remove the old report box.
    jQuery('#pmpro_report_' + name).remove();

    // Add placeholder.
    jQuery('.ajax-reports-pwa').append('<div id="pmpro_report_' + name + '"><h2>' + title + '</h2><img src="' + spinnerURL +'" class="spinner" /></div>');

    // Load report via AJAX.
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
          // Show report.
          jQuery('#pmpro_report_' + this.name).empty()
            .append('<h2>' + title + '</h2>')
            .append(data);
        }
      },error: function (xhr, ajaxOptions, thrownError) {
        // Show error in rporet box.
        jQuery('#pmpro_report_' + this.name).empty().append(xhr.responseText);
      }, complete: function() {
        // Nothing extra to do for now.
      }
    });
  }
  function checkLoginAndLoadContent() {     
    // Check if logged in and load appropriate content.
    jQuery.ajax({
      url: '/wp-admin/admin-ajax.php',
      type: 'GET',
      data: { 'action':'pmpro_reports_check_login'},
      cache: false,
      success: function (data) {          
        if(data == '1') {
          jQuery('.ajax-reports-pwa').append(jQuery('<button/>').addClass('btn btn-primary refresh-all').text('Refresh All'));
          Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
        } else {
          jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').text('Non logged users cannot see reports'), 
          jQuery('<h2/>').html('Please ' + '<a href="' + loginUrl + '">' + ' login ' + '</a>' + ' to view them.'),
          jQuery('.logo-wrapper').show());
        }
      },error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr.responseText);
      }, complete: function() {
        console.log('complete');
      }
    });
  }
  jQuery(document).ready(function($) {
    jQuery('body').on('click', '.refresh-all',  function() {
      Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
    });
  });
}