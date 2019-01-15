<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Send one notification per booking to admin and customer when an order contining bookings is made or when its status changes
 *
 * @since 1.2.2
 * @version 1.5.4
 * @param WC_Order $order
 * @param string $new_status
 * @param array $args
 */
function bookacti_send_notification_when_order_status_changes( $order, $new_status, $args = array() ) {

	if( is_numeric( $order ) ) {
		$order = wc_get_order( $order );
	}
	
	if( ! $order ) { return; }
	
	$action = isset( $_REQUEST[ 'action' ] ) ? $_REQUEST[ 'action' ] : '';
	
	// Check if the administrator must be notified
	// If the booking status is pending or booked, notify administrator, unless HE originated this change
	$notify_admin = 0;
	if(	 in_array( $new_status, array( 'booked', 'pending' ) )
	&& ! in_array( $action, array( 'woocommerce_mark_order_status', 'editpost' ) ) ) {
		$admin_notification	= bookacti_get_notification_settings( 'admin_new_booking' );
		$notify_admin		= $admin_notification[ 'active_with_wc' ] ? 1 : 0;
	}
	
	// Check if the customer must be notified
	$customer_notification	= bookacti_get_notification_settings( 'customer_' . $new_status . '_booking' );
	$notify_customer		= $customer_notification[ 'active_with_wc' ] ? 1 : 0;
	
	// If nobody needs to be notified, return
	if( ! $notify_admin && ! $notify_customer ) { return; }
	
	// Do not send notifications at all for transitionnal order status, 
	// because the booking is still considered as temporary
	$order_status = $order->get_status();
	if( $order_status === 'pending' && $new_status === 'pending' 
	||  $order_status === 'failed' && $new_status === 'cancelled' ) { return; }

	$order_items = $order->get_items();
	if( ! $order_items ) { return; }

	foreach( $order_items as $order_item_id => $item ) {
		
		// Check if the order item is a booking, or skip it
		if( ! $item || ( ! isset( $item[ 'bookacti_booking_id' ] ) && ! isset( $item[ 'bookacti_booking_group_id' ] ) ) ) { continue; }
		
		// If the state hasn't changed, do not send the notifications, unless it is a new order
		$old_status = isset( $args[ 'old_status' ] ) && $args[ 'old_status' ] ? $args[ 'old_status' ] : wc_get_order_item_meta( $order_item_id, 'bookacti_state', true );
		if( $old_status === $new_status && empty( $args[ 'is_new_order' ] ) ) { continue; }
		
		// Get booking ID and booking type ('single' or 'group')
		if( isset( $item[ 'bookacti_booking_id' ] ) ) {
			$booking_id		= $item[ 'bookacti_booking_id' ];
			$booking_type	= 'single';
		}
		else if( isset( $item[ 'bookacti_booking_group_id' ] ) ) {
			$booking_id		= $item[ 'bookacti_booking_group_id' ];
			$booking_type	= 'group';
		}
		
		// Send a booking confirmation to the customer
		if( $notify_customer ) {
			bookacti_send_notification( 'customer_' . $new_status . '_booking', $booking_id, $booking_type );
		}
		
		// Notify administrators that a new booking has been made
		if( $notify_admin ) {
			bookacti_send_notification( 'admin_new_booking', $booking_id, $booking_type );
		}
	}
}
add_action( 'bookacti_order_bookings_state_changed', 'bookacti_send_notification_when_order_status_changes', 10, 3 );


/**
 * Send notifications when a new order is made but stays in a pending state
 * 
 * @since 1.2.2
 * @param int $order_id
 * @param WC_Order $order
 */
function bookacti_send_notification_when_new_order_is_pending( $order_id, $order = null ) {
	bookacti_send_notification_when_order_status_changes( $order_id, 'pending', array( 'is_new_order' => true ) );
}
add_action( 'woocommerce_order_status_pending_to_processing', 'bookacti_send_notification_when_new_order_is_pending', 20, 2 );
add_action( 'woocommerce_order_status_pending_to_on-hold', 'bookacti_send_notification_when_new_order_is_pending', 20, 2 );


/**
 * Add a mention to notifications
 * 
 * @since 1.2.2 (was bookacti_add_wc_mention_to_notifications before)
 * @param array $notifications
 * @return array
 */
function bookacti_add_admin_refunded_booking_notification( $notifications ) {
	
	if( ! isset( $notifications[ 'admin_refunded_booking' ] ) ) {
		$notifications[ 'admin_refunded_booking' ] = array(
			'id'			=> 'admin_refunded_booking',
			'active'		=> 1,
			'title'			=> __( 'Customer has been refunded', BOOKACTI_PLUGIN_NAME ),
			'description'	=> __( 'This notification is sent to the administrator when a customer is successfully reimbursed for a booking.', BOOKACTI_PLUGIN_NAME ),
			'email'			=> array(
				'active'	=> 1,
				'to'		=> array( get_bloginfo( 'admin_email' ) ),
				'subject'	=> __( 'Booking refunded', BOOKACTI_PLUGIN_NAME ),
				/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email an administrator receive when a booking is refunded */
				'message'	=> __( '<p>A customer has been reimbursed for this booking:</p>
									<p>{booking_list}</p>
									<p>Contact him: {user_firstname} {user_lastname} ({user_email})</p>
									<p><a href="{booking_admin_url}">Click here</a> to edit this booking (ID: {booking_id}).</p>', BOOKACTI_PLUGIN_NAME ) )
		);
	}
	
	return $notifications;
}
add_filter( 'bookacti_notifications_default_settings', 'bookacti_add_admin_refunded_booking_notification', 10, 1 );


/**
 * Add WC-specific default notification settings
 * 
 * @since 1.2.2
 * @param array $notifications
 * @return array
 */
function bookacti_add_wc_default_notification_settings( $notifications ) {
	$add_settings_to = array( 
		'admin_new_booking', 
		'customer_pending_booking', 
		'customer_booked_booking', 
		'customer_cancelled_booking',
		'customer_refunded_booking'
	);
	
	foreach( $add_settings_to as $notification_id ) {
		if( isset( $notifications[ $notification_id ] ) ) {
			if( ! isset( $notifications[ $notification_id ][ 'active_with_wc' ] ) ) {
				$notifications[ $notification_id ][ 'active_with_wc' ] = 0;
			}
		}
	}
	
	return $notifications;
}
add_filter( 'bookacti_notifications_default_settings', 'bookacti_add_wc_default_notification_settings', 20, 1 );


/**
 * Sanitize WC-specific notifications settings
 * 
 * @since 1.2.2
 * @param array $notification
 * @param string $notification_id
 * @return array
 */
function bookacti_sanitize_wc_notification_settings( $notification, $notification_id ) {
	if( isset( $notification[ 'active_with_wc' ] ) ) {
		$notification[ 'active_with_wc' ] = intval( $notification[ 'active_with_wc' ] ) ? 1 : 0;
	}
	return $notification;
}
add_filter( 'bookacti_notification_sanitized_settings', 'bookacti_sanitize_wc_notification_settings', 20, 2 );


/**
 * Make sure that WC order data are up to date when a WC notification is sent
 * 
 * @since 1.2.2
 * @version 1.5.0
 * @param array $args
 * @return array
 */
function bookacti_wc_email_order_item_args( $args ) {
	
	// Check if the order contains bookings
	$has_bookings = false;
	foreach( $args[ 'items' ] as $item ) {
		if( isset( $item[ 'bookacti_booking_id' ] ) || isset( $item[ 'bookacti_booking_group_id' ] ) ) {
			$has_bookings = true;
			break;
		}
	}
	
	// If the order has no bookings, change nothing
	if( ! $has_bookings ) { return $args; }
	
	// WOOCOMMERCE 3.0.0 BW compability
	if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		$order_id = $args[ 'order' ]->get_id();
	} else {
		$order_id = $args[ 'order' ]->id;
	}
	
	// If the order has bookings, refresh the order instance to make sure data are up to date
	$fresh_order_instance = wc_get_order( $order_id );
	
	$args[ 'order' ] = $fresh_order_instance;
	$args[ 'items' ] = $fresh_order_instance->get_items();
	
	return $args;
}
add_filter( 'woocommerce_email_order_items_args', 'bookacti_wc_email_order_item_args', 10, 1 );


/**
 * Add WC notifications tags descriptions
 * @since 1.6.0
 * @version 1.6.1
 * @param array $tags
 * @param int $notification_id
 * @return array
 */
function bookacti_wc_notifications_tags( $tags, $notification_id ) {
	$tags[ '{price}' ] = esc_html__( 'Booking price, with currency.', BOOKACTI_PLUGIN_NAME );
	
	if( strpos( $notification_id, 'refund' ) !== false ) {
		$tags[ '{refund_coupon_code}' ] = esc_html__( 'The WooCommerce coupon code generated when the booking was refunded.', BOOKACTI_PLUGIN_NAME );
	}
	
	return $tags;
}
add_filter( 'bookacti_notifications_tags', 'bookacti_wc_notifications_tags', 15, 2 );


/**
 * Set WC notifications tags values
 * @since 1.6.0
 * @param array $tags
 * @param object $booking
 * @param string $booking_type
 * @param int $notification_id
 * @param string $locale
 * @return array
 */
function bookacti_wc_notifications_tags_values( $tags, $booking, $booking_type, $notification_id, $locale ) {
	
	$item = $booking_type === 'group' ? bookacti_get_order_item_by_booking_group_id( $booking ) : bookacti_get_order_item_by_booking_id( $booking );
	if( ! $item ) { return $tags; }
	
	// WooCommerce Backward compatibility
	if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		$item_id = $item->get_id();
		$price = $item->get_total();
	} else {
		$item_id = $item[ 'id' ];
		$price = $item[ 'line_total' ];
	}
	
	$currency = get_post_meta( $booking->order_id, '_order_currency', true );
	$tags[ '{price}' ]	= $currency ? wc_price( $price, array( 'currency' => $currency ) ) : $price;
	
	if( strpos( $notification_id, 'refund' ) !== false ) {
		$tags[ '{refund_coupon_code}' ]	= wc_get_order_item_meta( $item_id, 'bookacti_refund_coupon', true );
	}
	
	return $tags;
}
add_filter( 'bookacti_notifications_tags_values', 'bookacti_wc_notifications_tags_values', 15, 5 );