<?php
	global $current_user, $wpdb, $pmpro_reports;

	krsort( $pmpro_reports );
	$pmpro_reports = apply_filters( 'pmpro_reports_dashboard_reports', $pmpro_reports );
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="#2997c8">
	<meta name="robots" content="noindex">
	<link rel="stylesheet" id="googleFonts-css" href="//fonts.googleapis.com/css?family=Lato:400,700&ver=4.3.1" type="text/css" media="all">
	<link rel="manifest" href="/pmpro-reports-dashboard/manifest.json">
	<link rel="apple-touch-icon" href="/pmpro-reports-dashboard/icon-180.png" />
	<link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'css/style.css', dirname( __FILE__ ) ) );?>" type="text/css">
	<script type="text/javascript">
		const reports = <?php echo json_encode( $pmpro_reports );?>;
		const loginUrl = "<?php echo esc_url( wp_login_url( '/pmpro-reports-dashboard/?waitforlogin=1' ) ); ?>";
	</script>
	<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>'></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/pmpro-reports-dashboard.js', dirname( __FILE__ ) ) );?>'></script>
	</head>	
	<body <?php body_class() ?>>
	<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'pmpro_membership_manager' ) || current_user_can( 'pmpro_reports' ) ) { ?>
		<div class="preloader-wrapper">
			<img  class="preloader" src="<?php echo esc_url( plugins_url( 'images/loading.gif', dirname( __FILE__ ) ) );?>" alt="Loading..." />
		</div>
		<div class="preloader-wrapper logo">
			<img  class="preloader" src="<?php echo esc_url( plugins_url( 'images/loading-logo.gif', dirname( __FILE__ ) ) );?>" alt="Loading..." />
		</div>
	<?php } ?>
		<div class="ajax-reports-pwa">
			<div class="logo-wrapper">
				<img class="non-logged-logo" src="<?php echo esc_url( plugins_url( 'images/icon-750.png', dirname( __FILE__ ) ) );?>" alt="Paid Memberships Pro" />
			</div>
		</div>
	</body>	
</html>