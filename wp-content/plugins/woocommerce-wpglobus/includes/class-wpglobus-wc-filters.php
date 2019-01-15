<?php
/**
 * Filters and actions
 * Only the methods are here. The add_filter calls are in the Controller.
 * @package WPGlobus WC
 */

/**
 * Class WPGlobus_WC_Filters
 */
class WPGlobus_WC_Filters {

	/**
	 * Actions on create order.
	 *
	 * @param WC_Order $order The current order object.
	 *
	 * @since 3.2.0
	 */
	public static function action__woocommerce_checkout_create_order( $order ) {
		// Save the current locale to the order meta.
		$order->update_meta_data( WPGlobus_WC::META_ORDER_LOCALE, get_locale() );
	}

	/**
	 * WooCommerce 3.0 introduces "Get property" method, with filters.
	 * The filters are applied only in the "view" context.
	 * @see   \WC_Data::get_prop
	 * @since 1.7.0
	 */

	/**
	 * Variation name in the shopping cart is built from the product name
	 * and the term(s) name. The standard filtering will keep only the first
	 * language block. Need to use the `extract_text` method instead.
	 *
	 * @param string $name The variation name.
	 *
	 * @return string Part of the name for the current language.
	 * @since 1.7.0
	 */
	public static function filter__woocommerce_product_variation_get_name( $name = '' ) {
		if ( method_exists( 'WPGlobus_Core', 'extract_text' ) ) {
			// Available since WPGlobus 1.7.9
			$name = WPGlobus_Core::extract_text( $name );
		} else {
			$name = WPGlobus_Core::text_filter( $name );
		}

		return $name;
	}


	/**
	 * In the `product-image.php` template, WC builds the image HTML using the
	 * `$image_title       = $thumbnail_post->post_content;`
	 * construction for the `title="..."` attribute.
	 * As of WC 3.0, there is no filter for this specific attribute, so we have to
	 * filter the entire HTML tag.
	 *
	 * The `WPGlobus_Core::extract_text( $html );` method is not used here
	 * because it won't return text in default language.
	 *
	 * @param string $html The `<img>` HTML tag.
	 *
	 * @return string HTML with the filtered `title` attribute.
	 * @since 1.7.0
	 */
	public static function fix_image_title_attribute( $html ) {
		return preg_replace_callback( '/title="(.+?)"/', array(
			__CLASS__,
			'fix_image_title_attribute_callback'
		), $html );
	}

	/**
	 * Callback for @see WPGlobus_WC_Filters::fix_image_title_attribute
	 *
	 * @param string[] $matches The `regex matches` array.
	 *
	 * @return string The title tag.
	 * @since 1.7.0
	 */
	public static function fix_image_title_attribute_callback( $matches ) {
		return 'title="' . WPGlobus_Filters::filter__text( $matches[1] ) . '"';
	}

	/**
	 * Filter product's properties.
	 * When a @see \WC_Data::get_prop is called, the filter is appied.
	 * So, we only need to @see \WC_Data::set_prop them back.
	 *
	 * This is an action. The $product object is passed by reference. No need to return.
	 *
	 * @param WC_Product $product The product.
	 *
	 * @since 3.1.0
	 */
	public static function localize_product( $product ) {
		foreach ( array( 'name', 'short_description', 'description' ) as $_prop ) {
			$func_set = 'set_' . $_prop;
			$func_get = 'get_' . $_prop;
			$product->$func_set( $product->$func_get() );
		}
	}


	/**
	 * Legacy (before WC 3.0) methods still may be available with their own filters.
	 * @todo Check after the next WC release.
	 */

	/**
	 * Fix "Shop" name translation in Woo breadcrumbs. (They do not filter post->title there)
	 * @see woocommerce_breadcrumb (over-function)
	 *
	 * @param array $breadcrumbs
	 *
	 * @return array
	 */
	public static function filter__woocommerce_breadcrumbs( $breadcrumbs ) {
		$language = WPGlobus::Config()->language;
		foreach ( $breadcrumbs as &$breadcrumb ) {
			$breadcrumb[0] = WPGlobus_Core::text_filter( $breadcrumb[0], $language );
		}

		return $breadcrumbs;
	}

	/**
	 * @link https://github.com/woothemes/woocommerce/issues/8122
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function filter__woocommerce_taxonomy_args_product_cat( Array $args ) {
		$permalinks              = get_option( 'woocommerce_permalinks' );
		$args['rewrite']['slug'] =
			empty( $permalinks['category_base'] ) ? 'product-category' : $permalinks['category_base'];

		return $args;
	}

	/**
	 * @link https://github.com/woothemes/woocommerce/issues/8122
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function filter__woocommerce_taxonomy_args_product_tag( Array $args ) {
		$permalinks              = get_option( 'woocommerce_permalinks' );
		$args['rewrite']['slug'] =
			empty( $permalinks['tag_base'] ) ? 'product-tag' : $permalinks['tag_base'];

		return $args;
	}

	/**
	 * @see   WC_Abstract_Order::set_payment_method()
	 * @scope all
	 *
	 * @param string $title String to filter.
	 *
	 * @return string The filtered string.
	 */
	public static function filter__woocommerce_gateway_title( $title ) {
		/**
		 * Keep untranslated string when storing `_payment_method_title` into the `postmeta` table
		 */
		if ( ! WPGlobus_WP::is_function_in_backtrace( 'process_checkout' ) ) {
			$title = WPGlobus_Filters::filter__text( $title );
		}

		return $title;
	}

	/**
	 * @see   WC_Payment_Gateway::get_description()
	 * @scope front,ajax
	 *
	 * @param string $message String to filter.
	 *
	 * @return string The filtered string.
	 */
	public static function filter__woocommerce_gateway_description( $message ) {
		return WPGlobus_Filters::filter__text( $message );
	}

	/**
	 * @see woocommerce_demo_store()
	 *
	 * @param string $message String to filter.
	 *
	 * @return string The filtered string.
	 */
	public static function filter__option_woocommerce_demo_store_notice( $message ) {
		return WPGlobus_Filters::filter__text( $message );
	}

	/**
	 * Filter @see get_term() - additional to the one in
	 * @see WPGlobus_Filters::filter__get_term
	 *
	 * @param string|stdClass $term Term to filter.
	 *
	 * @return string|stdClass The filtered term.
	 */
	public static function filter__get_term( $term ) {

		/**
		 * For the "View/Edit Order" page:
		 * - Post type is 'shop_order'
		 * - Action is 'edit'. Otherwise, we run on the list as well.
		 */
		$post = get_post();
		if ( $post && $post->post_type === 'shop_order' && WPGlobus_WP::is_http_get_action( 'edit' ) ) {
			WPGlobus_Core::translate_term( $term, WPGlobus::Config()->language );
		}


		return $term;

	}

	/**
	 * @see WC_Settings_API::init_settings()
	 *
	 * @param string[] $settings
	 *
	 * @return string[]
	 */
	public static function filter__option_woocommerce_gateway_settings( $settings ) {
		if ( ! empty( $settings['instructions'] ) ) {
			$settings['instructions'] = WPGlobus_Filters::filter__text( $settings['instructions'] );
		}

		return $settings;
	}

	/**
	 * Translate product titles
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public static function filter__woocommerce_product_title( $title ) {
		/**
		 * Keep untranslated string when storing orders into the `woocommerce_order_items` table
		 */
		if ( ! WPGlobus_WP::is_function_in_backtrace( 'process_checkout' ) ) {
			$title = WPGlobus_Filters::filter__text( $title );
		}

		return $title;
	}

	/**
	 * Translate order attributes on the 'order-received' page.
	 * @see   WC_Abstract_Order::get_order_item_totals()
	 * @scope front
	 *
	 * @param array $total_rows
	 *
	 * @return array
	 */
	public static function filter__woocommerce_get_order_item_totals( $total_rows ) {

		if ( ! empty( $total_rows['shipping']['value'] ) ) {
			$total_rows['shipping']['value'] = WPGlobus_Filters::filter__text(
				$total_rows['shipping']['value'] );
		}

		return $total_rows;
	}

	/**
	 * Translate payment gateway title ('Cash on Delivery', etc.)
	 * This is needed for the 'order-received' page and for the
	 * list of orders in Admin (/wp-admin/edit.php?post_type=shop_order)
	 * *
	 * The payment title is displayed there as: `$order->payment_method_title`, without any filters.
	 * The `$order` calls `__get()` method, which gets the '_payment_method_title' from `postmeta`.
	 * *
	 * We want to store the title as a multilingual string, so the only way to translate it
	 * is to interrupt @see get_metadata() before it goes to get meta from the meta cache.
	 * *
	 * @since 1.2.0 : added `_purchase_note` meta processing. Method renamed.
	 *
	 * @param string $value     Null is passed. We set the value.
	 * @param int    $object_id Post ID (post type is 'shop_order')
	 * @param string $meta_key  Passed by the filter. We need only one key.
	 *
	 * @return string
	 */
	public static function filter__postmeta( $value, $object_id, $meta_key ) {
		/**
		 * WC may call it many times on one page. Let's cache.
		 */
		static $_cache;

		/**
		 * Check for scope
		 * @since 1.6.0
		 */
		if ( is_admin() ) {
			$haystack = array( '_payment_method_title' );
		} else {
			$haystack = array( '_payment_method_title', '_purchase_note' );
		}

		if ( in_array( $meta_key, $haystack, true ) ) {
			if ( isset( $_cache[ $meta_key ][ $object_id ] ) ) {
				$value = $_cache[ $meta_key ][ $object_id ];
			} else {
				/** @global wpdb $wpdb */
				global $wpdb;
				$meta_value = $wpdb->get_var( $wpdb->prepare(
					"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d LIMIT 1;",
					$meta_key, $object_id ) );

				if ( ! empty( $meta_value ) ) {
					$value = WPGlobus_Filters::filter__text( $meta_value );
				}
				$_cache[ $meta_key ][ $object_id ] = $value;
			}

		}

		return $value;
	}

	/**
	 * 'woocommerce_order_item_name' might arrive here either clean or wrapped with HTML
	 * For example, on the `order-received` page, it's wrapped with link if the product is visible,
	 * and comes clean if not.
	 * @link https://github.com/woothemes/woocommerce/pull/8159
	 * @example
	 *  <a href="/shop/clothing/happy-ninja/">{:en}Happy Ninja{:}{:ru}Title_RU{:}</a>
	 * Because we know our pattern {:[a-z]{2}}.+{:}, we can replace just our part.
	 *
	 * @param $sz
	 *
	 * @return mixed
	 */
	public static function filter__woocommerce_order_item_name( $sz ) {

		return preg_replace_callback(
			'/' . sprintf( WPGlobus::LOCALE_TAG, '[a-z]{2}', '.+' ) . '/',
			array( __CLASS__, '_preg_replace_callback__filter__text' ),
			$sz );

	}

	/**
	 * Translate order item names:
	 * when on Admin->Edit Order page
	 * @see   WC_Abstract_Order::get_items()
	 * when names are sent to PayPal Standard
	 * @see   WC_Gateway_Paypal_Request::prepare_line_items()
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public static function filter__woocommerce_order_get_items( $items ) {

		foreach ( $items as $id => $item ) {
			if ( ! empty( $item['name'] ) ) {
				$items[ $id ]['name'] = WPGlobus_Filters::filter__text( $item['name'] );
			}
		}

		return $items;
	}

	/**
	 * Callback for @see filter__woocommerce_order_item_name()
	 *
	 * @param $matches
	 *
	 * @return string
	 */
	protected static function _preg_replace_callback__filter__text( $matches ) {
		return WPGlobus_Filters::filter__text( $matches[0] );
	}

	/**
	 * Specify meta keys that may have multilingual content.
	 * @see   WPGlobus_Filters::set_multilingual_meta_keys
	 *
	 * @scope front
	 *
	 * @since 1.4.0
	 *
	 * @param array $multilingual_meta_keys
	 *
	 * @return array
	 */
	public static function filter__wpglobus_multilingual_meta_keys( $multilingual_meta_keys ) {

		$multilingual_meta_keys['_variation_description'] = true;

		/**
		 * Enable multilingual in the ALT image attributes in Media.
		 * Visible in the lightbox captions.
		 * @note  This should not be run on admin, because we do not have the UEdit
		 *       interface in Media, so filtering there would delete all languages.
		 * @todo  Consider having this in a core plugin filter. Waiting for a use case.
		 *
		 * @since 1.5.5
		 */
		$multilingual_meta_keys['_wp_attachment_image_alt'] = true;

		return $multilingual_meta_keys;

	}

	/**
	 * @see   WC_Email::__construct for the list of replacement pairs.
	 *
	 * @param string[] $replace
	 *
	 * @return string[]
	 * @since 1.6.1
	 */
	public static function filter__woocommerce_email_format_string_replace( $replace ) {
		foreach ( array( 'blogname', 'site-title' ) as $key ) {
			if ( isset( $replace[ $key ] ) ) {
				$replace[ $key ] = WPGlobus_Filters::filter__text( $replace[ $key ] );
			}
		}

		return $replace;
	}

	/**
	 * Filter email heading and subject when retrieved from options.
	 * @scope All, except for the WC Settings admin page.
	 *
	 * @param string   $value       String to filter.
	 * @param WC_Email $_this       The WC Email object (unused).
	 * @param string   $value_again No idea why WC passes it twice (unused).
	 * @param string   $key         Name of the option.
	 *
	 * @return string The filtered string.
	 * @since 1.6.1
	 */
	public static function filter__woocommerce_email_get_option(
		$value,
		/** @noinspection PhpUnusedParameterInspection */
		$_this,
		/** @noinspection PhpUnusedParameterInspection */
		$value_again,
		$key
	) {
		if ( ( 'heading' === $key || 'subject' === $key ) &&
		     ! WPGlobus_WP::is_plugin_page( 'wc-settings' )
		) {
			$value = WPGlobus_Filters::filter__text( $value );
		}

		return $value;
	}

	/**
	 * Filter blogname only in some specific cases.
	 *
	 * @param string $blogname String to filter.
	 *
	 * @return string The filtered string.
	 * @since 1.6.2
	 */
	public static function filter__option_blogname( $blogname ) {

		/**
		 * There is a call to `get_bloginfo( 'name' )`
		 * in the WC email header template
		 * plugins/woocommerce/templates/emails/email-header.php
		 */
		if ( WPGlobus_WP::is_function_in_backtrace(
			array( 'WC_Emails', 'email_header' ) )
		) {
			$blogname = WPGlobus_Filters::filter__text( $blogname );
		}

		return $blogname;
	}

	/**
	 * Filter texts in the list of available payment gateways.
	 * Use case: "Any payment method" dropdown in the list of Subscriptions.
	 * @see   WCS_Admin_Post_Types::restrict_by_payment_method
	 *
	 * @param WC_Payment_Gateway[] $available_gateways The array of gateways.
	 *
	 * @return WC_Payment_Gateway[] Filtered.
	 *
	 * @since 1.6.4
	 */
	public static function filter__woocommerce_available_payment_gateways( $available_gateways ) {
		foreach ( $available_gateways as $gateway ) {
			self::filter_ref__wc_payment_gateway( $gateway );
		}

		return $available_gateways;

	}

	/**
	 * Filter WC_Payment_Gateway object by reference.
	 *
	 * @param WC_Payment_Gateway $gateway The object.
	 *
	 * @return void This is a filter by reference.
	 * @since 1.6.4
	 */
	public static function filter_ref__wc_payment_gateway( &$gateway ) {
		/**
		 * These are the fields that we allow to translate in admin.
		 *
		 * @var string[]
		 */
		static $fields = array(
			'description',
			'title',
		);

		foreach ( $fields as $field ) {
			// 1. The main area.
			/** @noinspection PhpVariableVariableInspection */
			if ( isset( $gateway->$field ) && is_string( $gateway->$field ) ) {
				/** @noinspection PhpVariableVariableInspection */
				$gateway->$field = WPGlobus_Filters::filter__text( $gateway->$field );
			}
			// 2. The same fields are duplicated in the `settings` array.
			if ( isset( $gateway->settings[ $field ] ) && is_string( $gateway->settings[ $field ] ) ) {
				$gateway->settings[ $field ] = WPGlobus_Filters::filter__text( $gateway->settings[ $field ] );
			}
		}
	}

	/**
	 * Filter breadcrumbs.
	 * Use case: product attribute term archive.
	 *
	 * @param array $crumbs Breadcrumb trail.
	 *
	 * @return array Filtered.
	 * @since 1.6.4
	 */
	public static function filter__woocommerce_get_breadcrumb( $crumbs ) {
		foreach ( $crumbs as &$crumb ) {
			isset( $crumb[0] ) && $crumb[0] = WPGlobus_Filters::filter__text( $crumb[0] );
		}

		return $crumbs;
	}

	/**
	 * Filter the tax label - need it for the custom taxes if their names are multilingual.
	 *
	 * @param array $tax_totals The array of Tax Totals objects.
	 *
	 * @return array
	 * @since 3.1.2
	 */
	public static function filter__tax_label( $tax_totals ) {
		foreach ( $tax_totals as $code => $data ) {
			$data->label = WPGlobus_Filters::filter__text( $data->label );
		}

		return $tax_totals;
	}
}

/*EOF*/
