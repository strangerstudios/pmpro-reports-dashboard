<?php
/*
Plugin Name: Paid Memberships Pro - Reports Dashboard Add On
Plugin URI: https://www.paidmembershipspro.com/add-ons/responsive-reports-dashboard/
Description: Responsive Membership Reports Dashboard for Administrator and Membership Manager Role.
Version: .3.1
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
Text Domain: pmpro-reports-dashboard
Domain Path: /languages
*/


define( 'PMPRORD_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Load the languages folder for translations.
 */
function pmprordb_load_textdomain() {
	load_plugin_textdomain( 'pmpro-reports-dashboard', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pmprordb_load_textdomain' );

function init_pmpro_reports_dashboard()
{
	global $current_user, $wpdb, $pmpro_reports;
	if(!empty($_REQUEST['pmpro_reports']) && (current_user_can('administrator') || current_user_can('pmpro_membership_manager')) )
	{
		?>
		<html>
		<head>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<script type='text/javascript' src='<?php echo esc_url( includes_url( 'js/jquery/jquery.js') );?>'></script>
		<style>
			body {background: #FAFAFA; color: #404040; font-family: 'Arial', sans-serif; font-weight: 400; font-size: 16px; font-size: 1.6rem; line-height: 2.6rem; margin: 0; padding: 0; }
			div {background: #FAFAFA; border-bottom: 5px solid #EEE; padding: 2rem .5rem; }
			p {font-size: 10px; font-size: 1rem; margin: 0; padding: 0; }
			p a {color: #AAA; text-transform: uppercase; }
			table {border: 1px solid #EEE; border-collapse: separate; border-spacing: 0; width: 100%; }
			thead th {background: #EEE; font-size: 12px; font-size: 1.2rem; line-height: 2rem; padding: 1rem .5rem; text-align: left; }
			tbody th {border-top: 1px solid #EEE; font-size: 12px; font-size: 1.2rem; line-height: 2rem; padding: 1rem .5rem; text-align: left; }
			tbody td {border-top: 1px solid #EEE; font-size: 14px; font-size: 1.4rem; line-height: 2.4rem; padding: 1rem .5rem; text-align: left; }
			tbody tr:nth-child(odd) td, tbody tr:nth-child(odd) th {background: #FFF; }
			h2 {color: #AAA; font-size: 18px; font-size: 1.8rem; font-weight: 300; letter-spacing: 1px; margin: 0 0 1rem 0; padding: 0; text-transform: uppercase; }
			#pmpro_report_sales thead th:last-child {text-align: right; }
			.pmpro_report_tr button {background: none; border: none; color: #404040; font-family: 'Arial', sans-serif; font-weight: 400; font-size: 14px; font-size: 1.4rem; line-height: 2.4rem; padding: 0;}
			.pmpro_report_tr button.pmpro_report_th:before { bottom: 2px; display: inline-block; left: 0; padding: 0 5px 0 0; position: relative; text-decoration: none; vertical-align: bottom; }
			.pmpro_report_tr_sub th, .pmpro_report_tr_sub td {font-size: 12px; line-height: 1.6rem; padding: .5rem; }
			.pmpro_report-button { margin-top: 1rem; text-align: center; }
			.pmpro_report-button a {color: #404040; }
		</style>
		</head>	
		<body>	

		<?php
		//report widgets
		krsort($pmpro_reports);
		$pmpro_reports = apply_filters( 'pmpro_reports_dashboard_reports', $pmpro_reports );
		foreach($pmpro_reports as $report => $title)
		{
			//make sure title is translated (since these are set before translations happen)
			$title = __( $title, 'pmpro-reports-dashboard' );
			?>
			<div id="pmpro_report_<?php echo $report; ?>">			
				<h2><?php echo $title; ?></h2>
				<?php call_user_func("pmpro_report_" . $report . "_widget"); ?>
			</div>
			<?php
		}
		?>
		</body>
		</html>
		<?php	
		exit;
	}
}
add_action('init', 'init_pmpro_reports_dashboard')

/**
 * Figure out if we are loading the reports dashboard.
 */
function pmprord_controller()
{
	global $wp_filesystem;
	
	if ( empty( $_REQUEST['pmpro_reports_action'] ) && strpos( $_SERVER['REQUEST_URI'], '/pmpro-reports-dashboard' ) !== 0 ) {
		return;
	}
	
	//if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'pmpro_membership_manager' ) ) {
	//	return;
	//}
	
	$action = get_query_var( 'pmpro_reports_action' );
	
	require_once ( ABSPATH . '/wp-admin/includes/file.php' );
	WP_Filesystem();
	
	switch( $action ) {
		case 'sw':
			// load the SW JS
			header('Content-Type: application/javascript');			
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/js/sw.js' );
			break;
		case 'manifest':
			// load the PWA manifest
			header('Content-Type: application/json');			
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/manifest.json' );
			break;
		case 'icon180':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-180.png' );
			break;
		case 'icon750':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-750.png' );
			break;
		default:
			include( 'includes/dashboard.php' );
	}
	
	exit;
}
add_action('template_redirect', 'pmprord_controller');

/**
 * Add our query vars
 */
function pmprord_query_vars( $vars ){
	$vars[] = 'pmpro_reports_action';	
	return $vars;
}
add_filter( 'query_vars', 'pmprord_query_vars', 10, 1 );

/**
 * Add shorter rewrite endpoint for the dashboard.
 */
function pmprordb_add_rewrite_rule() {	
	add_rewrite_rule( '^pmpro-reports-dashboard/sw.js$', 'index.php?pmpro_reports_action=sw', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/manifest.json$', 'index.php?pmpro_reports_action=manifest', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-180.png$', 'index.php?pmpro_reports_action=icon180', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-750.png$', 'index.php?pmpro_reports_action=icon750', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard', 'index.php?pmpro_reports_action=dashboard', 'top' );
	
	flush_rewrite_rules();
}
add_action( 'init', 'pmprordb_add_rewrite_rule' );

/**
 * Keep WP from adding an ending slash
 * to our ../sw.js URL
 */
function pmprordb_redirect_canonical_callback( $redirect_url, $requested_url ) {
	$action = get_query_var( 'pmpro_reports_action' );	
	if ( in_array( $action, array( 'sw', 'manifest', 'icon' ) ) ) {
		return $requested_url;
	}
	
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'pmprordb_redirect_canonical_callback', 100, 2 );

/**
 * Add links to the plugin action links
 *
 * @param $links (array) - The existing link array
 * @return array -- Array of links to use
 *
 */
function pmprordb_add_action_links( $links ) {
    $new_links = array(
        '<a href="' . esc_url( add_query_arg( 'pmpro_reports', 'true', site_url() ) )  . '" title="' . esc_attr( __( 'View Reports', 'pmpro-reports-dashboard' ) ) . '">' . __( 'View Reports', 'pmpro-reports-dashboard' ) . '</a>',
    );
    return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'pmprordb_add_action_links' );


/**
 * Add links to the plugin row meta
 *
 * @param $links - Links for plugin
 * @param $file - main plugin filename
 * @return array - Array of links
 */
function pmprordb_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-reports-dashboard.php' ) !== false) {
		$new_links = array(
			'<a href="' . esc_url('https://www.paidmembershipspro.com/add-ons/responsive-reports-dashboard/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro-reports-dashboard' ) ) . '">' . __( 'Docs', 'pmpro-reports-dashboard' ) . '</a>',
			'<a href="' . esc_url('https://www.paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro-reports-dashboard' ) ) . '">' . __( 'Support', 'pmpro-reports-dashboard' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmprordb_plugin_row_meta', 10, 2 );
