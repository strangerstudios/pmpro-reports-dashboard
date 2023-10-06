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
      setTimeout( function() { checkLoginAndLoadContent(registration); }, timetowait );      
            
    }, function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
  function fetchReports(name, title, registration) {
    const $preloader = registration ? jQuery('.preloader-wrapper.logo') : jQuery('.preloader-wrapper');
    $preloader.css('display', 'flex');
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
          if(this.name != 'members_per_level') {
            if(jQuery('#pmpro_report_' + this.name).length == 0)  {
              jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').html(this.title), data);
            } else {
              jQuery('#pmpro_report_' + this.name).replaceWith(data);
            }
          } else {
            const $membersResponse = jQuery('<div/>').append(jQuery(data));
            const  $membersTable = $membersResponse.children('.pmpro_table_area');
            $membersTable.find('a').each(function(index, item) {
              jQuery(item).removeAttr('href');
            });
            if(jQuery('#pmpro_report_members_per_level').length == 0)  {
              const $memberSpan = jQuery('<span/>').attr('id', 'pmpro_report_members_per_level');
              const $detailsLink = jQuery('<a/>').addClass('button button-primary')
                                                  .attr('aria-label', 'View the full Active Members Per Level report')
                                                  .attr('href', '/wp-admin/admin.php?page=pmpro-reports&report=members_per_level')
                                                  .text('details');
              jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').text('Active Members Per Level'), $memberSpan.append($membersTable, jQuery('<p/>').addClass('pmpro_report-button').append($detailsLink)));
            } else {
              jQuery('#pmpro_report_members_per_level .pmpro_table_area').replaceWith($membersTable);
            }
          }
        }
      },error: function (xhr, ajaxOptions, thrownError) {
        jQuery('.ajax-reports-pwa').empty().append(xhr.responseText);
      }, complete: function() {
        jQuery('.preloader-wrapper').hide();
      }
    });
  }
  function checkLoginAndLoadContent(registration) {     
    // Check if logged in and load appropriate content.
    jQuery.ajax({
      url: '/wp-admin/admin-ajax.php',
      type: 'GET',
      data: { 'action':'pmpro_reports_check_login'},
      cache: false,
      success: function (data) {          
        if(data == '1') {
          jQuery('.ajax-reports-pwa').append(jQuery('<button/>').addClass('btn btn-primary refresh-all').text('Refresh All'));
          Object.entries(reports).forEach(([name, title]) => fetchReports(name, title, registration));
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
    $('body').on('click', '.refresh-all',  function() {
      Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
    });
  });
}