<?php
/**
 * Controller for both Front and Admin filters
 * @package WPGlobus WC
 */

/**
 * Filter the privacy policy texts.
 *
 * @since 3.4.0
 */
add_filter( 'woocommerce_get_privacy_policy_text', array(
	'WPGlobus_Filters',
	'filter__text',
), 0 );

/**
 * Filter the Terms and Conditions checkbox text.
 * (visible on Checkout; editable in Customizer)
 * @see wc_get_terms_and_conditions_checkbox_text()
 *
 * @since 3.4.0
 */
add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', array(
	'WPGlobus_Filters',
	'filter__text',
), 0 );

/**
 * Actions on create order.
 *
 * @param WC_Order $order The current order object.
 *
 * @since 3.2.0
 */
add_action( 'woocommerce_checkout_create_order', array(
	'WPGlobus_WC_Filters',
	'action__woocommerce_checkout_create_order'
));

/**
 * Two filters to "un-translate" what Woo are doing wrong.
 * @link https://github.com/woothemes/woocommerce/issues/8122
 */

add_filter( 'woocommerce_taxonomy_args_product_cat', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_taxonomy_args_product_cat'
), 0 );
add_filter( 'woocommerce_taxonomy_args_product_tag', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_taxonomy_args_product_tag'
), 0 );

add_filter( 'woocommerce_order_item_name', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_order_item_name'
), 0 );

add_filter( 'woocommerce_order_get_items', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_order_get_items'
), 0 );

/**
 * Translate shipping method names, such as "Flat Rate"
 * Printed in the cart-shipping.php template
 * @see wc_cart_totals_shipping_method_label()
 */
add_filter( 'woocommerce_cart_shipping_method_full_label', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );

/**
 * @see   WC_Payment_Gateway::get_title()
 */
add_filter( 'woocommerce_gateway_title', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_gateway_title'
), 0 );

/**
 * Translate payment gateway title
 * ('Cash on Delivery', etc.)
 * and Purchase Note
 */
add_filter( 'get_post_metadata', array(
	'WPGlobus_WC_Filters',
	'filter__postmeta'
), 0, 3 );

/**
 * Localize WC attribute labels
 * WooCommerce is taking attribute labels directly from DB, unfiltered, and applies own filter.
 */
add_filter( 'woocommerce_attribute_label', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );


/**
 * Localize WC price suffix
 * @see WC_Product::get_price_suffix()
 * admin.php?page=wc-settings&tab=tax&section
 */
add_filter( 'woocommerce_get_price_suffix', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );

/**
 * Localize `add to cart` buttons in archives and single product pages.
 * Needed, for example, if WC Subscriptions plugin is used
 * @see WC_Product::single_add_to_cart_text
 * @see WC_Product::add_to_cart_text
 */
add_filter( 'woocommerce_product_add_to_cart_text', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );
add_filter( 'woocommerce_product_single_add_to_cart_text', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );


/**
 * This filter has limited scope in WPGlobus.
 * Here we need to extend it.
 * So, we negate the WPGlobus condition and add own filter.
 */
if ( ! ( WPGlobus_WP::is_doing_ajax() || ! is_admin() || WPGlobus_WP::is_pagenow( 'nav-menus.php' ) ) ) {
	add_filter( 'get_term', array( 'WPGlobus_WC_Filters', 'filter__get_term' ), 0 );
}

/**
 * Two filters to support multilingual email subjects and headings in WC 2.6+
 * @since 1.6.1
 */
add_filter( 'woocommerce_email_format_string_replace',
	array( 'WPGlobus_WC_Filters', 'filter__woocommerce_email_format_string_replace' ), 0
);
add_filter( 'woocommerce_email_get_option',
	array( 'WPGlobus_WC_Filters', 'filter__woocommerce_email_get_option' ), 0, 4
);

/**
 * Filter blogname only in some specific cases.
 * @since 1.6.2
 */
add_filter( 'option_blogname', array( 'WPGlobus_WC_Filters', 'filter__option_blogname' ), 0 );

/**
 * Filter texts in the list of available payment gateways.
 * @since 1.6.4
 */
add_filter( 'woocommerce_available_payment_gateways',
	array( 'WPGlobus_WC_Filters', 'filter__woocommerce_available_payment_gateways' ),
	0
);

/**
 * Filter the tax label - need it for the custom taxes if their names are multilingual.
 * This works, for example, in admin - view order.
 * @since 3.1.2
 */
add_filter( "woocommerce_order_get_tax_totals",
	array( 'WPGlobus_WC_Filters', 'filter__tax_label' ),
	PHP_INT_MAX
);

/**
 * Filter the tax label - need it for the custom taxes if their names are multilingual.
 * This works, for example, on the checkout page.
 * @since 3.1.2
 */
add_filter( "woocommerce_cart_tax_totals",
	array( 'WPGlobus_WC_Filters', 'filter__tax_label' ),
	PHP_INT_MAX
);
