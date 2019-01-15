<?php
/**
 * Plugin Name: Hotline Phone Ring
 * Plugin URI: https://namncn.com/plugins/hotline-phone-ring/
 * Description: Fixed Hotline on the screen.
 * Version: 2.0.1
 * Author: Nam Truong
 * Author URI: https://namncn.com
 *
 * Text Domain: hotline-phone-ring
 * Domain Path: /languages/
 *
 * @package Hotline Phone Ring
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define.
define( 'HPR_VERSION', '2.0.1' );
define( 'HPR_FILE', __FILE__ );
define( 'HPR_PATH', plugin_dir_path( HPR_FILE ) );
define( 'HPR_URL', plugin_dir_url( HPR_FILE ) );
define( 'HPR_MODULES_PATH', HPR_PATH . 'modules/' );
define( 'HPR_ASSETS_URL', HPR_URL . 'assets/' );

require_once HPR_PATH . '/includes/class-hotline-phone-ring.php';

/**
 * [hpr_load_plugin_textdomain description]
 * @return [type] [description]
 */
function hpr_load_plugin_textdomain() {
	load_plugin_textdomain( 'hotline-phone-ring', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'hpr_load_plugin_textdomain' );

HPR::instance();
