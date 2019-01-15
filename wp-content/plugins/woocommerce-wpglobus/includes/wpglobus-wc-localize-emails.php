<?php
/**
 * Localized email classes for WooCommerce.
 *
 * @since 3.4.0
 */

/** @noinspection PhpLanguageLevelInspection */
// phpcs:disable Generic.Files.OneClassPerFile.MultipleFound

class WPGlobus_WC_Email_Customer_Invoice extends WC_Email_Customer_Invoice {
	use WC_Mail_Actions_Trait;
}

class WPGlobus_WC_Email_Customer_On_Hold_Order extends WC_Email_Customer_On_Hold_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_status_pending_to_on-hold_notification',
		'woocommerce_order_status_failed_to_on-hold_notification',
	];
}

class WPGlobus_WC_Email_Customer_Note extends WC_Email_Customer_Note {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_new_customer_note_notification',
	];
}

class WPGlobus_WC_Email_Customer_Completed_Order extends WC_Email_Customer_Completed_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_status_completed_notification',
	];
}

class WPGlobus_WC_Email_Customer_Refunded_Order extends WC_Email_Customer_Refunded_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_fully_refunded_notification',
		'woocommerce_order_partially_refunded_notification',
	];
}

class WPGlobus_WC_Email_Customer_Processing_Order extends WC_Email_Customer_Processing_Order {
	use WC_Mail_Actions_Trait;
	protected $parent_filters_to_remove = [
		'woocommerce_order_status_failed_to_processing_notification',
		'woocommerce_order_status_on-hold_to_processing_notification',
		'woocommerce_order_status_pending_to_processing_notification',
	];
}
