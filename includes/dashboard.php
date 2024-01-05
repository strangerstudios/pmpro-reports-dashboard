<?php
	define( 'VERSION', 'rc1' );
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
		}
	</script>
	<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>'></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/corechart.js?ver=' . VERSION, dirname( __FILE__ ) ) );?>'></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/pmpro-reports-dashboard.js?ver=' . VERSION, dirname( __FILE__ ) ) );?>'></script>
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
	
	</body>	
</html>
