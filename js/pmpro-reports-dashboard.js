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

	// Removes a single report from the cache (e.g. it used to have content and now
	// doesn't), so a later cold load doesn't resurrect stale data from it.
	function pmprordForgetCachedReport(name) {
		var cache = PMPRORD.readCache();
		if (cache && cache.reports && (name in cache.reports)) {
			delete cache.reports[name];
			cache.savedAt = Date.now();
			PMPRORD.writeCache(cache);
		}
	}

	// Marks the refresh button as syncing (or done) without touching report content.
	// The dot lives on the button (to its right) rather than the badge text, and its
	// space is always reserved in CSS, so nothing shifts when syncing starts or ends.
	function pmprordSetSyncing(isSyncing) {
		jQuery('.refresh-all').toggleClass('pmprord-syncing', isSyncing);
	}

	// A widget with nothing but a "Details" link isn't worth its own box - strip that
	// link out and see if there's anything left to show.
	function pmprordIsWidgetEmpty(html) {
		var wrapper = document.createElement('div');
		wrapper.innerHTML = html;
		wrapper.querySelectorAll('.pmpro_report-button').forEach(function (el) {
			el.remove();
		});
		return wrapper.textContent.replace(/\s+/g, '') === '';
	}

	// Puts report boxes back in the server's order (registration order, plus whatever
	// pmpro_reports_dashboard_reports filters have done to it - hiding some, moving
	// others). Needed because a cached box keeps whatever DOM position it was first
	// painted in, and a never-cached box (e.g. one with a <script>) just gets appended
	// wherever fetchReports() happens to run it - neither respects the current order
	// on their own, so this runs once everything has settled to put them back in line.
	function pmprordReorderReports() {
		var $container = jQuery('.ajax-reports-pwa');
		Object.keys(reports).forEach(function (name) {
			var box = document.getElementById('pmpro_report_' + name);
			if (box) {
				$container.append(box);
			}
		});
	}

	// Shows a single "View All Reports" link at the bottom once any widgets have been
	// skipped for having no content, and removes it again if that's no longer the case
	// (e.g. after a refresh brings real data back).
	function pmprordUpdateViewAllLink() {
		var totalReports = Object.keys(reports).length;
		// Direct children only - some widgets wrap their own content in an element
		// that reuses the exact same id as our outer wrapper (a pre-existing quirk),
		// which the descendant selector would double-count.
		var renderedReports = jQuery('.ajax-reports-pwa').children('[id^="pmpro_report_"]').length;
		var $link = jQuery('.pmprord-view-all-link');

		if (totalReports > 0 && renderedReports < totalReports) {
			if (! $link.length) {
				jQuery('.ajax-reports-pwa').append(
					jQuery('<p/>').addClass('pmprord-view-all-link').append(
						jQuery('<a/>').attr('href', reportsAdminURL).text(localized_strings.view_all_reports)
					)
				);
			}
		} else {
			$link.remove();
		}
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
				if (data && pmprordIsWidgetEmpty(data)) {
					// Nothing but a "Details" link - drop the widget instead of showing
					// an empty box; the view-all link at the bottom covers it. Also drop
					// any previously cached content for it so a cold load doesn't bring
					// stale (non-empty) data back for a report that's empty now.
					jQuery('#pmpro_report_' + this.name).remove();
					pmprordForgetCachedReport(this.name);
				} else if(data) {
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
				// Floor at zero so an overlapping refresh (see the click handler below)
				// can't drift the shared counter negative and desync the indicator.
				pmprordPending = Math.max(0, pmprordPending - 1);
				if (pmprordPending <= 0) {
					// Only reorder/re-check once everything has settled - doing it after
					// every single fetch would just repeat the same work N times.
					pmprordReorderReports();
					pmprordUpdateViewAllLink();
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
						$badge.text(badgeText);
					} else {
						jQuery('.ajax-reports-pwa').prepend(jQuery('<span/>').addClass('last-updated').text(badgeText));
					}

					// Add the refresh button right after the badge, unless it's already there,
					// and flag it as syncing until all reports finish loading.
					var $refreshBtn = jQuery('.ajax-reports-pwa .refresh-all');
					if (! $refreshBtn.length) {
						$refreshBtn = jQuery('<button/>').addClass('btn btn-primary refresh-all').text(localized_strings.refresh);
						jQuery('.ajax-reports-pwa .last-updated').after($refreshBtn);
					}
					$refreshBtn.addClass('pmprord-syncing');

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
						jQuery('.ajax-reports-pwa').children('[id^="pmpro_report_"]').each(function () {
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
					// Not logged in (or the session expired) - clear both the localStorage
					// cache AND whatever the boot script already painted from it, so a
					// signed-out visitor on a shared machine doesn't keep seeing the prior
					// session's cached report numbers next to the login prompt.
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
			// Update the last updated date and time.
			jQuery('.last-updated').text(PMPRORD.formatDate(new Date(), localized_strings.last_updated) + ' ');

			var reportNames = Object.keys(reports);
			if (! reportNames.length) {
				// Nothing to fetch - don't flag as syncing since it would never clear.
				return;
			}

			jQuery(this).addClass('pmprord-syncing');

			// Add to, don't overwrite, the pending count - if a previous refresh is
			// still in flight, each fetch's own ajax completion decrements exactly
			// once, so overwriting here could zero it out before those finish.
			pmprordPending += reportNames.length;
			Object.entries(reports).forEach(([name, title]) => fetchReports(name, title));
		});
	});
}