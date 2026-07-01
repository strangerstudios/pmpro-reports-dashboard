if ('serviceWorker' in navigator) {
	var reports = false;
	var pmprordPending = 0;

	// Reads the current reports cache, merges in this report's fresh HTML, prunes any
	// report names that are no longer in the current reports list, and saves it back.
	function pmprordCacheReport(name, title, html) {
		if (!PMPRORD.NAME_PATTERN.test(name)) {
			return;
		}
		// Some widgets (e.g. Active Members Per Level) embed a <script> that draws a
		// chart. Scripts inserted via the boot script's innerHTML paint never run, so
		// caching one would just show a blank chart. Leave those to the normal spinner.
		if (/<script[\s>]/i.test(html)) {
			return;
		}
		var cache = PMPRORD.readCache() || { reports: {} };
		cache.reports[name] = { title: title, html: html };
		if (reports) {
			Object.keys(cache.reports).forEach(function (cachedName) {
				if (!(cachedName in reports)) {
					delete cache.reports[cachedName];
				}
			});
		}
		cache.savedAt = Date.now();
		PMPRORD.writeCache(cache);
	}

	// Marks the last-updated badge as syncing (or done) without touching report content.
	function pmprordSetSyncing(isSyncing) {
		jQuery('.pmprord-sync-badge').toggleClass('pmprord-syncing', isSyncing);
	}

	function pmprordLoadContentAfterDelay() {
		// Need to pause a second for logins?
		let timetowait = 10;
		let urlParams = new URLSearchParams(window.location.search);
		if( urlParams.has('waitforlogin' ) ) {
			timetowait = 1000;
		}
		setTimeout( function() { checkLoginAndLoadContent(); }, timetowait );
	}
	window.addEventListener('load', function() {
		navigator.serviceWorker.register('/pmpro-reports-dashboard/sw.js').then(function(registration) {
			// Registration was successful
			console.log('ServiceWorker registration successful with scope: ', registration.scope);
			pmprordLoadContentAfterDelay();
		}, function(err) {
			// Registration failed, but the reports themselves don't depend on the service
			// worker (it only caches static shell assets), so still load the content.
			console.log('ServiceWorker registration failed: ', err);
			pmprordLoadContentAfterDelay();
		});
	});
	function fetchReports(name, title) {
		var existingBox = document.getElementById('pmpro_report_' + name);
		var hadCachedContent = !! existingBox && ! existingBox.classList.contains('pmprord-fresh-placeholder');

		if (hadCachedContent) {
			// Keep the last-known content visible; just flag it as refreshing.
			existingBox.classList.add('pmprord-updating');
			existingBox.classList.remove('pmprord-is-cached');
		} else {
			// No prior content to show, fall back to the placeholder + spinner.
			jQuery('#pmpro_report_' + name).remove();
			jQuery('.ajax-reports-pwa').append('<div id="pmpro_report_' + name + '" class="pmprord-fresh-placeholder"><h2>' + title + '</h2><img src="' + spinnerURL +'" class="spinner" /></div>');
		}

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
			hadCachedContent: hadCachedContent,
			success: function (data) {
				if(data) {
					// Show report.
					jQuery('#pmpro_report_' + this.name).removeClass('pmprord-updating pmprord-is-cached pmprord-fresh-placeholder').empty()
						.append('<h2>' + title + '</h2>')
						.append(data);
					pmprordCacheReport(this.name, title, data);
				}
			},error: function (xhr, ajaxOptions, thrownError) {
				var box = jQuery('#pmpro_report_' + this.name).removeClass('pmprord-updating');
				if (this.hadCachedContent) {
					// Keep showing the last-known-good data instead of blowing it away.
					box.find('.pmprord-refresh-error').remove();
					box.append('<p class="pmprord-refresh-error">' + localized_strings.refresh_failed + '</p>');
				} else {
					// Nothing to fall back to, show the error as before.
					box.empty().append(xhr.responseText);
				}
			},
			complete: function () {
				pmprordPending--;
				if (pmprordPending <= 0) {
					pmprordSetSyncing(false);
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
					// Update the badge left by the boot script in place (if there is one)
					// instead of removing + re-appending it - appending would drop it to the
					// bottom of the container, after the cached report boxes already there.
					var badgeText = PMPRORD.formatDate(new Date(), localized_strings.last_updated) + ' ';
					var $badge = jQuery('.ajax-reports-pwa .last-updated');
					if ($badge.length) {
						$badge.addClass('pmprord-sync-badge pmprord-syncing').text(badgeText);
					} else {
						jQuery('.ajax-reports-pwa').prepend(jQuery('<span/>').addClass('last-updated pmprord-sync-badge pmprord-syncing').text(badgeText));
					}

					// Add the refresh button right after the badge, unless it's already there.
					if (! jQuery('.ajax-reports-pwa .refresh-all').length) {
						jQuery('.ajax-reports-pwa .last-updated').after(jQuery('<button/>').addClass('btn btn-primary refresh-all').text(localized_strings.refresh));
					}

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

					// Drop any cached report boxes for reports that no longer exist (skip this
					// if the reports list came back empty, e.g. from a failed list request,
					// so we don't wipe good cached data over a transient error).
					if (Object.keys(reports).length > 0) {
						jQuery('.ajax-reports-pwa [id^="pmpro_report_"]').each(function () {
							if (!(this.id.replace('pmpro_report_', '') in reports)) {
								jQuery(this).remove();
							}
						});
					}

					pmprordPending = Object.keys(reports).length;
					if (pmprordPending <= 0) {
						pmprordSetSyncing(false);
					}
					Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
				} else {
					// Not logged in (or the session expired) - don't leave cached report data
					// sitting in localStorage on a machine someone else might use.
					PMPRORD.clearCache();
					jQuery('.ajax-reports-pwa').append(
						jQuery('<p/>').text(localized_strings.must_be_logged_in),
						jQuery('<p/>').html('<a href="' + loginURL + '">' + localized_strings.login_to_access + '</a>'),
					);
				}
			},error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.responseText);
			}, complete: function() {
				// Hide loading logo gif.
				jQuery('.preloader-wrapper').hide();

				// Show header, unless the boot script already revealed it from cache
				// (re-running slideDown on a visible flex element can reset its display).
				if (jQuery('.header').css('display') === 'none') {
					jQuery('.header').slideDown();
				}
			}
		});
	}
	jQuery(document).ready(function($) {
		jQuery('body').on('click', '.refresh-all',	function() {
			// Update the last updated date and time, and flag it as syncing again.
			jQuery('.last-updated').addClass('pmprord-sync-badge pmprord-syncing').text(PMPRORD.formatDate(new Date(), localized_strings.last_updated) + ' ');

			// Update the reports.
			pmprordPending = Object.keys(reports).length;
			Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
		});
	});
}