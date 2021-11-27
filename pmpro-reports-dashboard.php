<?php
/*
Plugin Name: Paid Memberships Pro - Reports Dashboard Add On
Plugin URI: https://www.paidmembershipspro.com/add-ons/responsive-reports-dashboard/
Description: Responsive Membership Reports Dashboard for Administrator and Membership Manager Role.
Version: .3
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
Text Domain: pmpro-reports-dashboard
Domain Path: /languages
*/

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
add_action('init', 'init_pmpro_reports_dashboard');

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
