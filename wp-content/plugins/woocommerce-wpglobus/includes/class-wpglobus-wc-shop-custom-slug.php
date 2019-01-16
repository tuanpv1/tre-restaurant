<?php

/**
 * Handle custom language-dependent slugs for the Shop page.
 * Loaded only if the WPGlobus Plus "Slug" module is active.
 * @since 1.7.0
 */
class WPGlobus_WC_Shop_Custom_Slug {

	/**
	 * WPGlobus_WC_Shop_Custom_Slug constructor.
	 */
	public function __construct() {
		add_filter( 'option_rewrite_rules', array( $this, 'localize_rewrite_rules' ), PHP_INT_MAX );
		add_filter( 'wpglobus_pre_localize_current_url', array( $this, 'localize_shop_url' ), PHP_INT_MAX, 2 );
	}


	/**
	 * WooCommerce uses rewrite rules to convert the "shop" page into a product archive.
	 * Those rules use the default slug.
	 * WPGlobus Plus allows changing the default slug for each language.
	 * Here we adjust the rewrite rules by using the custom slug from WPGlobus Plus.
	 *
	 * Without this, WooCommerce produces a fatal error on the shop page with a custom slug.
	 *
	 * @param array|string $rules The array ot rewrite rules. In some cases, an empty string.
	 *
	 * @return array|string Adjusted rules.
	 */
	public function localize_rewrite_rules( $rules ) {

		if ( empty( $rules ) ) {
			/**
			 * Nothing to do. Happens, for example, after @see \WP_Rewrite::flush_rules
			 */
			return $rules;
		}

		$language = WPGlobus::Config()->language;

		if ( $language === WPGlobus::Config()->default_language ) {
			return $rules;
		}

		$page_id_shop = wc_get_page_id( 'shop' );

		// Check if there is a custom slug for the Shop page in this `$language`.
		$slug_custom = (string) apply_filters( 'wpglobus_plus_get_custom_slug', '', $page_id_shop, $language );

		if ( $slug_custom ) {
			// This is the full path to the Shop page, relative to the site root.
			// If the Shop page has a parent, the URI will look like `parent/shop`.
			$uri_default = (string) get_page_uri( $page_id_shop );

			// This is a simplified URI of the translated page.
			// Assumed that the parent's slug is default.
			// TODO Must traverse and get the custom slug for the parent(s).
			$uri_custom = str_replace( basename( $uri_default ), $slug_custom, $uri_default );

			// Get all keys as an array and replace default slug with the custom slug.
			$search  = '/' . preg_quote( trailingslashit( $uri_default ), '/' ) . '/';
			$replace = trailingslashit( $uri_custom );
			$keys    = preg_replace( $search, $replace, array_keys( $rules ) );

			// Put the keys back to the Rules array.
			$rules = array_combine( $keys, $rules );
		}

		return $rules;
	}

	/**
	 * WPGlobus Plus does not process the Shop page correctly because
	 * it's not a real page but a product archive.
	 * This filter works after the main filter from Plus, only on the Shop page.
	 * It returns the correct Shop URL with the custom language slug.
	 *
	 * Without it, the menu and widget language switchers show only the default Shop URL.
	 *
	 * @param string $url This should be empty. If not then Plus already found the URL.
	 * @param string $language The target language.
	 *
	 * @return string The Shop URL.
	 */
	public function localize_shop_url( $url = '', $language = '' ) {
		// Bail out if:
		if (
			// Someone already found the URL. Let's not interfere.
			$url
			// Can't work without knowing the target language.
			|| ! $language
			// We are not on the Shop page.
			|| ! is_shop()
			// Alternative to `is_shop` is
			// $page_id_current = url_to_postid( WPGlobus_Utils::current_url() );
			// compare to
			// $page_id_shop = wc_get_page_id( 'shop' );
		) {
			return $url;
		}

		// Check if the Slug module active.
		if ( ! class_exists( 'WPGlobusPlus_Slug', false ) ) {
			return $url;
		}

		$cache_group = 'woocommerce-wpglobus-shop-url';
		if ( $cached_url = wp_cache_get( $language, $cache_group ) ) {
			return $cached_url;
		}

		// The easiest way to get the custom URL is to temporarily switch the language
		// and use the standard method, and let WPGlobus Plus apply the necessary filters.
		$old_language = WPGlobus::Config()->language;
		WPGlobus::Config()->set_language( WPGlobus::Config()->locale[ $language ] );
		$url = get_page_link( wc_get_page_id( 'shop' ) );
		WPGlobus::Config()->set_language( WPGlobus::Config()->locale[ $old_language ] );

		wp_cache_add( $language, $url, $cache_group );

		return $url;
	}
}
