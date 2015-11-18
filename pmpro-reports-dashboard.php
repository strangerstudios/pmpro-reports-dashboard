<?php
/*
Plugin Name: Paid Memberships Pro - Reports Dashboard Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-reports-dashboard/
Description: Responsive Membership Reports Dashboard for Administrator and Membership Manager Role.
Version: .1.1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

function init_pmpro_reports_dashboard()
{
	global $current_user, $wpdb, $pmpro_reports;
	if(!empty($_REQUEST['pmpro_reports']) && (current_user_can('administrator') || current_user_can('pmpro_membership_manager')) )
	{
		?>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<link rel="stylesheet" id="googleFonts-css" href="//fonts.googleapis.com/css?family=Lato:400,700&ver=4.3.1" type="text/css" media="all">
		<style>
			body {background: #FAFAFA; color: #404040; font-family: 'Lato', sans-serif; font-weight: 400; font-size: 16px; font-size: 1.6rem; line-height: 2.6rem; margin: 0; padding: 0; }
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
		</style>			

		<?php
		//report widgets
		krsort($pmpro_reports);
		foreach($pmpro_reports as $report => $title)
		{
			//make sure title is translated (since these are set before translations happen)
			$title = __($title, "pmpro");
			?>
			<div id="pmpro_report_<?php echo $report; ?>">			
				<h2><?php echo $title; ?></h2>
				<?php call_user_func("pmpro_report_" . $report . "_widget"); ?>
				<p style="text-align:center;">
					<a href="<?php echo admin_url("admin.php?page=pmpro-reports&report=" . $report);?>"><?php _e('Full Report', 'pmpro');?></a>
				</p>
			</div>
			<?php
		}		
		exit;
	}
}
add_action('init', 'init_pmpro_reports_dashboard');
