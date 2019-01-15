<?php
/**
 * Support for YITH WooCommerce Ajax Search
 * @since 1.5.0
 * @see https://wordpress.org/plugins/yith-woocommerce-ajax-search/
 *
 * @package Woocommerce WPGlobus
 */

if ( ! class_exists( 'WPGlobus_WC_yith_woocommerce_ajax_search' ) ) :

	/**
	 * Class WPGlobus_wc_yith_woocommerce_ajax_search
	 */
	class WPGlobus_WC_yith_woocommerce_ajax_search {

		/**
		 * Controller.
		 */
		public static function controller() {

			if ( is_admin() ) {

				add_filter(
					'wpglobus_enabled_pages',
					array(
						__CLASS__,
						'enable_page'
					)
				);

				add_filter(
					'wpglobus_localize_custom_data',
					array(
						__CLASS__,
						'custom_data'
					),
					10,	3
				);

				add_action(
					'admin_footer',
					array(
						__CLASS__,
						'fix_wpglobus_dialog_form'
					), 10
				);

			} else {
				add_filter( 'option_yith_wcas_search_input_label',  array( 'WPGlobus_Filters', 'filter__text' ) );
				add_filter( 'option_yith_wcas_search_submit_label',  array( 'WPGlobus_Filters', 'filter__text' ) );
			}

		}

		/**
		 * Fix wpglobus-dialog-form
		 *
		 * @return void
		 */
		public static function fix_wpglobus_dialog_form() {
			if ( empty( $_GET[ 'page' ] ) || 'yith_wcas_panel' != $_GET[ 'page' ] ) {
				return;
			}
			?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				jQuery( document ).on( 'click', '.wpglobus_dialog_start', function(ev) {
					setTimeout(function () {
						var w = jQuery('.wpglobus-dialog').width() - 30;
						jQuery( '#wpglobus-dialog-wrapper' ).css( 'width', w+'px' );
					}, 500 );
				});
			/* ]]> */
			</script>
			<?php

		}

		/**
		 * Add elements with WPGlobusDialogApp
		 * @see General settings at wp-admin/admin.php?page=yith_wcas_panel
		 *
		 * @param array  $page_data_values An array with custom data or null.
		 * @param string $page_data_key    Data key. @since 1.3.0
		 * @param string $page      Page. @since 1.5.0
		 * @return array
		 */
		public static function custom_data( $page_data_values, $page_data_key, $page ) {

			if ( 'yith_wcas_panel' == $page ) {

				/**
				 * @see $page_data_key in class-wpglobus.php
				 */
				$page_data_values['addElements']['yith_wcas_search_input_label'] 	= 'yith_wcas_search_input_label';
				$page_data_values['addElements']['yith_wcas_search_submit_label'] 	= 'yith_wcas_search_submit_label';
			}

			return $page_data_values;

		}

		/**
		 * Enable yith_wcas_panel page for load wpglobus-admin.js
		 *
		 * @param array $pages
		 *
		 * @return array
		 */
		public static function enable_page( $pages ) {
			$pages[] = 'yith_wcas_panel';
			return $pages;
		}

	}

endif;

