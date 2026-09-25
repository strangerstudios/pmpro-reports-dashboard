var reports = false;
var pmprordPending = 0;
var pmprordFailed = false;
window.addEventListener('load', function() {
	// The reports don't depend on the service worker, so load them even if it isn't supported.
	if (! ('serviceWorker' in navigator)) {
		loadContentAfterDelay();
		return;
	}

	navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
		// Registration was successful
		console.log('ServiceWorker registration successful with scope: ', registration.scope);
		loadContentAfterDelay();
	}, function(err) {
		// Registration failed, but the reports don't depend on the service worker, so load them anyway.
		console.log('ServiceWorker registration failed: ', err);
		loadContentAfterDelay();
	});
});
function loadContentAfterDelay() {
	// Need to pause a second for logins?
	let timetowait = 10;
	let urlParams = new URLSearchParams(window.location.search);
	if( urlParams.has('waitforlogin' ) ) {
		timetowait = 1000;
	}
	setTimeout( function() { checkLoginAndLoadContent(); }, timetowait );
}
function cacheReport(name, title, html) {
	// Rebuild the cache in the current report order, dropping reports that no longer exist.
	var cached = PMPRORD.readCache();
	// Keep the previous saved time. It is only updated once all reports have refreshed.
	var cache = { user: pmprordUser, savedAt: cached ? cached.savedAt : Date.now(), reports: {} };
	Object.keys(reports).forEach(function(reportName) {
		if (reportName === name) {
			// Scripts (e.g. charts) don't run when the cache is painted, so don't cache reports that need them.
			if (! /<script[\s>]/i.test(html)) {
				cache.reports[reportName] = { title: title, html: html };
			}
		} else if (cached && cached.reports[reportName]) {
			cache.reports[reportName] = cached.reports[reportName];
		}
	});
	PMPRORD.writeCache(cache);
}
function refreshReports() {
	pmprordPending = Object.keys(reports).length;
	pmprordFailed = false;
	if (pmprordPending === 0) {
		return;
	}

	// Disable the refresh button until all reports are back.
	jQuery('.refresh-all').prop('disabled', true);
	Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
}
function fetchReports(name, title) {
	// Dim the report box while it loads.
	jQuery('.ajax-reports-pwa').children('#pmpro_report_' + name).addClass('pmprord-updating');

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
				jQuery('.ajax-reports-pwa').children('#pmpro_report_' + this.name).removeClass('pmprord-placeholder').empty()
					.append('<h2>' + title + '</h2>')
					.append(data);
				cacheReport(this.name, title, data);
			}
		},error: function (xhr, ajaxOptions, thrownError) {
			pmprordFailed = true;
			var box = jQuery('.ajax-reports-pwa').children('#pmpro_report_' + this.name);
			if (box.hasClass('pmprord-placeholder')) {
				// Nothing loaded yet, so show the error in the report box.
				box.empty().append(xhr.responseText);
			} else {
				// Keep showing the last loaded report.
				box.find('.pmprord-refresh-error').remove();
				box.append(jQuery('<p/>').addClass('pmprord-refresh-error').text(localized_strings.refresh_failed));
			}
		}, complete: function() {
			jQuery('.ajax-reports-pwa').children('#pmpro_report_' + this.name).removeClass('pmprord-updating');

			pmprordPending--;
			if (pmprordPending === 0) {
				// Only update the last updated date and time if everything refreshed.
				if (! pmprordFailed) {
					var now = new Date();
					jQuery('.last-updated').text(PMPRORD.formatDate(now));
					var cache = PMPRORD.readCache();
					if (cache) {
						cache.savedAt = now.getTime();
						PMPRORD.writeCache(cache);
					}
				}
				jQuery('.refresh-all').prop('disabled', false);
			}
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
				// Add the last updated date (filled in once the reports load) and the refresh button, unless they were painted from the cache.
				if (! jQuery('.last-updated').length) {
					jQuery('.ajax-reports-pwa').prepend(jQuery('<span/>').addClass('last-updated'));
				}
				jQuery('.last-updated').after(jQuery('<button/>').addClass('btn btn-primary refresh-all').text(localized_strings.refresh));

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
							// A -1 response means the user doesn't have permissions to view reports.
							if(data == '-1') {
								PMPRORD.clearCache();
								jQuery('.ajax-reports-pwa').empty().append(jQuery('<p/>').text(localized_strings.no_permission));
								window.location.replace(homeURL);
								return;
							}

							// Update the reports.
							reports = data;
						},error: function (xhr, ajaxOptions, thrownError) {
							// Show error in report box.
							jQuery('.ajax-reports-pwa').append(xhr.responseText);
							reports = [];
						}
					});
				}

				// If the list of reports couldn't be loaded, keep showing any cached reports.
				if (! Object.keys(reports).length) {
					return;
				}

				// Remove cached reports that no longer exist.
				var container = jQuery('.ajax-reports-pwa');
				container.children('[id^="pmpro_report_"]').each(function() {
					if (! (this.id.replace('pmpro_report_', '') in reports)) {
						jQuery(this).remove();
					}
				});

				// Put the report boxes in order, adding a placeholder for any report that wasn't cached.
				Object.entries(reports).forEach(([name, title]) => {
					var box = container.children('#pmpro_report_' + name);
					if (! box.length) {
						box = jQuery(PMPRORD.reportHTML(name, title, '<img src="' + spinnerURL + '" class="spinner" />', 'pmprord-placeholder'));
					}
					container.append(box);
				});

				refreshReports();
			} else {
				// Not logged in, so clear any reports painted from the cache.
				PMPRORD.clearCache();
				jQuery('.ajax-reports-pwa').empty().append(
					jQuery('<p/>').text(localized_strings.must_be_logged_in),
					jQuery('<p/>').html('<a href="' + loginURL + '">' + localized_strings.login_to_access + '</a>'),
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
		refreshReports();
	});
});