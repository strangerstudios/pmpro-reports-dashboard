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
 * Figure out if we are loading the reports dashboard.
 */
function pmprord_controller() {
	global $wp_filesystem;
	
	if ( empty( $_REQUEST['pmpro_reports_action'] ) && strpos( $_SERVER['REQUEST_URI'], '/pmpro-reports-dashboard' ) !== 0 ) {
		return;
	}
	
	if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'pmpro_membership_manager' ) || ! current_user_can( 'pmpro_reports' ) ) {
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

	if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'pmpro_membership_manager' ) || ! current_user_can( 'pmpro_reports' ) ) {
		return;
	}

	add_rewrite_rule( '^pmpro-reports-dashboard/sw.js$', 'index.php?pmpro_reports_action=sw', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/manifest.json$', 'index.php?pmpro_reports_action=manifest', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-180.png$', 'index.php?pmpro_reports_action=icon180', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard/images/icon-750.png$', 'index.php?pmpro_reports_action=icon750', 'top' );
	add_rewrite_rule( '^pmpro-reports-dashboard', 'index.php?pmpro_reports_action=dashboard', 'top' );
	
	flush_rewrite_rules();
}
add_action( 'admin_init', 'pmprordb_add_rewrite_rule' );

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
	if ( ! isset( $_REQUEST['page'] ) &&  $_REQUEST['page'] != 'pmpro-reports' ) {
		return;
	}

	// We're viewing an individual report page, let's not show the link.
	if ( isset( $_REQUEST['report'] ) && $_REQUEST['page'] == 'pmpro-reports' ) {
		return;
	}

	?>
	<script>
		jQuery(document).ready(function() {
			jQuery('.memberships_page_pmpro-reports h1').append(' <a id="pmprordb-view-mobile" class="page-title-action" href="/pmpro-reports-dashboard" target="_blank"><?php echo esc_html__( 'View Mobile Reports', 'pmpro-reports-dashboard' ); ?></a>');
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


/**
 * AJAX callback for the reports dashboard.
 * @since TBD
 */
function pmpro_reports_ajax( ) {
	global $pmpro_reports;
	$report_name = sanitize_text_field( $_GET['report_name'] );
	//Bail if given name does not belong to a PMPro report
	if( ! in_array( $report_name, array_keys( $pmpro_reports ) ) )  {
		esc_html__( 'Invalid report name.', 'pmpro-reports-dashboard' ); 
		wp_die();
	}
	if( $report_name != 'members_per_level' ) {
		call_user_func( "pmpro_report_" . esc_attr( $report_name ) . "_widget" );
	} else {
		call_user_func( "pmpro_report_" . esc_attr( $report_name ) . "_page" );
	}
	 wp_die();
}

/**
 * AJAX callback for the reports dashboard when user is not logged in.
 * @since TBD
 */
function pmpro_reports_ajax_no_priv( ) {
	echo '<div class="pmpro_message pmpro_error">';
	echo esc_html__( 'Reports Page requires user is logged in to see it.', 'pmpro-reports-dashboard' );
	echo '</div>';
	wp_die();
}

add_action("wp_ajax_pmpro_reports_ajax", "pmpro_reports_ajax");
add_action("wp_ajax_nopriv_pmpro_reports_ajax", "pmpro_reports_ajax_no_priv");



