<?php
	global $current_user, $wpdb, $pmpro_reports;

	krsort( $pmpro_reports );
	//remove member  “Active Memberships Per Level” report until we're able to style it appropriately.
	unset( $pmpro_reports['members_per_level'] );
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
	<script type="text/javascript">
		const reports = <?php echo json_encode( $pmpro_reports );?>;
	</script>
	<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>'></script>
	<script type='text/javascript' src='<?php echo esc_url( plugins_url( 'js/pmpro-reports-dashboard.js', dirname( __FILE__ ) ) );?>'></script>
	<style>
		body {background: #FAFAFA; color: #404040; font-family: 'Lato', sans-serif; font-weight: 400; font-size: 16px; font-size: 1.6rem; line-height: 2.6rem; margin: 0; padding: 0; }
		div[id^="pmpro_report"] {background: #FAFAFA; border-bottom: 5px solid #EEE; padding: 2rem .5rem; }
		p {font-size: 10px; font-size: 1rem; margin: 0; padding: 0; }
		p a {color: #AAA; text-transform: uppercase; }
		table {border: 1px solid #EEE; border-collapse: separate; border-spacing: 0; width: 100%; }
		thead th {background: #EEE; font-size: 12px; font-size: 1.2rem; line-height: 2rem; padding: 1rem .5rem; text-align: left; }
		tbody th {border-top: 1px solid #EEE; font-size: 12px; font-size: 1.2rem; line-height: 2rem; padding: 1rem .5rem; text-align: left; }
		tbody td {border-top: 1px solid #EEE; font-size: 14px; font-size: 1.4rem; line-height: 2.4rem; padding: 1rem .5rem; text-align: left; }
		tbody tr:nth-child(odd) td, tbody tr:nth-child(odd) th {background: #FFF; }
		h2 {color: #AAA; font-size: 18px; font-size: 1.8rem; font-weight: 300; letter-spacing: 1px; margin: 0 0 1rem 0; padding: 0; text-transform: uppercase; }
		#pmpro_report_sales thead th:last-child {text-align: right; }
		.pmpro_report_tr_sub {display: table-row !important; }
		.pmpro_report_tr button {background: none; border: none; color: #404040; font-family: 'Lato', sans-serif; font-weight: 400; font-size: 14px; font-size: 1.4rem; line-height: 2.4rem; padding: 0;}
		.pmpro_report_tr_sub th, .pmpro_report_tr_sub td {font-size: 12px; line-height: 1.6rem; padding: .5rem; }
		.preloader-wrapper {display:none; background: #FFF; height: 100%; justify-content:center; align-items:center;}
		img.preloader {height: fit-content;}
	</style>
	</head>	
	<body <?php body_class() ?>>
		<div class="preloader-wrapper">
			<img  class="preloader" src="<?php echo esc_url( plugins_url( 'images/loading.gif', dirname( __FILE__ ) ) );?>" alt="Loading..." />
		</div>
		<div class="ajax-reports-pwa">
		</div>
	</body>	
</html>