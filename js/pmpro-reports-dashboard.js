if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
      if(jQuery('body').hasClass('logged-in')) {
        jQuery('.ajax-reports-pwa').append(jQuery('<button/>').addClass('btn btn-primary refresh-all').text('Refresh All'));
        Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
      } else {
        jQuery('.ajax-reports-pwa').append(jQuery('<h2/>').text('Non logged users cannot see reports'), 
        jQuery('<h2/>').html('Please ' + '<a href="/login">' + ' login ' + '</a>' + ' to view them.'),
        jQuery('.logo-wrapper').show());

      }
    }, function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
  function fetchReports(name, title) {
    jQuery('.preloader-wrapper').css('display', 'flex');
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
  jQuery(document).ready(function($) {
    $('body').on('click', '.refresh-all',  function() {
      Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
    });
  });
}