<?php
/**
 * Admin Model/View
 * @author      WPGlobus
 * @category    Admin
 * @package     WPGlobus-WC/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPGlobus_WC_Admin' ) ) :

	/**
	 * Class WPGlobus_WC_Admin
	 */
	class WPGlobus_WC_Admin {

		protected static $enabled_entities = array(
			'product',
			'product_attributes',
		);

		/** */
		public static function controller() {

			if ( WPGlobus_WP::is_pagenow( array(
				'post-new.php',
				'edit-tags.php',
				'term.php', # @since WP 4.5
				'edit.php',
				'post.php',
				'admin.php'
			) )
			) {
				add_filter( 'wpglobus_disabled_entities', array(
					'WPGlobus_WC_Admin',
					'enable_wpglobus_on_product_pages'
				), 0 );
			}

            add_filter( 'wpglobus_enabled_pages', array(
                'WPGlobus_WC_Admin',
                'on_wpglobus_enabled_pages'
            ), 0 );

			add_action( 'admin_footer', array(
				'WPGlobus_WC_Admin',
				'product_attribute_inputs'
			), 0 );

			/**
			 * Filter comment titles in WooCommerce Recent Reviews admin dashboard.
			 * (Available since WC 2.3)
			 * @see WC_Admin_Dashboard
			 */
			add_filter( 'woocommerce_admin_dashboard_recent_reviews', array(
				'WPGlobus_Filters',
				'filter__text'
			), 0 );

			/**
			 * @see wc_get_attribute_taxonomies()
			 */
			add_filter( 'woocommerce_attribute_taxonomies', array(
				'WPGlobus_WC_Admin',
				'filter__attribute_taxonomies'
			), 0 );

			/**
			 * @since 1.3.0
			 * wp-admin/admin.php?page=wc-reports&tab=orders&report=sales_by_product
			 */
			add_filter( 'woocommerce_product_object', array(
				'WPGlobus_WC_Admin',
				'filter__product_object'
			), 0 );

			/**
			 * Filter for category object at
			 * wp-admin/admin.php?page=wc-reports&tab=orders&report=sales_by_category
			 * We can use standard WPGlobus `get_term` filter in wpglobus-controller.php
			 * more info @see WC_Report_Sales_By_Category::get_chart_legend
			 *
			 * @since 1.3.0
			 */
			if ( isset( $_GET['report'] ) && isset( $_GET['page'] ) && 'wc-reports' == $_GET['page'] ) {
				if ( 'sales_by_category' === $_GET['report'] ) {
					add_filter( 'get_term',
                        array( 'WPGlobus_Filters', 'filter__get_term' ), 0 );
				} elseif ( 'sales_by_product' === $_GET['report'] ) {
					/**
					 * Filter product name in the "Showing reports for:" widget
                     * on /wp-admin/admin.php?page=wc-reports&tab=orders&report=sales_by_product&product_ids=...
                     * @since 3.1.2
					 */
					add_filter( 'woocommerce_product_get_name',
						array( 'WPGlobus_Filters', 'filter__text' ), 0 );
				}
			}
		}

		/**
		 * Translate product (WC 2.x)
		 * The 'woocommerce_product_object' filter no longer exists in WC 3.x.
		 * Here is the 2.x code snippet for reference:
		 * <code>
		 * private function get_product_object( $the_product ) {
		 * 	if ( false === $the_product ) {
		 * 		$the_product = $GLOBALS['post'];
		 * 	} elseif ( is_numeric( $the_product ) ) {
		 * 		$the_product = get_post( $the_product );
		 * 	} elseif ( $the_product instanceof WC_Product ) {
		 * 		$the_product = get_post( $the_product->id );
		 * 	} elseif ( ! ( $the_product instanceof WP_Post ) ) {
		 * 		$the_product = false;
		 * 	}
		 * 		return apply_filters( 'woocommerce_product_object', $the_product );
		 * }
		 * </code>
		 *
		 * @since 1.3.0
		 *        Fixed in 3.0.2
		 *
		 * @param false|WP_Post|WC_Product|mixed $product
		 *
		 * @return false|WP_Post|WC_Product|mixed
		 */
		public static function filter__product_object( $product ) {

			// If $product has title and content, regardless its type, let's filter it.

			if ( ! empty( $product->post_title ) ) {
				$product->post_title = WPGlobus_Core::text_filter( $product->post_title, WPGlobus::Config()->language );
			}

			if ( ! empty( $product->post_content ) ) {
				$product->post_content = WPGlobus_Core::text_filter( $product->post_content, WPGlobus::Config()->language );
			}

			return $product;
		}

        /**
         * Enable pages for WPGlobusDialogApp
         * @see 'wpglobus_enabled_pages' filter
         *
         * @since 1.2.0
         * @param string[] $enabled_pages
         *
         * @return array
         */
        public static function on_wpglobus_enabled_pages( $enabled_pages ) {
            $enabled_pages[] = 'wc-settings';
            return $enabled_pages;
        }

		/**
		 * Enable WPGlobus on the 'product' pages (post_type=product)
		 *
		 * @param string[] $entities
		 *
		 * @return array
		 */
		public static function enable_wpglobus_on_product_pages( $entities ) {
			if ( ! WPGlobus_WC_Utils::enabled_entity( self::$enabled_entities, 'product' ) ) {
				return $entities;
			}
			foreach ( $entities as $key => $entity ) {
				if ( false !== strpos( $entity, 'product' ) ) {
					unset( $entities[ $key ] );
				}
			}

			return $entities;
		}


		/**
		 * Output language fields for Edit Attribute page
		 * check pagenow for 'admin.php' for Woocommerce 2.2
		 * and for 'edit.php' since Woocommerce 2.3
		 */
		public static function product_attribute_inputs() {

			if ( ! empty( $_GET['edit'] ) && WPGlobus_WP::is_plugin_page( 'product_attributes' ) ) {

				$edit = absint( $_GET['edit'] );

				$label = WPGlobus_WC_Utils::attribute_label_by( 'id', $edit );

				?>

				<div id="wpglobus-wc-attribute-labels">
					<?php
					foreach ( WPGlobus::Config()->enabled_languages as $language ) :
						$return = ( $language === WPGlobus::Config()->default_language ?
							WPGlobus::RETURN_IN_DEFAULT_LANGUAGE : WPGlobus::RETURN_EMPTY );
						?>
						<input data-language="<?php echo $language; ?>"
						       id="attribute_label_<?php echo $language; ?>"
						       class="wpglobus-wc-attribute-label wpglobus-translatable" type="text"
						       value="<?php echo esc_attr(
							       WPGlobus_Core::text_filter( $label, $language, $return ) ); ?>"
						       name="attribute_label_<?php echo $language; ?>"
						       placeholder="<?php echo esc_attr(
							       WPGlobus::Config()->language_name[ $language ] . ' (' .
							       WPGlobus::Config()->en_language_name[ $language ] . ')'
						       ); ?>">
						<br/>
					<?php endforeach; ?>
				</div>
			<?php

			}

		}

		/**
		 * Filter for attribute taxonomies
		 * @see   wc_get_attribute_taxonomies()
		 * *
		 * @scope admin
		 *        NOTE: the function is called in many admin and front places.
		 *        However, the effect is visible only in admin.
		 *        The rest should be covered by other filters.
		 * @see   WC_Admin_Attributes::add_attribute() - *NEEDED*
		 * @see   WC_Meta_Box_Product_Data::output() - *NEEDED*
		 * @see   WC_Post_Types::register_taxonomies() - WORKS WITH OR WITHOUT
		 * @see   WC_Query::layered_nav_init() - WORKS WITHOUT
		 * @see   WC_Widget_Layered_Nav::init_settings() - WORKS WITHOUT
		 * @see   wc_get_attribute_taxonomy_names() - USELESS
		 *
		 * @param array $attribute_taxonomies
		 *
		 * @return array
		 */
		public static function filter__attribute_taxonomies( $attribute_taxonomies ) {

			foreach ( $attribute_taxonomies as &$tax ) {
				$tax->attribute_label = WPGlobus_Filters::filter__text( $tax->attribute_label );
			}

			return $attribute_taxonomies;

		}


	} // class

endif;

# --- EOF
