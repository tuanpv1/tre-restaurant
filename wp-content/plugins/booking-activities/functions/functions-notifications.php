<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }
	
/**
 * Array of configurable notifications
 * 
 * @since 1.2.1 (was bookacti_get_emails_default_settings in 1.2.0)
 * @return array
 */
function bookacti_get_notifications_default_settings() {
	$notifications = array( 
		'admin_new_booking' => 
			array(
				'id'		=> 'admin_new_booking',
				'active'	=> 1,
				'title'		=> __( 'Customer has made a booking', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the administrator when a new booking is registered.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'to'		=> array( get_bloginfo( 'admin_email' ) ),
					'subject'	=> __( 'New booking!', BOOKACTI_PLUGIN_NAME ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email an administrator receive when a booking is made */
					'message'	=> __( '<p>You have {booking_total_qty} new booking(s) from {user_firstname} {user_lastname} ({user_email})!</p>
										<p>{booking_list}</p>
										<p>Booking status: <strong>{booking_status}</strong>.</p>
										<p><a href="{booking_admin_url}">Click here</a> to edit this booking (ID: {booking_id}).</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		'admin_cancelled_booking' => 
			array(
				'id'		=> 'admin_cancelled_booking',
				'active'	=> 1,
				'title'		=> __( 'Customer has cancelled a booking', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the administrator when a customer cancel a booking.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'to'		=> array( get_bloginfo( 'admin_email' ) ),
					'subject'	=> __( 'Booking cancelled', BOOKACTI_PLUGIN_NAME ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email an administrator receive when a booking is cancelled */
					'message'	=> __( '<p>A customer has cancelled a booking.</p>
										<p>{booking_list}</p>
										<p>Contact him: {user_firstname} {user_lastname} ({user_email})</p>
										<p><a href="{booking_admin_url}">Click here</a> to edit this booking (ID: {booking_id}).</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		'admin_rescheduled_booking' => 
			array(
				'id'		=> 'admin_rescheduled_booking',
				'active'	=> 1,
				'title'		=> __( 'Customer has rescheduled a booking', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the administrator when a customer reschedule a booking.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'to'		=> array( get_bloginfo( 'admin_email' ) ),
					'subject'		=> __( 'Booking rescheduled', BOOKACTI_PLUGIN_NAME ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email an administrator receive when a booking is rescheduled */
					'message'	=> __( '<p>A customer has rescheduled a booking.</p>
										<p>Old booking: {booking_old_start} - {booking_old_end}</p>
										<p>New booking: {booking_list}</p>
										<p>Contact him: {user_firstname} {user_lastname} ({user_email})</p>
										<p><a href="{booking_admin_url}">Click here</a> to edit this booking (ID: {booking_id}).</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		
		'customer_pending_booking' => 
			array(
				'id'		=> 'customer_pending_booking',
				'active'	=> 1,
				'title'		=> __( 'Booking status turns to "Pending"', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the customer when one of his bookings becomes "Pending". If you set the "Default booking state" option to "Pending", this notification will be sent right after the booking is made.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'subject'	=> __( 'Your booking is pending', BOOKACTI_PLUGIN_NAME ) . ' - ' . apply_filters( 'bookacti_translate_text', get_bloginfo( 'name' ) ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email a customer receive when a booking is made, but is still Pending */
					'message'	=> __( '<p>Thank you for your booking request {user_firstname}!</p>
										<p>{booking_list}</p>
										<p>Your reservation is <strong>pending</strong>.</p>
										<p>We will process your request and contact you as soon as possible.</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		'customer_booked_booking' => 
			array(
				'id'			=> 'customer_booked_booking',
				'active'		=> 1,
				'title'			=> __( 'Booking status turns to "Booked"', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the customer when one of his bookings becomes "Booked". If you set the "Default booking state" option to "Booked", this notification will be sent right after the booking is made.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'subject'	=> __( 'Your booking is complete! Thank you', BOOKACTI_PLUGIN_NAME ) . ' - ' . apply_filters( 'bookacti_translate_text', get_bloginfo( 'name' ) ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email a customer receive when a booking is made and Complete */
					'message'	=> __( '<p>Thank you for your booking {user_firstname}!</p>
										<p>{booking_list}</p>
										<p>We confirm that your reservation is now <strong>complete</strong>.</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		'customer_cancelled_booking' => 
			array(
				'id'			=> 'customer_cancelled_booking',
				'active'		=> 1,
				'title'			=> __( 'Booking status turns to "Cancelled"', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the customer when one of his bookings becomes "Cancelled".', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'subject'	=> __( 'Your booking has been cancelled', BOOKACTI_PLUGIN_NAME ) . ' - ' . apply_filters( 'bookacti_translate_text', get_bloginfo( 'name' ) ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email a customer receive when a booking is cancelled */
					'message'	=> __( "<p>Hello {user_firstname},
										<p>Your booking has been <strong>cancelled</strong>.</p>
										<p>{booking_list}</p>
										<p>If you didn't cancelled this reservation or if you think this is an error, please contact us.</p>", BOOKACTI_PLUGIN_NAME ) )
			),
		'customer_refunded_booking' => 
			array(
				'id'			=> 'customer_refunded_booking',
				'active'		=> 1,
				'title'			=> __( 'Booking status turns to "Refunded"', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the customer when one of his bookings becomes "Refunded".', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'subject'	=> __( 'Your booking has been refunded', BOOKACTI_PLUGIN_NAME ) . ' - ' . apply_filters( 'bookacti_translate_text', get_bloginfo( 'name' ) ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email a customer receive when he is reimbursed for a booking */
					'message'	=> __( '<p>Hello {user_firstname},
										<p>Your booking has been <strong>refunded</strong>.</p>
										<p>{booking_list}</p>
										<p>We are sorry for the inconvenience and hope to see you soon.</p>', BOOKACTI_PLUGIN_NAME ) )
			),
		'customer_rescheduled_booking' => 
			array(
				'id'			=> 'customer_rescheduled_booking',
				'active'		=> 1,
				'title'			=> __( 'Booking is rescheduled', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'This notification is sent to the customer when one of his bookings is rescheduled.', BOOKACTI_PLUGIN_NAME ),
				'email'			=> array(
					'active'	=> 1,
					'subject'	=> __( 'Your booking has been rescheduled', BOOKACTI_PLUGIN_NAME ) . ' - ' . apply_filters( 'bookacti_translate_text', get_bloginfo( 'name' ) ),
					/* translators: Keep tags as is (this is a tag: {tag}), they will be replaced in code. This is the default email a customer receive when a booking is rescheduled */
					'message'	=> __( "<p>Hello {user_firstname},
										<p>Your booking has been <strong>rescheduled</strong> from {booking_old_start} to:</p>
										<p>{booking_list}</p>
										<p>If you didn't rescheduled this reservation or if you think this is an error, please contact us.</p>", BOOKACTI_PLUGIN_NAME ) )
			),
	);

	return apply_filters( 'bookacti_notifications_default_settings', $notifications );
}


/**
 * Get notification default settings
 * 
 * @since 1.2.1 (was bookacti_get_email_default_settings in 1.2.0)
 * @param string $notification_id
 * @return false|array
 */
function bookacti_get_notification_default_settings( $notification_id ) {

	if( ! $notification_id ) { return false; }

	$notifications = bookacti_get_notifications_default_settings();

	if( ! isset( $notifications[ $notification_id ] ) ) { return false; }

	return $notifications[ $notification_id ];
}


/**
 * Get notification settings
 * 
 * @since 1.2.1 (was bookacti_get_email_settings in 1.2.0)
 * @param string $notification_id
 * @param boolean $raw
 * @return false|array
 */
function bookacti_get_notification_settings( $notification_id, $raw = true ) {

	if( ! $notification_id ) { return false; }

	$notifications = bookacti_get_notifications_default_settings();

	if( ! isset( $notifications[ $notification_id ] ) ) { return false; }
	
	$notification_settings = array();
	
	// Get raw value from database
	if( $raw ) {
		$alloptions = wp_load_alloptions(); // get_option() calls wp_load_alloptions() itself, so there is no overhead at runtime 
		if( isset( $alloptions[ 'bookacti_notifications_settings_' . $notification_id ] ) ) {
			$notification_settings	= maybe_unserialize( $alloptions[ 'bookacti_notifications_settings_' . $notification_id ] );
		}
	} 
	
	// Else, get notification settings through a normal get_option
	else {
		$notification_settings = get_option( 'bookacti_notifications_settings_' . $notification_id );
	}
	
	// Make sure all values are set
	foreach( $notifications[ $notification_id ] as $key => $value ) {
		if( ! isset( $notification_settings[ $key ] ) ) {
			$notification_settings[ $key ] = $value;
		}
	}

	return apply_filters( 'bookacti_notification_settings', $notification_settings, $notification_id, $raw );
}


/**
 * Sanitize notification settings
 * 
 * @since 1.2.1 (was bookacti_sanitize_email_settings in 1.2.0)
 * @param array $args
 * @param string $notification_id Optionnal notification id. If set, default value will be picked from the corresponding notification.
 * @return array
 */
function bookacti_sanitize_notification_settings( $args, $notification_id = '' ) {
	if( ! $args ) { return false; }

	$defaults = bookacti_get_notification_default_settings( $notification_id );
	if( ! $defaults ) {
		$defaults = array(
			'id'		=> $notification_id,
			'active'	=> 0,
			'title'		=> '',
			'email'		=> array(
				'active'	=> 1,
				'to'		=> array(),
				'subject'	=> '',
				'message'	=> '' )
		);
	}

	$notification = array();
	foreach( $defaults as $key => $default_value ) {
		
		// Do not save constant data
		if( $key === 'id' || $key === 'title' ) { continue; }
		
		$notification[ $key ] = isset( $args[ $key ] ) ? $args[ $key ] : $default_value;

		if( $key === 'active' ) {

			$notification[ $key ] = intval( $notification[ $key ] ) ? 1 : 0;

		} else if( $key === 'email' ) {
			foreach( $default_value as $email_key => $email_value ) {
				
				$notification[ 'email' ][ $email_key ] = isset( $args[ 'email' ][ $email_key ] ) ? $args[ 'email' ][ $email_key ] : $email_value;
				
				if( $email_key === 'active' ) {
					
					$notification[ 'email' ][ $email_key ] = intval( $notification[ 'email' ][ $email_key ] ) ? 1 : 0;
					
				} else if( $email_key === 'to' ) {

					if( ! is_array( $notification[ 'email' ][ $email_key ] ) ) {
						$notification[ 'email' ][ $email_key ] = strval( $notification[ 'email' ][ $email_key ] );
						$notification[ 'email' ][ $email_key ] = strpos( $notification[ 'email' ][ $email_key ], ',' ) !== false ? explode( ',', $notification[ 'email' ][ $email_key ] ) : array( $notification[ 'email' ][ $email_key ] );
					}
					foreach( $notification[ 'email' ][ $email_key ] as $to_key => $to_email_address ) {
						$sanitized_email = sanitize_email( $to_email_address );
						if( $sanitized_email ) {
							$notification[ 'email' ][ $email_key ][ $to_key ] = $sanitized_email;
						} else {
							unset( $notification[ 'email' ][ $email_key ][ $to_key ] );
						}
					}

				} else if( $email_key === 'title' || $email_key === 'subject' ) {

					$sanitized_field = sanitize_text_field( stripslashes( $notification[ 'email' ][ $email_key ] ) );
					$notification[ 'email' ][ $email_key ] = $sanitized_field ? $sanitized_field : $default_value;

				} else if( $email_key === 'message' ) {

					$sanitized_textarea = wp_kses_post( stripslashes( $notification[ 'email' ][ $email_key ] ) );
					$notification[ 'email' ][ $email_key ] = $sanitized_textarea ? $sanitized_textarea : $default_value;
				}
			}
		}
	}
	
	return apply_filters( 'bookacti_notification_sanitized_settings', $notification, $notification_id );
}


/**
 * Get notifications tags
 * @since 1.2.0
 * @version 1.6.0
 * @param string $notification_id Optional.
 * @return array
 */
function bookacti_get_notifications_tags( $notification_id = '' ) {
	
	$tags = array( 
		'{booking_id}'			=> esc_html__( 'Booking unique ID (integer). Bookings and booking groups have different set of IDs.', BOOKACTI_PLUGIN_NAME ),
		'{booking_title}'		=> esc_html__( 'The event / group of events title.', BOOKACTI_PLUGIN_NAME ),
		'{booking_quantity}'	=> esc_html__( 'Booking quantity. If bookings of a same group happen to have different quantities, the higher is displayed.', BOOKACTI_PLUGIN_NAME ),
		'{booking_total_qty}'	=> esc_html__( 'For booking groups, this is the bookings sum. For single bookings, this is the same as {booking_quantity}.', BOOKACTI_PLUGIN_NAME ),
		'{booking_status}'		=> esc_html__( 'Current booking status.', BOOKACTI_PLUGIN_NAME ),
		'{booking_start}'		=> esc_html__( 'Booking start date and time displayed in a user-friendly format. For booking groups, the first event start date and time is used.', BOOKACTI_PLUGIN_NAME ),
		'{booking_end}'			=> esc_html__( 'Booking end date and time displayed in a user-friendly format. For booking groups, the last event end date and time is used.', BOOKACTI_PLUGIN_NAME ),
		'{booking_list}'		=> esc_html__( 'Booking summary displayed as a booking list. You should use this tag once in every notification to know what booking (group) it is about.', BOOKACTI_PLUGIN_NAME ),
		'{user_firstname}'		=> esc_html__( 'The user first name', BOOKACTI_PLUGIN_NAME ),
		'{user_lastname}'		=> esc_html__( 'The user last name', BOOKACTI_PLUGIN_NAME ),
		'{user_email}'			=> esc_html__( 'The user email address', BOOKACTI_PLUGIN_NAME ),
		'{user_id}'				=> esc_html__( 'The user ID. If the user has booked without account, this will display his email address.', BOOKACTI_PLUGIN_NAME ),
		'{user_ical_url}'		=> esc_html__( 'URL to export the user list of bookings in ical format. If the user doesn\'t have an account, only the current booking is exported.', BOOKACTI_PLUGIN_NAME ),
		'{user_ical_key}'		=> esc_html__( 'User ical export secret key. Useful to create a custom ical export URL.', BOOKACTI_PLUGIN_NAME )
	);
	
	if( substr( $notification_id, 0, 6 ) === 'admin_' ) {
		$tags[ '{booking_admin_url}' ]	= esc_html__( 'URL to the booking admin panel. Use this tag only on notifications sent to administrators.', BOOKACTI_PLUGIN_NAME );
	}
	
	if( $notification_id === 'admin_rescheduled_booking' || $notification_id === 'customer_rescheduled_booking' ) {
		$tags[ '{booking_old_start}' ]	= esc_html__( 'Booking start date and time before reschedule. Displayed in a user-friendly format.', BOOKACTI_PLUGIN_NAME );
		$tags[ '{booking_old_end}' ]	= esc_html__( 'Booking end date and time before reschedule. Displayed in a user-friendly format.', BOOKACTI_PLUGIN_NAME );
	}
	
	return apply_filters( 'bookacti_notifications_tags', $tags, $notification_id );
}


/**
 * Get notifications tags and values corresponding to given booking
 * @since 1.2.0
 * @version 1.6.0
 * @param int $booking_id
 * @param string $booking_type 'group' or 'single'
 * @param string $notification_id
 * @param string $locale Optional
 * @return array
 */
function bookacti_get_notifications_tags_values( $booking_id, $booking_type, $notification_id, $locale = 'site' ) {
	
	// Set default locale to site's locale
	if( $locale === 'site' ) { $locale = bookacti_get_site_locale(); }
	
	$booking_data = array();
	
	$filters = $booking_type === 'group' ? array( 'in__booking_group_id' => array( $booking_id ) ) : array( 'in__booking_id' => array( $booking_id ) );
	$filters = bookacti_format_booking_filters( array_merge( array( 'templates' => '', 'fetch_meta' => true ), $filters ) );
	$booking_array = $booking_type === 'group' ? bookacti_get_booking_groups( $filters ) : bookacti_get_bookings( $filters );
	$booking = ! empty( $booking_array[ $booking_id ] ) ? $booking_array[ $booking_id ] : null;
	
	if( $booking ) {
		$datetime_format = apply_filters( 'bookacti_translate_text', bookacti_get_message( 'date_format_long', true ), $locale );
		
		if( $booking_type === 'group' ) {
			$bookings			= bookacti_get_bookings_by_booking_group_id( $booking_id );
			$group_of_events	= bookacti_get_group_of_events( $booking->event_group_id );

			$booking_data[ '{booking_total_qty}' ]	= 0;
			foreach( $bookings as $grouped_booking ) { $booking_data[ '{booking_total_qty}' ] += intval( $grouped_booking->quantity ); }
			$booking_data[ '{booking_title}' ]		= $group_of_events ? $group_of_events->title : '';
			$booking_data[ '{booking_start}' ]		= bookacti_format_datetime( $booking->start, $datetime_format );
			$booking_data[ '{booking_end}' ]		= bookacti_format_datetime( $booking->end, $datetime_format );
			$booking_data[ '{booking_admin_url}' ]	= esc_url( admin_url( 'admin.php?page=bookacti_bookings' ) . '&event_group_id=' . $group_of_events->id );

		} else {
			$bookings	= array( $booking );
			$event		= bookacti_get_event_by_id( $booking->event_id );
			
			$booking_data[ '{booking_total_qty}' ]	= $booking->quantity;
			$booking_data[ '{booking_title}' ]		= $event ? $event->title : '';
			$booking_data[ '{booking_start}' ]		= bookacti_format_datetime( $booking->event_start, $datetime_format );
			$booking_data[ '{booking_end}' ]		= bookacti_format_datetime( $booking->event_end, $datetime_format );
			$booking_data[ '{booking_admin_url}' ]	= esc_url( admin_url( 'admin.php?page=bookacti_bookings' ) . '&event_id=' . $booking->event_id . '&event_start=' . $booking->event_start . '&event_end=' . $booking->event_end );
		}

		$booking_data[ '{booking_id}' ]			= $booking_id;
		$booking_data[ '{booking_title}' ]		= $booking_data[ '{booking_title}' ] ? apply_filters( 'bookacti_translate_text', $booking_data[ '{booking_title}' ], $locale ) : '';
		$booking_data[ '{booking_status}' ]		= bookacti_format_booking_state( $booking->state );
		$booking_data[ '{booking_quantity}' ]	= $booking->quantity;
		$booking_data[ '{booking_list}' ]		= bookacti_get_formatted_booking_events_list( $bookings, 'show', $locale );

		if( $booking->user_id ) { 
			$booking_data[ '{user_id}' ] = $booking->user_id;
			$user = is_numeric( $booking->user_id ) ? get_user_by( 'id', $booking->user_id ) : null;
			if( $user ) { 
				$booking_data[ '{user_firstname}' ]	= ! empty( $user->first_name ) ? $user->first_name : '';
				$booking_data[ '{user_lastname}' ]	= ! empty( $user->last_name ) ? $user->last_name : ''; 
				$booking_data[ '{user_email}' ]		= ! empty( $user->user_email ) ? $user->user_email : '';
				$booking_data[ '{user_phone}' ]		= ! empty( $user->phone ) ? $user->phone : '';
				
				$user_meta = get_user_meta( $booking->user_id );
				if( ! empty( $user_meta[ 'bookacti_secret_key' ][ 0 ] ) ) {
					$booking_data[ '{user_ical_key}' ] = $user_meta[ 'bookacti_secret_key' ][ 0 ];
				} else {
					$booking_data[ '{user_ical_key}' ] = md5( microtime().rand() );
					update_user_meta( $booking->user_id, 'bookacti_secret_key', $booking_data[ '{user_ical_key}' ] );
				}
				$booking_data[ '{user_ical_url}' ] = esc_url( home_url( 'my-bookings.ics?action=bookacti_export_user_booked_events&key=' . $booking_data[ '{user_ical_key}' ] . '&lang=' . $locale ) );
			} else {
				$booking_data[ '{user_firstname}' ]	= ! empty( $booking->user_first_name ) ? $booking->user_first_name : '';
				$booking_data[ '{user_lastname}' ]	= ! empty( $booking->user_last_name ) ? $booking->user_last_name : '';
				$booking_data[ '{user_email}' ]		= ! empty( $booking->user_email ) ? $booking->user_email : '';
				$booking_data[ '{user_phone}' ]		= ! empty( $booking->user_phone ) ? $booking->user_phone : '';
			}
		}
		if( empty( $booking_data[ '{user_ical_key}' ] ) ) {
			$booking_id_param_name = $booking_type === 'group' ? 'booking_group_id' : 'booking_id';
			$booking_data[ '{user_ical_url}' ] = esc_url( home_url( 'my-bookings.ics?action=bookacti_export_booked_events&' . $booking_id_param_name . '=' . $booking_id . '&lang=' . $locale ) );
		}
	}
	
	$default_tags = array_keys( bookacti_get_notifications_tags( $notification_id ) );
	
	// Make sure the array contains all tags 
	$tags = array();
	foreach( $default_tags as $default_tag ) {
		$tags[ $default_tag ] = isset( $booking_data[ $default_tag ] ) ? $booking_data[ $default_tag ] : '';
	}
	
	return apply_filters( 'bookacti_notifications_tags_values', $tags, $booking, $booking_type, $notification_id, $locale );
}


/**
 * Send a notification according to its settings
 * 
 * @since 1.2.1 (was bookacti_send_email in 1.2.0)
 * @version 1.6.0
 * @param string $notification_id Must exists in "bookacti_notifications_default_settings"
 * @param int $booking_id
 * @param string $booking_type "single" or "group"
 * @param array $args Replace or add notification settings and tags
 * @param boolean $async Whether to send the notification asynchronously. 
 * @return array
 */
function bookacti_send_notification( $notification_id, $booking_id, $booking_type, $args = array(), $async = true ) {
	
	// Send notifications asynchronously
	$allow_async = apply_filters( 'bookacti_allow_async_notifications', bookacti_get_setting_value( 'bookacti_notifications_settings', 'notifications_async' ) );
	if( $allow_async && $async ) {
		wp_schedule_single_event( time(), 'bookacti_send_async_notification', array( $notification_id, $booking_id, $booking_type, $args, false ) );
		return;
	}
	
	// Get notification settings
	$notification = bookacti_get_notification_settings( $notification_id );
	
	// Replace or add notification settings
	if( ! empty( $args ) && ! empty( $args[ 'notification' ] ) ) {
		$notification = array_merge( $notification, $args[ 'notification' ] );
	}
	
	if( ! $notification || ! $notification[ 'active' ] ) { return false; }
	
	// Change params according to recipients
	$locale = '';
	if( substr( $notification_id, 0, 8 ) === 'customer' ) {
		
		$user_id = $booking_type === 'group' ? bookacti_get_booking_group_owner( $booking_id ) : bookacti_get_booking_owner( $booking_id );
		$user = is_numeric( $user_id ) ? get_user_by( 'id', $user_id ) : null;
		
		// If the user has an account
		if( $user ) {
			$user_data = get_user_by( 'id', $user_id );
			if( $user_data ) {
				$user_email = $user->user_email;

				// Use the user locale to translate the email
				$locale = bookacti_get_user_locale( $user );
			}
		} else if( is_email( $user_id ) ) {
			$user_email = $user_id;
		} else {
			$object_type = $booking_type === 'group' ? 'booking_group' : 'booking';
			$user_email = bookacti_get_metadata( $object_type, $booking_id, 'user_email', true );
			if( ! is_email( $user_email ) ) { $user_email = ''; }
		}
		
		// Fill the recipients fields
		$notification[ 'email' ][ 'to' ] = array( $user_email );
	}
	
	if( ! $locale ) { $locale = bookacti_get_site_locale();	}
	
	$locale = apply_filters( 'bookacti_notification_locale', $locale, $notification_id, $booking_id, $booking_type, $args );
	
	// Temporarilly switch locale to site or user default's
	bookacti_switch_locale( $locale );
	
	// Replace tags in message and replace linebreaks with html tags
	$tags = bookacti_get_notifications_tags_values( $booking_id, $booking_type, $notification_id, $locale );
	
	// Replace or add tags values
	if( ! empty( $args ) && ! empty( $args[ 'tags' ] ) ) {
		$tags = array_merge( $tags, $args[ 'tags' ] );
	}
	
	$notification	= apply_filters( 'bookacti_notification_data', $notification, $tags, $locale, $booking_id, $booking_type, $args );
	$tags			= apply_filters( 'bookacti_notification_tags', $tags, $notification, $locale, $booking_id, $booking_type, $args );
	$allow_sending	= apply_filters( 'bookacti_notification_sending_allowed', true, $notification, $tags, $locale, $booking_id, $booking_type, $args );
	
	if( ! $allow_sending ) { bookacti_restore_locale(); return array(); } 
	
	// Send email notification
	$sent = array( 'email' => 0 );
	$sent_email = bookacti_send_email_notification( $notification, $tags, $locale );
	
	if( $sent_email ) {
		$sent[ 'email' ] = count( $notification[ 'email' ][ 'to' ] );
	}
	
	$sent = apply_filters( 'bookacti_send_notifications', $sent, $notification_id, $notification, $tags, $booking_id, $booking_type, $args, $locale );
	
	// Switch locale back to normal
	bookacti_restore_locale();
	
	return $sent;
}

// Hook the asynchronous call and send the notification
add_action( 'bookacti_send_async_notification', 'bookacti_send_notification', 10, 5 );


/**
 * Send an email notification
 * 
 * @since 1.2.0
 * @version 1.2.1
 * @param array $notification
 * @param array $tags
 * @param string $locale
 * @return boolean
 */
function bookacti_send_email_notification( $notification, $tags = array(), $locale = 'site' ) {
	
	// Do not send email notification if it is deactivated or if there are no recipients
	if( ! $notification[ 'active' ] || ! $notification[ 'email' ][ 'active' ] || ! $notification[ 'email' ][ 'to' ] ) { return false; }
	
	// Set default locale to site's locale
	if( $locale === 'site' ) {
		$locale = bookacti_get_site_locale();
	}
	
	$to			= $notification[ 'email' ][ 'to' ];
	$subject	= apply_filters( 'bookacti_translate_text', $notification[ 'email' ][ 'subject' ], $locale );
	$message	= wpautop( str_replace( array_keys( $tags ), array_values( $tags ), apply_filters( 'bookacti_translate_text', $notification[ 'email' ][ 'message' ], $locale ) ) );
	
	$from_name	= bookacti_get_setting_value( 'bookacti_notifications_settings', 'notifications_from_name' );
	$from_email	= bookacti_get_setting_value( 'bookacti_notifications_settings', 'notifications_from_email' );
	$headers	= array( 'Content-Type: text/html; charset=UTF-8;', 'From:' . $from_name . ' <' . $from_email . '>' );
	
	$email_data = apply_filters( 'bookacti_email_notification_data', array(
		'headers'	=> $headers,
		'to'		=> $to,
		'subject'	=> $subject,
		'message'	=> $message
	), $notification, $tags, $locale );
	
	$sent = wp_mail( $email_data[ 'to' ], $email_data[ 'subject' ], $email_data[ 'message' ], $email_data[ 'headers' ] );
	
	do_action( 'bookacti_email_notification_sent', $sent, $email_data, $notification, $tags, $locale );
	
	return $sent;
}


// Allow this function to be replaced
if( ! function_exists( 'bookacti_send_new_user_notification' ) ) {

/**
 * Email login credentials to a newly-registered user in an asynchronous way
 * @since 1.5.0
 * @global string  $wp_version
 * @param  int     $user_id   User ID.
 * @param  string  $notify    Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                            string (admin only), 'user', or 'both' (admin and user). Default 'both'.
 * @param  boolean $async     Whether to send the notification asynchronously. 
 */
function bookacti_send_new_user_notification( $user_id, $notify = 'both', $async = true ) {
	// Send notifications asynchronously
	$allow_async = apply_filters( 'bookacti_allow_async_notifications', bookacti_get_setting_value( 'bookacti_notifications_settings', 'notifications_async' ) );
	if( $allow_async && $async ) {
		wp_schedule_single_event( time(), 'bookacti_send_async_new_user_notification', array( $user_id, $notify, false ) );
		return;
	}	
	
	// Send new user email in a backward compatible way
	global $wp_version;
	if( $notify === 'user' && version_compare( $wp_version, '4.6.0', '<' ) ) { $notify = 'both'; }
	
	if( version_compare( $wp_version, '4.3.1', '>=' ) ) {
		wp_new_user_notification( $user_id, null, $notify );
	} else if( version_compare( $wp_version, '4.3.0', '==' ) ) {
		wp_new_user_notification( $user_id, $notify );
	} else {
		$user = get_user_by( 'id', $user_id );
		wp_new_user_notification( $user_id, $user->user_pass );
	}
}

// Hook the asynchronous call and send the new user notification
add_action( 'bookacti_send_async_new_user_notification', 'bookacti_send_new_user_notification', 10, 3 );

}