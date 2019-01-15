<?php
/**
 * Support for WC_Composite_Products
 * @since   1.6.0
 * @see
 *
 * @package Woocommerce WPGlobus
 */

if ( ! class_exists( 'WPGlobus_WC_Composite_Products' ) ) :

	/**
	 * Class WPGlobus_WC_Composite_Products
	 */
	class WPGlobus_WC_Composite_Products {

		/**
		 * Controller.
		 */
		public static function controller() {

			if ( is_admin() ) {

				/**
				 * WooCommerce 3.0 introduces "Get property" method, with filters.
				 * The filters are applied only in the "view" context.
				 * @see   WC_Data::get_prop
				 * @since 1.7.0
				 */

				add_filter( 'woocommerce_product_get_name', array(
					__CLASS__,
					'translate_component_config_options'
				), 0 );

				add_action(
					'admin_print_scripts',
					array(
						__CLASS__,
						'print_scripts'
					)
				);

				/**
				 * @see filter in woocommerce-composite-products\includes\admin\class-wc-cp-admin.php
				 */
				add_filter( 'woocommerce_composite_component_title', array( 'WPGlobus_Filters', 'filter__text' ) );

			} else {
				/**
				 * front-end
				 */

				add_filter( 'woocommerce_get_cart_item_from_session', array(
					__CLASS__,
					'filter__woocommerce_get_cart_item_from_session',
				), PHP_INT_MAX
				);


				/**
				 * @see get_price_suffix() in woocommerce-composite-products\includes\class-wc-product-composite.php
				 */
				add_filter( 'option_woocommerce_price_display_suffix', array( 'WPGlobus_Filters', 'filter__text' ) );

				/**
				 * @see filter in woocommerce-composite-products\templates\single-product\component-single-page.php
				 */
				add_filter( 'woocommerce_composite_component_title', array( 'WPGlobus_Filters', 'filter__text' ) );

				/**
				 * @see filter in woocommerce-composite-products\templates\single-product\component-single-page.php
				 */
				add_filter( 'woocommerce_composite_component_description', array(
					'WPGlobus_Filters',
					'filter__text'
				) );

				/**
				 * @see filter in class-wc-cp-ajax.php and in
				 * @see WC_CP_Display::show_composited_product
				 */
				add_action( 'woocommerce_composite_show_composited_product', array(
					'WPGlobus_WC_Filters',
					'localize_product'
				), - 1, 1 );

				/**
				 * @todo doc
				 * @see  class-wc-cp-display.php
				 */
				//add_filter( 'woocommerce_composite_front_end_params',  array( 'WPGlobus_WC_Composite_Products', 'front_end_params' ) );

			}

		}

		/**
		 * Translate the component title before it is displayed on the Cart page.
		 *
		 * The cart item is being retrieved from the session,
		 * @see   WC_Cart::get_cart_from_session
		 *
		 * It has the form of:
		 * $session_data =>
		 *      $composite_data =>
		 *          1234567890 =>
		 *              'title' => 'multilingual string'
		 *
		 * Then, it's processed by the filter
		 * @see   WC_CP_Cart::get_cart_item_from_session
		 *
		 * We must run our filter after everyone else and translate the 'title'.
		 *
		 * @param array[] $session_data The shopping cart session data.
		 *
		 * @return array[] Data with translated title.
		 * @since 1.7.0
		 */
		public static function filter__woocommerce_get_cart_item_from_session( $session_data ) {
			if ( isset( $session_data['composite_data'] ) ) {
				foreach ( $session_data['composite_data'] as $component_id => &$component_configuration ) {
					if ( ! empty( $component_configuration['title'] ) ) {
						$component_configuration['title'] = WPGlobus_Filters::filter__text( $component_configuration['title'] );
					}
				}
			}

			return $session_data;
		}

		/**
		 * @deprecated 3.1.0 Does not work as of WC 3.1
		 * Filter product's `post` before it's displayed.
		 * This is an action. The $product object is passed by reference. No need to return.
		 *
		 * @param WC_Product $product The product.
		 */
		public static function filter__product( $product ) {
			/** @noinspection PhpUndefinedFieldInspection */
			WPGlobus_Core::translate_wp_post( $product->post, WPGlobus::Config()->language );
		}

		/**
		 * Enqueue scripts
		 *
		 * @return void
		 */
		public static function print_scripts() {

			if ( ! in_array( WPGlobus_WP::pagenow(), array( 'post.php', 'post-new.php' ) ) ) {
				return;
			}

			wp_register_script(
				'wpglobus-wc-cp',
				WPGlobus_WC::$PLUGIN_DIR_URL . 'assets/js/admin/wpglobus_wc_cp' . WPGlobus::SCRIPT_SUFFIX() . '.js',
				array( 'jquery', 'wpglobus-admin' ),
				WOOCOMMERCE_WPGLOBUS_VERSION,
				true
			);
			wp_enqueue_script( 'wpglobus-wc-cp' );
			wp_localize_script(
				'wpglobus-wc-cp',
				'WPGlobusWC_CP',
				array(
					'addElementsByClass' => array( 'group_title', 'group_description' )
				)
			);

		}

		/**
		 * This filter is applied only to a specific area in admin:
		 * Product data (of a Composite product)
		 *  => Components
		 *      => Add Component (of view existing)
		 *          => Advanced Settings
		 *              => Default Option (dropdown)
		 *
		 * @param string $name
		 *
		 * @return string
		 * @scope admin
		 * @since 1.7.0
		 */
		public static function translate_component_config_options( $name ) {
			if ( WPGlobus_WP::is_functions_in_backtrace( array(
				array( 'WC_CP_Meta_Box_Product_Data', 'component_config_options', ),
				array( 'WC_CP_Meta_Box_Product_Data', 'component_config_default_option', ),
			) )
			) {

				$name = WPGlobus_Filters::filter__text( $name );
			}

			return $name;
		}

	}

endif;

