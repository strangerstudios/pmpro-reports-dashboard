<?php
/*
Plugin Name: Paid Memberships Pro - Mobile Reports Dashboard Add On
Plugin URI: https://www.paidmembershipspro.com/add-ons/responsive-reports-dashboard/
Description: Streamlined membership site reports dashboard designed for mobile and responsive screens.
Version: 1.0
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
Text Domain: pmpro-reports-dashboard
Domain Path: /languages
*/

define( 'PMPRORD_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Figure out if we are loading the reports dashboard.
 */
function pmprord_controller() {
	global $wp_filesystem;
	
	if ( empty( $_REQUEST['pmpro_reports_action'] ) && strpos( $_SERVER['REQUEST_URI'], '/pmpro-reports-dashboard' ) !== 0 ) {
		return;
	}
	
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
		case 'icon48':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-48.png' );
			break;
		case 'icon72':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-72.png' );
			break;
		case 'icon96':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-96.png' );
			break;
		case 'icon128':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-128.png' );
			break;
		case 'icon144':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-144.png' );
			break;
		case 'icon180':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-180.png' );
			break;
		case 'icon192':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-192.png' );
			break;
		case 'icon512':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-512.png' );
			break;
		case 'icon750':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-750.png' );
			break;
		case 'icon1024':
			// load the app icon
			header('Content-Type: image/png');
			echo $wp_filesystem->get_contents( PMPRORD_DIR . '/images/icon-1024.png' );
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
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-48.png$', 'index.php?pmpro_reports_action=icon48', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-72.png$', 'index.php?pmpro_reports_action=icon72', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-96.png$', 'index.php?pmpro_reports_action=icon96', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-128.png$', 'index.php?pmpro_reports_action=icon128', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-144.png$', 'index.php?pmpro_reports_action=icon144', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-180.png$', 'index.php?pmpro_reports_action=icon180', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-192.png$', 'index.php?pmpro_reports_action=icon192', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-512.png$', 'index.php?pmpro_reports_action=icon512', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-750.png$', 'index.php?pmpro_reports_action=icon750', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-1024.png$', 'index.php?pmpro_reports_action=icon1024', 'top' );
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
 * Add links to the reports page in PMPro. /// move to page load rather on admin init.
 */
function pmprordb_add_links_report_page() {

	// Only load on the reports main page.
	if ( ! isset( $_REQUEST['page'] ) ||  $_REQUEST['page'] != 'pmpro-reports' ) {
		return;
	}

	// We're viewing an individual report page, let's not show the link.
	if ( isset( $_REQUEST['report'] ) && $_REQUEST['page'] == 'pmpro-reports' ) {
		return;
	}

	?>
	<script>
		jQuery(document).ready(function() {
			jQuery('.memberships_page_pmpro-reports h1').append(' <a id="pmprordb-view-mobile" class="page-title-action" href="<?php echo esc_url( site_url( '/pmpro-reports-dashboard/' ) ); ?>" target="_blank"><?php echo esc_html__( 'View Mobile Reports', 'pmpro-reports-dashboard' ); ?></a>');
		});
	</script>
	<?php
}
add_action( 'admin_head', 'pmprordb_add_links_report_page' );

/**
 * Add links to the plugin action links
 *
 * @param $links (array) - The existing link array
 * @return array -- Array of links to use
 *
 */
function pmprordb_add_action_links( $links ) {
    $new_links = array(
        '<a href="' . esc_url( site_url( '/pmpro-reports-dashboard/' ) ) . '" title="' . esc_attr( __( 'View Reports', 'pmpro-reports-dashboard' ) ) . '">' . __( 'View Reports', 'pmpro-reports-dashboard' ) . '</a>',
    );
    return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'pmprordb_add_action_links' );

/**
 * Redirect the old ?pmpro_reports=true to the new /pmpro-reports-dashboard/ URL.
 */
function pmprordb_redirect_old_url() {
	if ( ! isset( $_REQUEST['pmpro_reports'] ) ) {
		return;
	}
	
	wp_redirect( site_url( '/pmpro-reports-dashboard/' ) );
	exit;
}
add_action( 'init', 'pmprordb_redirect_old_url' );

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

/**
 * AJAX callback to get the widget for a single report.
 * @since 1.0
 */
function pmpro_reports_ajax( ) {
	global $pmpro_reports;
	
	// Require admin or reports cap to view reports.
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'pmpro_reports' ) ) {
		esc_html_e( 'You do not have permissions to perform this action.', 'pmpro-reports-dashboard' );
		wp_die();
	}
	
	$report_name = sanitize_text_field( $_GET['report_name'] );
	// Bail if given name does not belong to a PMPro report.
	if( ! in_array( $report_name, array_keys( $pmpro_reports ) ) )  {
		esc_html__( 'Invalid report name.', 'pmpro-reports-dashboard' ); 
		wp_die();
	}
	call_user_func( "pmpro_report_" . esc_attr( $report_name ) . "_widget" );
	wp_die();
}
add_action( 'wp_ajax_pmpro_reports_ajax', 'pmpro_reports_ajax' );

/**
 * AJAX callback for the reports dashboard when user is not logged in.
 * @since 1.0
 */
function pmpro_reports_ajax_no_priv( ) {
	echo '<div class="pmpro_message pmpro_error">';
	echo esc_html__( 'You must log in to view the mobile reports dashboard.', 'pmpro-reports-dashboard' );
	echo '</div>';
	wp_die();
}
add_action( 'wp_ajax_nopriv_pmpro_reports_ajax', 'pmpro_reports_ajax_no_priv' );

/**
 * AJAX callback to check if the user is logged in.
 * @since 1.0
 */
function pmpro_reports_check_login_ajax( ) {
	if( is_user_logged_in() ) {
		echo '1';
	} else {
		echo '0';
	}
	wp_die();
}
add_action( 'wp_ajax_pmpro_reports_check_login', 'pmpro_reports_check_login_ajax' );
add_action( 'wp_ajax_nopriv_pmpro_reports_check_login', 'pmpro_reports_ajax_check_login' );

/**
 * AJAX callback to get a list of reports.
 */
function pmpro_reports_list_ajax( ) {
	global $pmpro_reports;
	
	// Require admin or reports cap to view reports.
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'pmpro_reports' ) ) {
		wp_die('-1'); // Send -1 to tell JS to redirect away.
	}
	
	// Sort, filter, and return reports.
	krsort( $pmpro_reports );
	echo json_encode( apply_filters( 'pmpro_reports_dashboard_reports', $pmpro_reports ) );
	wp_die();
}
add_action( 'wp_ajax_pmpro_reports_list', 'pmpro_reports_list_ajax' );