<?php
	global $current_user, $wpdb, $pmpro_reports;

	krsort( $pmpro_reports );
	$pmpro_reports = apply_filters( 'pmpro_reports_dashboard_reports', $pmpro_reports );
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="#0C3D54">
	<meta name="robots" content="noindex">
	<link rel="manifest" href="/pmpro-reports-dashboard/manifest.json">
	<link rel="apple-touch-icon" href="/pmpro-reports-dashboard/icon-180.png" />
	<link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'css/style.css', dirname( __FILE__ ) ) );?>" type="text/css">
	<script type="text/javascript">
		const reports = <?php echo json_encode( $pmpro_reports );?>;
		const loginUrl = "<?php echo esc_url( wp_login_url( '/pmpro-reports-dashboard/?waitforlogin=1' ) ); ?>";
		const spinnerURL = "<?php echo esc_url( plugins_url( 'images/loading.gif', dirname( __FILE__ ) ) );?>";
	</script>
	<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>'></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/pmpro-reports-dashboard.js', dirname( __FILE__ ) ) );?>'></script>
	</head>	
	<body <?php body_class() ?>>
	
		<div class="preloader-wrapper logo">
			<img  class="preloader" src="<?php echo esc_url( plugins_url( 'images/loading-logo.gif', dirname( __FILE__ ) ) );?>" alt="Loading..." />
		</div>

		<div class="ajax-reports-pwa">
			<!-- This is updated by the login check. -->
		</div>
	
	</body>	
</html>