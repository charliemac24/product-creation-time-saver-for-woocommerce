<?php
/**
 * Plugin Name:		Product Creation Time Saver for WooCommerce
 * Description: 	A lightweight plugin that will allow you to create reusable templates to save time in creating WooCommerce products.
 * Author: 			Charlie Macaraeg
 * Version: 		1.6
 * Author URI:		https://profiles.wordpress.org/charliemac24
 * License:         GPLv3
 * License URI:     https://www.gnu.org/licenses/gpl.txt
 * Text Domain:		product-creation-time-saver-for-woocommerce
 * Domain Path:		/languages
 *
 * @package		Product_Creation_Time_Saver_for_WooCommerce
 * @author		charliemac24
 * @license		GPLv3
 * @link		https://www.gnu.org/licenses/gpl.txt
 *
 * Product Creation Time Saver for WooCommerce is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 3, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.e
 *
 * Product Creation Time Saver for WooCommerce is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
**/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('PCTS_PLUGIN_VERSION')) {
    define('PCTS_PLUGIN_VERSION', "1.6");
}
if (!defined('PCTS_PLUGIN_DIR')) {
    define('PCTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
if (!defined('PCTS_PLUGIN_URI')) {
    define('PCTS_PLUGIN_URI', plugin_dir_url( __FILE__ ));
}

global $wpdb;

define('PCTS_TEMPLATES_TBL', "{$wpdb->prefix}pcts_templates");

include_once(PCTS_PLUGIN_DIR ."functions.php");

load_plugin_textdomain( 'product-creation-time-saver-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages/' );

// Things needed in activation
register_activation_hook(__FILE__, function(){

	// Generate table

	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// user activation key tbl
	$sql = "CREATE TABLE IF NOT EXISTS " . PCTS_TEMPLATES_TBL . " (
				ID BIGINT(20) NOT NULL AUTO_INCREMENT,
				template_name VARCHAR(255),
				template_options LONGTEXT,
				PRIMARY KEY(ID)
			);";

	dbDelta($sql);

});

// apply dropdown in the add screen
add_action('admin_footer',function(){

	global $wpdb,$pagenow;

	$pcts_templates = $wpdb->get_results("SELECT * FROM ".PCTS_TEMPLATES_TBL,ARRAY_A);

	if(empty($pcts_templates))
		return;
	
	$output = "";
	
	$output .= "<script>
			jQuery(document).ready(function($){";

	if($pagenow == 'post-new.php' && get_post_type() == 'product'){

		// button
		$button =  "";
		foreach($pcts_templates as $template){
			$button .= "<a class=\"button-primary pcts-apply-template\" attr-template-id=\"".esc_attr($template['ID'])."\">".esc_html(__('Apply Time Saver Template','product-creation-time-saver-for-woocommerce'))."</a>";
		}

		$button_click = "$('.pcts-apply-template').click(function(){
			window.location.href = '".admin_url('post-new.php?post_type=product&pcts_template_id=')."'+$(this).attr('attr-template-id');
		});";

		// javascript		
		$output .= "$('".$button."').insertAfter('.wp-heading-inline');";	
		$output .= $button_click;			
		
	}

	// apply delete JS
	$output .= "setTimeout(function(){
			$('.pcts-success').fadeOut();
		},3000);";
	
	$output .= "});
		</script>";

	echo $output;
});
