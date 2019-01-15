<?php
/**
 * File: woocommerce-wpglobus.php
 *
 * @package   WPGlobus\WC
 * @author    TIV.NET INC, Alex Gor (alexgff) and Gregory Karpinsky (tivnet)
 * @copyright 2015-2018 TIV.NET INC. / WPGlobus
 */

// <editor-fold desc="WordPress plugin header">
/**
 * Plugin Name: WooCommerce WPGlobus
 * Version: 3.5.0
 * Plugin URI: https://wpglobus.com/product/woocommerce-wpglobus/
 * Description: Make WooCommerce multilingual using <a href="https://wordpress.org/plugins/wpglobus/">WPGlobus</a>.
 * Text Domain: woocommerce-wpglobus
 * Domain Path: /languages/
 * Author: WPGlobus
 * Author URI: https://wpglobus.com/
 * License: GPL-3.0-or-later
 * License URI: https://spdx.org/licenses/GPL-3.0-or-later.html
 * WC requires at least: 3.1.0
 * WC tested up to: 3.4.5
 */
// </editor-fold>
// <editor-fold desc="GNU Clause">
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// </editor-fold>
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once dirname( __FILE__ ) . '/woo-includes/woo-functions.php';
}

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

add_action( 'plugins_loaded', 'wpglobus_wc_init', 0 );
function wpglobus_wc_init() {

	// Main WPGlobus plugin is required.
	if ( ! defined( 'WPGLOBUS_VERSION' ) ) {
		add_action( 'admin_notices', 'woocommerce_wpglobus_core_plugin_required' );

		return;
	}

	/**
	 * This will not be set if the core WPGlobus plugin is inactive.
	 */
	define( 'WOOCOMMERCE_WPGLOBUS_VERSION', '3.5.0' );

	/**
	 * @deprecated 1.6.3 Use WOOCOMMERCE_WPGLOBUS_VERSION instead
	 */
	define( 'WPGLOBUS_WC_VERSION', WOOCOMMERCE_WPGLOBUS_VERSION );

	// Compatibility methods for mbstring. In the Core since 1.6.5.
	if ( version_compare( WPGLOBUS_VERSION, '1.6.5', '<' ) ) {
		require_once dirname( __FILE__ ) . '/includes/compat/mbstring.php';
	}

	require_once dirname( __FILE__ ) . '/includes/class-wpglobus-wc-utils.php';

	require_once dirname( __FILE__ ) . '/includes/class-wpglobus-wc.php';

	// phpcs:disable
	WPGlobus_WC::$PLUGIN_DIR_PATH = plugin_dir_path( __FILE__ );
	WPGlobus_WC::$PLUGIN_DIR_URL  = plugin_dir_url( __FILE__ );
	// phpcs:enable

	require_once dirname( __FILE__ ) . '/includes/class-wpglobus-wc-filters.php';
	require_once dirname( __FILE__ ) . '/includes/wpglobus-wc-controller.php';
	if ( ! is_admin() || WPGlobus_WP::is_doing_ajax() ) {
		require_once dirname( __FILE__ ) . '/includes/wpglobus-wc-controller-front.php';
	}

	// phpcs:disable
	global $WPGlobus_WC;
	/* @noinspection OnlyWritesOnParameterInspection */
	$WPGlobus_WC = new WPGlobus_WC();
	// phpcs:enable

	/**
	 * Switch email locale to the locale of the order they are related to.
	 *
	 * @since    3.4.0
	 * @requires WPGlobus 1.9.15+ because of the `wpglobus_use_admin_wplang` filter.
	 *
	 * @since    3.5.0
	 * @requires PHP 5.5.21+ because trait with constructor is used.
	 * @link     https://stackoverflow.com/a/52467401/2193477
	 */
	if (
		version_compare( WPGLOBUS_VERSION, '1.9.15', '>=' )
		&& defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 50521 ) {

		require_once dirname( __FILE__ ) . '/includes/wc-mail-actions/trait-wc-mail-actions.php';
		require_once dirname( __FILE__ ) . '/includes/class-wpglobus-wc-localize-emails.php';
		new WPGlobus_WC_Localize_Emails();
	}

	/**
	 * For the wp-admin, non-AJAX:
	 */
	if ( WPGlobus_WP::in_wp_admin() ) {

		// Load translations
		load_plugin_textdomain( 'woocommerce-wpglobus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		require_once dirname( __FILE__ ) . '/includes/admin/class-wpglobus-wc-admin.php';
		WPGlobus_WC_Admin::controller();

		require_once dirname( __FILE__ ) . '/includes/admin/class-wpglobus-wc-translations.php';

		if ( ! empty( $_POST['wpglobus_wc_translations_export'] ) ) { // WPCS: input var ok, CSRF ok.
			/**
			 * Special case: the [Export to Excel] button pressed in the Translations screen
			 */
			require_once dirname( __FILE__ ) . '/includes/vendor/xlsxwriter.class.php';
			require_once dirname( __FILE__ ) . '/includes/admin/wpglobus-wc-translations-export.php';

		} else {

			require_once dirname( __FILE__ ) . '/includes/admin/class-wpglobus-wc-admin-assets.php';
			WPGlobus_WC_Admin_Assets::controller();

			require_once dirname( __FILE__ ) . '/includes/admin/class-wpglobus-wc-admin-help.php';
			WPGlobus_WC_Admin_Help::controller();
		}
	}

	/**
	 * Support for YITH WooCommerce Ajax Search
	 *
	 * @since 1.5.0
	 *
	 * @see   https://wordpress.org/plugins/yith-woocommerce-ajax-search/
	 */
	if ( defined( 'YITH_WCAS' ) ) {
		require_once dirname( __FILE__ ) . '/includes/vendor/class-wpglobus-wc-yith-woocommerce-ajax-search.php';
		WPGlobus_WC_yith_woocommerce_ajax_search::controller();
	}

	/**
	 * Support for Composite Products
	 *
	 * @since 1.6.0
	 *
	 * @see   https://www.woothemes.com/products/composite-products/
	 */
	if ( class_exists( 'WC_Composite_Products' ) ) {
		require_once dirname( __FILE__ ) . '/includes/vendor/class-wpglobus-wc-composite-products.php';
		WPGlobus_WC_Composite_Products::controller();
	}

}

/**
 * Setup updater.
 *
 * @since    1.6.2
 * @requires WPGLOBUS_VERSION 1.5.9
 */
function woocommerce_wpglobus__setup_updater() {
	/** @noinspection PhpUndefinedClassInspection */
	new TIVWP_Updater( array(
		'plugin_file' => __FILE__,
		'product_id'  => 'WooCommerce WPGlobus',
		'url_product' => 'https://wpglobus.com/product/woocommerce-wpglobus/',
	) );
}

add_action( 'tivwp_updater_factory', 'woocommerce_wpglobus__setup_updater' );

/**
 * Display an admin notice in WordPress admin area.
 *
 * @since 3.0.3
 */
function woocommerce_wpglobus_core_plugin_required() {
	echo '<div class="notice error"><p>';

	printf(
	// Translators: %1$s - name of this plugin; %2$s - WPGlobus.
		esc_html__( 'The %1$s will not function unless the core plugin, %2$s, is activated.', 'woocommerce-wpglobus' ),
		'<strong>WooCommerce WPGlobus</strong>',
		'<strong>WPGlobus</strong>'
	);

	echo '</p></div>';
}
