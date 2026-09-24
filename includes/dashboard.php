<?php
	define( 'VERSION', 'rc2' );
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
		// ID of the current user if they can view reports, 0 otherwise. Cached reports are only shown to this user.
		const pmprordUser = <?php echo ( current_user_can( 'manage_options' ) || current_user_can( 'pmpro_reports' ) ) ? (int) get_current_user_id() : 0; ?>;
		
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
			'refresh_failed': <?php echo json_encode( esc_html__( 'Could not refresh. Showing last saved data.', 'pmpro-reports-dashboard' ) ); ?>,
		}

		// Cache of the last loaded reports so that returning users see them right away while fresh data loads.
		var PMPRORD = {
			CACHE_KEY: 'pmprord_reports_cache',
			// Returns the cache if it belongs to the current user, null otherwise.
			readCache: function() {
				try {
					var cache = JSON.parse( window.localStorage.getItem( this.CACHE_KEY ) );
					if ( cache && pmprordUser && cache.user === pmprordUser && typeof cache.reports === 'object' ) {
						return cache;
					}
				} catch ( e ) {}
				return null;
			},
			writeCache: function( cache ) {
				try {
					window.localStorage.setItem( this.CACHE_KEY, JSON.stringify( cache ) );
				} catch ( e ) {}
			},
			clearCache: function() {
				try {
					window.localStorage.removeItem( this.CACHE_KEY );
				} catch ( e ) {}
			},
			reportHTML: function( name, title, html, classes ) {
				return '<div id="pmpro_report_' + name + '" class="' + classes + '"><h2>' + title + '</h2>' + html + '</div>';
			},
			formatDate: function( date ) {
				return localized_strings.last_updated
					.replace( '%s', date.toLocaleDateString( 'en-US', { month: 'long', day: 'numeric', year: 'numeric' } ) )
					.replace( '%s', date.toLocaleTimeString( 'en-US', { hour: 'numeric', minute: 'numeric', hour12: true } ) ) + ' ';
			},
			paintFromCache: function() {
				var cache = this.readCache();
				if ( ! cache ) {
					// Logged out or a different user, so don't leave the last user's reports behind.
					this.clearCache();
					return;
				}

				var html = '';
				Object.keys( cache.reports ).forEach( function( name ) {
					html += PMPRORD.reportHTML( name, cache.reports[ name ].title, cache.reports[ name ].html, '' );
				} );
				if ( ! html ) {
					return;
				}

				var container = document.querySelector( '.ajax-reports-pwa' );
				container.innerHTML = '<span class="last-updated"></span>' + html;
				container.querySelector( '.last-updated' ).textContent = this.formatDate( new Date( cache.savedAt ) );
				document.querySelector( '.preloader-wrapper' ).style.display = 'none';
				document.querySelector( '.header' ).style.display = '';
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
		<script>PMPRORD.paintFromCache();</script>
	
	</body>	
</html>
