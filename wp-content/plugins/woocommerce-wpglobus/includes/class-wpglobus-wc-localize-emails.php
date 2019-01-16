<?php
/**
 * Copyright (c) 2018. TIV.NET INC. / WPGlobus. All Rights Reserved.
 */


class WPGlobus_WC_Localize_Emails {

	/**
	 * The hook priority.
	 *
	 * @var int
	 */
	const HOOK_PRIORITY = 4242;

	/**
	 * The "return false" callable.
	 *
	 * @var callable
	 */
	protected static $disable_it = array( __CLASS__, 'disable_it' );

	/**
	 * True if locale was switched before the trigger.
	 *
	 * @var bool
	 */
	protected $need_to_restore_locale = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup filters and actions.
	 */
	public function setup_hooks() {
		add_filter( 'woocommerce_email_classes', array( $this, 'override_email_classes' ), self::HOOK_PRIORITY );
		add_action( 'wc_mail_actions_before_trigger', array( $this, 'maybe_switch_locale' ) );
		add_action( 'wc_mail_actions_after_trigger', array( $this, 'maybe_restore_locale' ) );
	}

	/**
	 * Replace WC classes with our inherited.
	 *
	 * @param array $email_classes The list of email classes.
	 *
	 * @return array The updated list.
	 */
	public function override_email_classes( $email_classes ) {
		require_once __DIR__ . '/wpglobus-wc-localize-emails.php';

		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once __DIR__ . '/wpglobus-wcs-localize-emails.php';
		}

		foreach ( $email_classes as $class => $object ) {
			$replace_with = 'WPGlobus_' . $class;
			if ( class_exists( $replace_with ) ) {
				$email_classes[ $class ] = new $replace_with();
			}
		}

		return $email_classes;
	}

	/**
	 * Switch locale if order has a different one.
	 *
	 * @param $order
	 */
	public function maybe_switch_locale( $order ) {

		$this->need_to_restore_locale = false;

		if ( $order ) {
			$switch_to = $this->locale_to_switch_to( $order );
			if ( $switch_to ) {
				$this->switch_to_locale( $switch_to );
				$this->need_to_restore_locale = true;
			}
		}
	}

	/**
	 * Restore locale if was switched previously.
	 */
	public function maybe_restore_locale() {
		if ( $this->need_to_restore_locale ) {
			$this->restore_locale();
		}
	}


	/**
	 * Parse the arguments sent to the `trigger()` method and detect if the current locale needs to be switched.
	 *
	 * @param WC_Order|false $order Order object, or Partial/Full refund (bool).
	 *
	 * @return string Locale to switch to or empty string if switching is not required.
	 */
	protected function locale_to_switch_to( $order ) {

		$locale_to_switch_to = '';

		$current_locale = get_locale();

		if ( $order && is_a( $order, 'WC_Order' ) ) {
			$order_locale = $order->get_meta( WPGlobus_WC::META_ORDER_LOCALE );
			if ( $order_locale !== $current_locale ) {
				$locale_to_switch_to = $order_locale;
			}
		}

		return $locale_to_switch_to;
	}

	/**
	 * Switch to locale.
	 *
	 * @param string $locale Locale to switch to.
	 */
	protected function switch_to_locale( $locale ) {

		// Ignore the admin's locale.
		add_filter( 'wpglobus_use_admin_wplang', self::$disable_it );

		// Switch WP and WPGlobus (hooked to) locales.
		switch_to_locale( $locale );

		// Switch WooCommerce's locale.
		wc_switch_to_site_locale();

		// Reload WCS translations.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			WC_Subscriptions::load_plugin_textdomain();
		}

		// Tell WC not to switch it's locale again because we did it already.
		add_filter( 'woocommerce_email_setup_locale', self::$disable_it, PHP_INT_MAX );
		add_filter( 'woocommerce_email_restore_locale', self::$disable_it, PHP_INT_MAX );
	}

	/**
	 * Restore everything we did in @see WPGlobus_WC_Localize_Emails::switch_to_locale().
	 */
	protected function restore_locale() {
		wc_restore_locale();
		if ( class_exists( 'WC_Subscriptions' ) ) {
			WC_Subscriptions::load_plugin_textdomain();
		}
		remove_filter( 'woocommerce_email_setup_locale', self::$disable_it, PHP_INT_MAX );
		remove_filter( 'woocommerce_email_restore_locale', self::$disable_it, PHP_INT_MAX );
		remove_filter( 'wpglobus_use_admin_wplang', self::$disable_it );
	}

	/**
	 * Using this method instead of @see __return_false() so we can safely remove only our filters.
	 *
	 * @return false
	 */
	public static function disable_it() {
		return false;
	}
}
