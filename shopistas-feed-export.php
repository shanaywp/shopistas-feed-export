<?php
/**
 * Plugin Name: Shopistas Feed Export
 * Plugin URI: https://www.tidbitsolution.com/
 * Description: This plugin provide woocommerce product XML Feed.
 * Version: 1.0.0
 * Author: Shanay
 * Author URI: https://www.tidbitsolution.com/
 * Text Domain: shopistas-feed-export
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define WOO_FEED_PLUGIN_FILE.
if ( ! defined( 'WOO_FEED_PLUGIN_FILE' ) ) {
	define( 'WOO_FEED_PLUGIN_FILE', __FILE__ );
}

define( 'WOO_FEED_VERSION', '1.0.0' );
define( 'WOO_FEED_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
define( 'WOO_FEED_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WOO_FEED_MAIN_FILE', __FILE__ );
define( 'WOO_FEED_ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WOO_FEED_DIR_NAME', dirname(plugin_basename(__FILE__)) . '/' );

function WOO_FEED_Active() {

	// Require parent plugin
	if( ! is_plugin_active('woocommerce/woocommerce.php') && current_user_can('activate_plugins')) {

		// Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Woocommerce Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}
}
register_activation_hook( WOO_FEED_PLUGIN_FILE , 'WOO_FEED_Active');

add_action('plugins_loaded', 'woo_plugin_init');
function woo_plugin_init() {
	load_plugin_textdomain( 'shopistas-feed-export', false, dirname(plugin_basename( __FILE__ )) . '/languages' );
}
// Include the main WooCommerce class.
if ( ! class_exists( 'WooTicketBooking' ) ) {
	include_once dirname( __FILE__ ) . '/includes/settings.php';
}