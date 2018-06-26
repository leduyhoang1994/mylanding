<?php
/**
 * Plugin Name: Kuteshop Toolkit
 * Plugin URI:  http://kutethemes.net
 * Description: Kuteshop toolkit for Kuteshop theme. Currently supports the following theme functionality: shortcodes, CPT.
 * Version:     1.0.1
 * Author:      Kutethemes Team
 * Author URI:  http://kutethemes.net
 * License:     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kuteshop-toolkit
 **/
// Define url to this plugin file.
define( 'KUTESHOP_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );
// Define path to this plugin file.
define( 'KUTESHOP_TOOLKIT_PATH', plugin_dir_path( __FILE__ ) );
// Include function plugins if not include.
if ( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function kuteshop_toolkit_load_textdomain()
{
	load_plugin_textdomain( 'kuteshop-toolkit', false, KUTESHOP_TOOLKIT_PATH . 'languages' );
}

add_action( 'init', 'kuteshop_toolkit_load_textdomain' );
// Run shortcode in widget text
add_filter( 'widget_text', 'do_shortcode' );
// Register custom post types
include_once( KUTESHOP_TOOLKIT_PATH . '/includes/post-types.php' );
// Register init
if ( !function_exists( 'kuteshop_toolkit_init' ) ) {
	function kuteshop_toolkit_init()
	{
		include_once( KUTESHOP_TOOLKIT_PATH . '/includes/init.php' );
		include_once( KUTESHOP_TOOLKIT_PATH . '/includes/classes/importer/importer.php' );
		if ( class_exists( 'Vc_Manager' ) ) {
			include_once( KUTESHOP_TOOLKIT_PATH . '/includes/shortcode.php' );
		}
	}
}
add_action( 'kuteshop_toolkit_init', 'kuteshop_toolkit_init' );
if ( !function_exists( 'kuteshop_toolkit_install' ) ) {
	function kuteshop_toolkit_install()
	{
		do_action( 'kuteshop_toolkit_init' );
	}
}
add_action( 'plugins_loaded', 'kuteshop_toolkit_install', 11 );
