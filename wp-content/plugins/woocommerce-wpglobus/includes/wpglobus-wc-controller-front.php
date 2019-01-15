<?php
/**
 * Front Controller
 * @package WPGlobus WC
 */

/**
 * WooCommerce 3.0 introduces "Get property" method, with filters.
 * The filters are applied only in the "view" context.
 * @see   WC_Data::get_prop
 * @since 1.7.0
 */

foreach ( array( 'name', 'short_description', 'description' ) as $_prop ) {
	add_filter( 'woocommerce_product_get_' . $_prop, array(
		'WPGlobus_Filters',
		'filter__text'
	), 0 );
}
unset( $_prop );

add_filter( 'woocommerce_product_variation_get_name', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_product_variation_get_name'
), 0 );

add_filter( 'woocommerce_single_product_image_thumbnail_html', array(
	'WPGlobus_WC_Filters',
	'fix_image_title_attribute'
), PHP_INT_MAX );

/**
 * Legacy (before WC 3.0) methods still may be available with their own filters.
 * @todo Check after the next WC release.
 */


/**
 * @see WC_Product::get_title()
 * WooCommerce does not apply the standard 'the_title' filter, and uses its own instead.
 */
add_filter( 'woocommerce_product_title', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_product_title'
), 0 );

/**
 * @see woocommerce/templates/single-product/short-description.php
 */
add_filter( 'woocommerce_short_description', array(
	'WPGlobus_Filters',
	'filter__text'
), 0 );

/**
 * @see   WC_Payment_Gateway::get_description()
 */
add_filter( 'woocommerce_gateway_description', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_gateway_description'
), 0 );

/**
 * @see woocommerce_demo_store()
 */
add_filter( 'option_woocommerce_demo_store_notice', array(
	'WPGlobus_WC_Filters',
	'filter__option_woocommerce_demo_store_notice'
), 0 );


/**
 * Gateway instructions are printed on the order-received (Thank you) page without filtering,
 * directly from the settings
 * @see WC_Gateway_BACS::__construct()
 * @see WC_Gateway_BACS::thankyou_page()
 */
add_filter( 'option_woocommerce_bacs_settings', array(
	'WPGlobus_WC_Filters',
	'filter__option_woocommerce_gateway_settings'
), 0 );
add_filter( 'option_woocommerce_cod_settings', array(
	'WPGlobus_WC_Filters',
	'filter__option_woocommerce_gateway_settings'
), 0 );
add_filter( 'option_woocommerce_cheque_settings', array(
	'WPGlobus_WC_Filters',
	'filter__option_woocommerce_gateway_settings'
), 0 );

/**
 * Translate order attributes on the 'order-received' page.
 */
add_filter( 'woocommerce_get_order_item_totals', array(
	'WPGlobus_WC_Filters',
	'filter__woocommerce_get_order_item_totals'
), 0 );

/**
 * @since 1.4.0
 */
add_filter( 'wpglobus_multilingual_meta_keys', array(
	'WPGlobus_WC_Filters',
	'filter__wpglobus_multilingual_meta_keys'
), 0 );

/**
 * Filter the order button text.
 * Appears on Checkout page woocommerce/templates/checkout/payment.php.
 *
 * Priority must be higher than the filter placed by the WooCommerce Subscriptions plugin:
 * @see   WC_Subscriptions::order_button_text
 * @since 1.6.2
 */
add_filter( 'woocommerce_order_button_text', array(
	'WPGlobus_Filters',
	'filter__text'
), 11 );

/**
 * Filter breadcrumbs.
 *
 * @since 1.6.4
 */
add_filter( 'woocommerce_get_breadcrumb',
	array( 'WPGlobus_WC_Filters', 'filter__woocommerce_get_breadcrumb' ),
	PHP_INT_MAX
);
