<?php
/**
 * Localized email classes for WooCommerce Subscriptions.
 *
 * @since 3.4.0
 */

/** @noinspection PhpLanguageLevelInspection */
// phpcs:disable Generic.Files.OneClassPerFile.MultipleFound

class WPGlobus_WCS_Email_Processing_Renewal_Order extends WCS_Email_Processing_Renewal_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_status_pending_to_processing_renewal_notification',
		'woocommerce_order_status_pending_to_on-hold_renewal_notification',
	];
}

class WPGlobus_WCS_Email_Completed_Renewal_Order extends WCS_Email_Completed_Renewal_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_status_completed_renewal_notification',
	];
}

class WPGlobus_WCS_Email_Completed_Switch_Order extends WCS_Email_Completed_Switch_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_subscriptions_switch_completed_switch_notification',
	];
}

class WPGlobus_WCS_Email_Customer_Renewal_Invoice extends WCS_Email_Customer_Renewal_Invoice {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_generated_manual_renewal_order_renewal_notification',
		'woocommerce_order_status_failed_renewal_notification',
	];
}
