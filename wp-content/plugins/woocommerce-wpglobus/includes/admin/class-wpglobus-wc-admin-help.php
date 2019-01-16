<?php
/**
 * Add some content to the help tab.
 * @author      WPGlobus
 * @category    Admin
 * @package     WPGlobus-WC/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPGlobus_WC_Admin_Help' ) ) :

	/**
	 * WPGlobus_WC_Admin_Help Class
	 */
	class WPGlobus_WC_Admin_Help {

		/** */
		public static function controller() {
			add_action( 'current_screen', array( __CLASS__, 'add_tabs' ), 999 );
		}

		/**
		 * Add Contextual help tabs
		 */
		public static function add_tabs() {
			$screen = get_current_screen();

			if ( WPGlobus_WP::is_plugin_page( 'wpglobus-wc-translations' ) ) {

				ob_start();
				/**
				 * @url http://codex.wordpress.org/Function_Reference/load_template#Loading_a_template_in_a_plugin.2C_but_allowing_theme_and_child_theme_to_override_template
				 */
				$overridden_template = locate_template( 'help-tab-translate.php' );
				if ( $overridden_template ) {
					// locate_template() returns path to file
					// if either the child theme or the parent theme have overridden the template
					load_template( $overridden_template );
				} else {
					// If neither the child nor parent theme have overridden the template,
					// we load the template from the 'templates' sub-directory of the directory this file is in
					load_template( WPGlobus_WC::$PLUGIN_DIR_PATH . '/templates/help-tab-translate.php' );
				}

				$screen->add_help_tab( array(
					'id'      => __CLASS__ . '_translate',
					'title'   => __( 'Translate', 'woocommerce-wpglobus' ),
					'content' => ob_get_clean(),
				) );


				$screen->set_help_sidebar(
					$screen->get_help_sidebar() .
					'<p><a href="https://wpglobus.com/" target="_blank">WPGlobus.com</a></p>'
				);
			}
		}

	} // class

endif;

# --- EOF
