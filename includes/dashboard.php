<?php
	define( 'VERSION', 'rc4' );
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="#0C3D54">
	<meta name="robots" content="noindex">
	<link rel="manifest" href="/pmpro-reports-dashboard/manifest.json">
	<link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'css/style.css?ver=' . VERSION, dirname( __FILE__ ) ) );?>" type="text/css">
	<script type="text/javascript">
		// Set up some global variables for the JS.
		const homeURL = "<?php echo esc_url( home_url() ); ?>";
		const loginURL = "<?php echo esc_url( wp_login_url( '/pmpro-reports-dashboard/?waitforlogin=1' ) ); ?>";
		const spinnerURL = "<?php echo esc_url( plugins_url( 'images/loading.gif?ver=' . VERSION, dirname( __FILE__ ) ) );?>";
		const reportsAdminURL = "<?php echo esc_url( admin_url( 'admin.php?page=pmpro-reports' ) ); ?>";
		
		// Preload the spinner.
		var spinnerImage = new Image();
		spinnerImage.src = spinnerURL;

		// Some localized strings used in the JS.
		var localized_strings = {
			'last_updated': <?php echo json_encode( esc_html__( 'Last Updated: %s at %s.', 'pmpro-reports-dashboard' ) ); ?>,
			'refresh': <?php echo json_encode( esc_html__( 'Refresh', 'pmpro-reports-dashboard' ) ); ?>,
			'no_permission': <?php echo json_encode( esc_html__( 'You do not have permission to view this dashboard.', 'pmpro-reports-dashboard' ) ); ?>,
			'must_be_logged_in': <?php echo json_encode( esc_html__( 'You must be logged in to view reports.', 'pmpro-reports-dashboard' ) ); ?>,
			'login_to_access': <?php echo json_encode( esc_html__( 'Log in now to access this dashboard.', 'pmpro-reports-dashboard' ) ); ?>,
			'refresh_failed': <?php echo json_encode( esc_html__( "Couldn't refresh, showing last saved data.", 'pmpro-reports-dashboard' ) ); ?>,
			'view_all_reports': <?php echo json_encode( esc_html__( 'View All Reports', 'pmpro-reports-dashboard' ) ); ?>,
		}

		// PMPRORD: tiny, dependency-free localStorage cache used to instant-paint the
		// last-known reports while fresh data loads in the background (loaded and run
		// before jQuery/corechart/the main dashboard script, which are deferred below).
		var PMPRORD = {
			CACHE_KEY: 'pmprord_reports_cache_v1',
			NAME_PATTERN: /^[a-zA-Z0-9_-]+$/,
			readCache: function () {
				try {
					var raw = window.localStorage.getItem( this.CACHE_KEY );
					if ( ! raw ) {
						return null;
					}
					var parsed = JSON.parse( raw );
					if ( ! parsed || typeof parsed !== 'object' || typeof parsed.reports !== 'object' ) {
						return null;
					}
					return parsed;
				} catch ( e ) {
					return null;
				}
			},
			writeCache: function ( cache ) {
				try {
					window.localStorage.setItem( this.CACHE_KEY, JSON.stringify( cache ) );
				} catch ( e ) {
					// Storage full, disabled, or unavailable (e.g. private browsing). Caching is
					// purely an optimization here, so fail silently and keep working without it.
				}
			},
			clearCache: function () {
				try {
					window.localStorage.removeItem( this.CACHE_KEY );
				} catch ( e ) {}
			},
			escapeHTML: function ( str ) {
				var div = document.createElement( 'div' );
				div.textContent = String( str );
				return div.innerHTML;
			},
			formatDate: function ( date, template ) {
				var dateStr = date.toLocaleDateString( 'en-US', { month: 'long', day: 'numeric', year: 'numeric' } );
				var timeStr = date.toLocaleTimeString( 'en-US', { hour: 'numeric', minute: 'numeric', hour12: true } );
				return template.replace( '%s', dateStr ).replace( '%s', timeStr );
			},
			// Paints the last cached reports immediately, before any JS bundle has loaded,
			// so returning admins see their last-known numbers instead of a blank spinner.
			// The real dashboard script replaces this with fresh data once it loads.
			paintFromCache: function () {
				var cache = this.readCache();
				if ( ! cache || ! cache.reports ) {
					return false;
				}

				var container = document.querySelector( '.ajax-reports-pwa' );
				if ( ! container ) {
					return false;
				}

				var html = '';
				var self = this;
				Object.keys( cache.reports ).forEach( function ( name ) {
					if ( ! self.NAME_PATTERN.test( name ) ) {
						return;
					}
					var report = cache.reports[ name ];
					if ( ! report || typeof report.html !== 'string' || typeof report.title !== 'string' ) {
						return;
					}
					// Defense in depth: a <script> tag in cached HTML won't execute when
					// painted via innerHTML (e.g. a chart never draws), so skip it here too
					// even though the write side is already supposed to have filtered it out.
					if ( /<script[\s>]/i.test( report.html ) ) {
						return;
					}
					html += '<div id="pmpro_report_' + name + '" class="pmprord-is-cached"><h2>' + self.escapeHTML( report.title ) + '</h2>' + report.html + '</div>';
				} );

				if ( ! html ) {
					return false;
				}

				// Same "Last Updated:" wording as the loaded state - the refresh button's
				// dot is enough to signal that this is being refreshed in the background.
				var savedAt = new Date( cache.savedAt || Date.now() );
				container.innerHTML = '<span class="last-updated">' +
					this.escapeHTML( this.formatDate( savedAt, localized_strings.last_updated ) + ' ' ) +
					'</span>' + html;

				var preloader = document.querySelector( '.preloader-wrapper' );
				if ( preloader ) {
					preloader.style.display = 'none';
				}
				var header = document.querySelector( '.header' );
				if ( header ) {
					header.style.display = 'flex';
				}

				return true;
			}
		};
	</script>
	<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>' defer></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/corechart.js?ver=' . VERSION, dirname( __FILE__ ) ) );?>' defer></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/pmpro-reports-dashboard.js?ver=' . VERSION, dirname( __FILE__ ) ) );?>' defer></script>
	</head>
	<body <?php body_class() ?>>
		<div class="preloader-wrapper logo">
			<img class="preloader" alt="<?php esc_attr_e( 'Loading reports dashboard...', 'pmpro-reports-dashboard' ); ?>" src="<?php echo esc_url( plugins_url( 'images/loading-logo.gif?ver=' . VERSION, dirname( __FILE__ ) ) );?>" />
		</div>

		<div class="header" style="display: none;">
			<img alt="<?php esc_attr_e( 'Paid Memberships Pro', 'pmpro-reports-dashboard' ); ?>" src="<?php echo esc_url( plugins_url( 'images/icon-white-transparent.png?ver=' . VERSION, dirname( __FILE__ ) ) );?>" />
			<?php
				// Show a link back to the site.
				printf( '<a href="%s" class="admin-link">%s</a>', esc_url( admin_url() ), esc_attr__( 'Back to site', 'pmpro-reports-dashboard' ) );
			?>
		</div>
	
		<div class="ajax-reports-pwa">
			<!-- This is updated by the login check. -->
		</div>
		<script>
			// Instant-paint whatever we last cached, ahead of jQuery/corechart/the main
			// dashboard script (all deferred above) even finishing their downloads.
			PMPRORD.paintFromCache();
		</script>

	</body>
</html>
