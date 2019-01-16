<?php
/**
 * Load assets.
 * @author      WPGlobus
 * @category    Admin
 * @package     WPGlobus-WC/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPGlobus_WC_Admin_Assets' ) ) :

	/**
	 * WPGlobus_WC_Admin_Assets Class
	 */
	class WPGlobus_WC_Admin_Assets {

		public static function controller() {
			add_filter( 'admin_body_class', array( __CLASS__, 'filter__admin_body_class' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
			//			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

			/**
			 * @since 1.6.6
			 */
			add_filter( 'wpglobus_plus_publish_bulk_disabled_entities', array( __CLASS__, 'filter__disabled_entities' ) );

		}

		/**
		 * Add class to body in admin
		 *
		 * @param string $classes
		 *
		 * @return string
		 */
		public static function filter__admin_body_class( $classes ) {
			if ( get_current_screen()->post_type === 'product' ) {
				$classes .= ' wpglobus-wc-wp-admin';
			}

			return $classes;
		}

		/**
		 * Enqueue styles
		 */
		public static function admin_styles() {

			/**
			 * Styles for all product admin pages
			 */
			if ( get_current_screen()->post_type === 'product' ) {
				wp_enqueue_style(
					'wpglobus-wc-admin',
					WPGlobus_WC::$PLUGIN_DIR_URL .
					'assets/css/admin/wpglobus-wc-admin.css',
					array(),
					WOOCOMMERCE_WPGLOBUS_VERSION
				);
			}

			/**
			 * *Additional* styles for the Attribute Translation page
			 * (this is not `elseif`)
			 */
			if ( WPGlobus_WP::is_plugin_page( 'wpglobus-wc-translations' ) ) {
				wp_enqueue_style(
					'wpglobus-wc-translations',
					WPGlobus_WC::$PLUGIN_DIR_URL .
					'assets/css/admin/wpglobus-wc-translations.css',
					array( 'wp-jquery-ui-dialog' ),
					WOOCOMMERCE_WPGLOBUS_VERSION
				);
			}


		}

		/**
		 * Exclude type 'product' from array of disabled entities on page Set draft status ( module Publish ).
		 *
		 * @since 1.6.6
		 * @see   filter 'wpglobus_plus_publish_bulk_disabled_entities'
		 * in wpglobus-plus\includes\class-wpglobus-plus-publish-extend.php
		 * in wpglobus-plus\includes\class-wpglobus-plus-publish2.php
		 *
		 * @param array $disabled_entities
		 *
		 * @return mixed
		 */
		public static function filter__disabled_entities( $disabled_entities ) {
			foreach( $disabled_entities as $key=>$entity ) {
				if ( 'product' === $entity ) {
					unset( $disabled_entities[$key] );
					break;
				}
			}
			return $disabled_entities;
		}

		/**
		 * Enqueue scripts
		 */
		//		public static function admin_scripts() {
		//		}

	} // class

endif;

# --- EOF
