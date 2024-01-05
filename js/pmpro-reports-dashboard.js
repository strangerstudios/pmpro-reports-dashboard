if ('serviceWorker' in navigator) {
	var reports = false;
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
			async: true,
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
				// Show error in report box.
				jQuery('#pmpro_report_' + this.name).empty().append(xhr.responseText);
			}
		});
	}
	function checkLoginAndLoadContent() {
		// Check if logged in and load appropriate content.
		jQuery.ajax({
			async: false,
			url: '/wp-admin/admin-ajax.php',
			type: 'GET',
			data: { 'action':'pmpro_reports_check_login'},
			cache: false,
			success: function (data) {					
				if(data == '1') {
					// Get the current date and format it
					const currentDate = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
					const currentTime = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });

					// Append the last updated date
					jQuery('.ajax-reports-pwa').append(jQuery('<span/>').addClass('last-updated').text('Last Updated: ' + currentDate + ' at ' + currentTime + '. '));

					// Append the refresh button
					jQuery('.ajax-reports-pwa').append(jQuery('<button/>').addClass('btn btn-primary refresh-all').text('Refresh'));

					// Show spinner.
					jQuery('.ajax-reports-pwa').append(jQuery('<img/>').addClass('preloader-wrapper fetching-reports').attr('src', spinnerURL));

					// Get list of reports.
					if ( reports === false ) {
						jQuery.ajax({
							async: false,
							url: '/wp-admin/admin-ajax.php',
							type: 'GET',
							data: { 'action':'pmpro_reports_list'},
							dataType: 'json',
							cache: false,
							success: function (data) {
								reports = data;
							},error: function (xhr, ajaxOptions, thrownError) {
								// Show error in report box.
								jQuery('#pmpro_report_' + this.name).empty().append(xhr.responseText);
								reports = [];
							}
						});
					}

					// Remove spinner.
					jQuery('.fetching-reports').remove();

					Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
				} else {
					jQuery('.ajax-reports-pwa').append(
						jQuery('<p/>').text('You must be logged in to view reports.'), 
						jQuery('<p/>').html('<a href="' + loginUrl + '">' + 'Log in now' + '</a>' + ' to access this dashboard.'),
					);
				}
			},error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.responseText);
			}, complete: function() {
				// Hide loading logo gif.
				jQuery('.preloader-wrapper').hide();

				// Show header.
				jQuery('.header').slideDown();
			}
		});
	}
	jQuery(document).ready(function($) {
		jQuery('body').on('click', '.refresh-all',	function() {
			// Update the last updated date and time.
			const currentDate = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
			const currentTime = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
			jQuery('.last-updated').text('Last Updated: ' + currentDate + ' at ' + currentTime + '. ');

			// Update the reports.
			Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
		});
	});
}