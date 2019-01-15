<?php
/**
 * WC_Mail_Actions
 *
 * @copyright TIV.NET INC. All Rights Reserved.
 *
 * @version   0.0.1
 * @note      FOR INTERNAL USE ONLY!!! Name and location of this file are not guaranteed to stay.
 */

/** @noinspection PhpLanguageLevelInspection */

trait WC_Mail_Actions_Trait {

	/**
	 * Constructor.
	 */
	public function __construct() {
		/** @noinspection PhpUndefinedClassInspection */
		parent::__construct();

		$this->remove_parent_filters();
	}

	/**
	 * Each email class add hooks in its Constructor.
	 * When we replace a WC class with our version, the hooks are already set.
	 * If we do not remove the parent hooks, we'll be sending duplicate emails.
	 *
	 * @todo Check if we can delete all parent hooks without hard-coding the specific tags in `parent_filters_to_remove`.
	 */
	protected function remove_parent_filters() {
		if ( ! empty( $this->parent_filters_to_remove ) ) {
			$parent_class_name = get_parent_class( $this );
			foreach ( $this->parent_filters_to_remove as $tag ) {
				$this->unhook( $parent_class_name, $tag );
			}
		}
	}

	/**
	 * Remove all filters by class name and tag.
	 *
	 * @param string $class_name
	 * @param string $tag
	 */
	protected function unhook( $class_name, $tag ) {

		/** @global WP_Hook[] $wp_filter */
		global $wp_filter;

		$the_hook = $wp_filter[ $tag ];

		$callbacks_to_remove = array();

		foreach ( $the_hook as $priority => $callbacks ) {
			foreach ( $callbacks as $cb_id => $cb ) {
				$callback = $cb['function'];
				if (
					isset( $callback[0], $callback[1] )
					&& get_class( $callback[0] ) === $class_name
				) {
					$callbacks_to_remove[] = array(
						'priority' => $priority,
						'function' => $callback,
					);
				}
			}
		}

		foreach ( $callbacks_to_remove as $cb ) {
			remove_filter( $tag, $cb['function'], $cb['priority'] );
		}
	}

	/**
	 * Parse the arguments sent to the `trigger()` method and get the Order object from them.
	 *
	 * @param int|array      $args    The order ID or array of named arguments.
	 * @param WC_Order|false $a_order Order object, or Partial/Full refund (bool).
	 *
	 * @return WC_Order|false The order object or False if order not passed or not found.
	 */
	protected function get_order_from_trigger_args( $args, $a_order = false ) {

		/**
		 * Some classes pass $args array instead.
		 *
		 * @see WC_Email_Customer_Note::trigger()
		 */
		if ( is_array( $args ) && isset( $args['order_id'] ) ) {
			$order_id = $args['order_id'];
		} else {
			$order_id = $args;
		}

		if ( $order_id && ! is_a( $a_order, 'WC_Order' ) ) {
			// Order ID passed but $order object - not.
			$order = wc_get_order( $order_id );
		} else {
			$order = $a_order;
		}

		return $order;
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int|array      $args      The order ID or array of named arguments.
	 * @param WC_Order|false $a_order   Order object, or Partial/Full refund (bool).
	 * @param int            $refund_id Refund ID (in the "Refunded Order" email.
	 */
	public function trigger( $args, $a_order = false, $refund_id = null ) {

		$order = $this->get_order_from_trigger_args( $args, $a_order );

		/**
		 * @param WC_Order|false $order
		 */
		do_action( 'wc_mail_actions_before_trigger', $order );

		if ( is_array( $args ) && isset( $args['order_id'] ) ) {
			/** @noinspection PhpUndefinedClassInspection */
			parent::trigger( $args );
		} else {
			/** @noinspection PhpUndefinedClassInspection */
			parent::trigger( $args, $a_order, $refund_id );
		}

		/**
		 * @param WC_Order|false $order
		 */
		do_action( 'wc_mail_actions_after_trigger', $order );

	}
}
