<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// BOOKINGS

	/**
	 * Check if a booking is whithin the athorized delay as of now
	 * 
	 * @since 1.1.0
	 * @version 1.4.0
	 * 
	 * @param object|int $booking
	 * @return boolean
	 */
	function bookacti_is_booking_in_delay( $booking ) {

		if( is_numeric( $booking ) ) {
			$booking = bookacti_get_booking_by_id( $booking );
		}

		if( ! is_object( $booking ) ) { return false; }

		$is_in_delay	= false;
		$delay_global	= bookacti_get_setting_value( 'bookacti_cancellation_settings', 'cancellation_min_delay_before_event' );
		$timezone		= bookacti_get_setting_value( 'bookacti_general_settings', 'timezone' );
		
		// Get the more specific per activity / group category delay
		$delay_specific = false;
		if( $booking->group_id ) {
			$booking_group	= bookacti_get_booking_group_by_id( $booking->group_id );
			$event_group	= bookacti_get_group_of_events( $booking_group->event_group_id );
			$category_data	= bookacti_get_metadata( 'group_category', $event_group->category_id );
			$delay_specific	= isset( $category_data[ 'booking_changes_deadline' ] ) ? intval( $category_data[ 'booking_changes_deadline' ] ) : false;
		} else {
			$event			= bookacti_get_event_by_id( $booking->event_id );
			$activity_data	= bookacti_get_metadata( 'activity', $event->activity_id );
			$delay_specific	= isset( $activity_data[ 'booking_changes_deadline' ] ) ? intval( $activity_data[ 'booking_changes_deadline' ] ) : false;
		}
		
		// Sanitize
		if( ! is_numeric( $delay_specific ) || $delay_specific < 0 ){ $delay_specific = false; } 
		if( ! is_numeric( $delay_global ) || $delay_global < 0 )	{ $delay_global = 0; } 
		
		// Choose the most specific defined value
		$delay = $delay_specific !== false ? $delay_specific : $delay_global;
		
		$event_datetime		= DateTime::createFromFormat( 'Y-m-d H:i:s', $booking->event_start );
		$delay_datetime		= $event_datetime->sub( new DateInterval( 'P' . $delay . 'D' ) );
		$current_datetime	= new DateTime( 'now', new DateTimeZone( $timezone ) );

		if( $current_datetime < $delay_datetime ) { $is_in_delay = true; }

		return apply_filters( 'bookacti_is_booking_in_delay', $is_in_delay, $booking );
	}




// BOOKINGS PAGE

	/**
	 * Return the HTML code to display activities by templates in the bookings page
	 * 
	 * @param array $template_ids
	 * @param array $activity_ids
	 * @return string
	 */
	function bookacti_get_activities_html_for_booking_page( $template_ids, $activity_ids = array() ) {

		$activities = bookacti_get_activities_by_template( $template_ids, false );
		$j = 0;
		$html = '';
		foreach ( $activities as $activity ) {	
			if( ( empty( $activity_ids )  && $j === 0 ) || in_array( $activity->id, $activity_ids ) ) { $selected = 'selected'; } else { $selected = ''; }

			// Retrieve activity title
			$title = apply_filters( 'bookacti_translate_text', $activity->title );

			// Display activity
			$html.=	"<div class='bookacti-bookings-filter-activity bookacti-bookings-filter' "
				.		"data-activity-id='" . esc_attr( $activity->id ) . "' "
				.		"style='background-color: " . esc_attr( $activity->color ) . "; border-color: " . esc_attr( $activity->color ) . "' " 
				.		esc_attr( $selected )
				.	" >"
				.		"<div class='bookacti-bookings-filter-content' >"
				.			"<div class='bookacti-bookings-filter-activity-title' >"
				.				"<strong>" . esc_html( $title ). "</strong>"
				.			"</div>"
				.		"</div>"
				.		"<div class='bookacti-bookings-filter-bg' ></div>"
				.	"</div>";

			$j++;
		}

		return apply_filters( 'bookacti_activities_html_by_templates', $html, $template_ids, $activity_ids );
	}
	
	
	/**
	 * Get Default booking filters
	 * @since 1.6.0
	 * @return array
	 */
	function bookacti_get_default_booking_filters() {
		return apply_filters( 'bookacti_default_booking_filters', array(
			'templates'					=> array(), 
			'activities'				=> array(), 
			'booking_id'				=> 0, 
			'booking_group_id'			=> 0,
			'event_group_id'			=> 0, 
			'event_id'					=> 0, 
			'event_start'				=> '', 
			'event_end'					=> '', 
			'status'					=> array(), 
			'user_id'					=> 0,
			'form_id'					=> 0,
			'from'						=> '',
			'to'						=> '',
			'active'					=> false,
			'group_by'					=> '',
			'order_by'					=> array( 'creation_date', 'id', 'event_start' ), 
			'order'						=> 'desc',
			'offset'					=> 0,
			'per_page'					=> 0,
			'in__booking_id'			=> array(),
			'in__booking_group_id'		=> array(),
			'not_in__booking_id'		=> array(),
			'not_in__booking_group_id'	=> array(),
			'not_in__user_id'			=> array(),
			'fetch_meta'				=> false
		));
	}
	
	
	/**
	 * Format booking filters
	 * @since 1.3.0
	 * @version 1.6.0
	 * @param array $filters 
	 * @return array
	 */
	function bookacti_format_booking_filters( $filters = array() ) {

		$default_filters = bookacti_get_default_booking_filters();
		
		$formatted_filters = array();
		foreach( $default_filters as $filter => $default_value ) {
			// If a filter isn't set, use the default value
			if( ! isset( $filters[ $filter ] ) ) {
				$formatted_filters[ $filter ] = $default_value;
				continue;
			}
			
			$current_value = $filters[ $filter ];
			
			// Else, check if its value is correct, or use default
			if( in_array( $filter, array( 'templates' ) ) ) {
				if( is_numeric( $current_value ) ) { $current_value = array( $current_value ); }
				if( is_array( $current_value ) ) {
					// Check if current user is allowed to manage desired templates, or unset them
					if( ! empty( $current_value ) ) {
						foreach( $current_value as $i => $template_id ) {
						if( ! is_numeric( $template_id ) || ! bookacti_user_can_manage_template( $template_id ) ) {
								unset( $current_value[ $i ] );
							}
						}
					}
					// Re-check if the template list is empty because some template filters may have been removed
					// and get all allowed templates if it is empty
					if( empty( $current_value ) ) {
						$current_value = array_keys( bookacti_fetch_templates() );
					}
				}
				else { $current_value = $default_value; }
				
			} else if( in_array( $filter, array( 'activities', 'in__booking_id', 'in__booking_group_id', 'not_in__booking_id', 'not_in__booking_group_id' ), true ) ) {
				if( is_numeric( $current_value ) )	{ $current_value = array( $current_value ); }
				if( ! is_array( $current_value ) )	{ $current_value = $default_value; }
				else if( ( $i = array_search( 'all', $current_value ) ) !== false ) { unset( $current_value[ $i ] ); }
				
			} else if( in_array( $filter, array( 'not_in__user_id' ), true ) ) {
				if( is_numeric( $current_value ) || is_string( $current_value ) )	{ $current_value = array( $current_value ); }
				if( ! is_array( $current_value ) )	{ $current_value = $default_value; }
				
			} else if( in_array( $filter, array( 'status' ), true ) ) {
				if( is_string( $current_value ) )	{ $current_value = array( $current_value ); }
				if( ! is_array( $current_value ) )	{ $current_value = $default_value; }
				else if( ( $i = array_search( 'all', $current_value ) ) !== false ) { unset( $current_value[ $i ] ); }
				
			} else if( in_array( $filter, array( 'booking_id', 'booking_group_id', 'event_group_id', 'event_id', 'offset', 'per_page' ), true ) ) {
				if( ! is_numeric( $current_value ) ){ $current_value = $default_value; }
			
			} else if( in_array( $filter, array( 'event_start', 'event_end' ), true ) ) {
				if( ! bookacti_sanitize_datetime( $current_value ) ) { $current_value = $default_value; }
			
			} else if( in_array( $filter, array( 'from', 'to' ), true ) ) {
				if( ! bookacti_sanitize_date( $current_value ) ) { $current_value = $default_value; }
			
			} else if( in_array( $filter, array( 'active' ), true ) ) {
					 if( in_array( $current_value, array( true, 'true', 1, '1' ), true ) )	{ $current_value = 1; }
				else if( in_array( $current_value, array( 0, '0' ), true ) ){ $current_value = 0; }
				if( ! in_array( $current_value, array( 0, 1 ), true ) ) { $current_value = $default_value; }
				
			} else if( in_array( $filter, array( 'fetch_meta' ), true ) ) {
				if( in_array( $current_value, array( true, 'true', 1, '1' ), true ) ) { $current_value = true; }
				else { $current_value = false; }
				
			} else if( $filter === 'order_by' ) {
				$sortable_columns = array( 
					'id', 
					'user_id', 
					'event_id', 
					'event_start', 
					'event_end', 
					'state', 
					'quantity', 
					'template_id', 
					'activity_id', 
					'creation_date' 
				);
				if( is_string( $current_value ) )	{ 
					if( ! in_array( $current_value, $sortable_columns, true ) ) { $current_value = $default_value; }
					else { $current_value = array( $current_value ); }
				}
				if( ! is_array( $current_value ) )				{ $current_value = $default_value; }
				if( $current_value[ 0 ] === 'creation_date' )	{ $current_value = array( 'creation_date', 'id', 'event_start' ); }
				else if( $current_value[ 0 ] === 'id' )			{ $current_value = array( 'id', 'event_start' ); }
				
			} else if( $filter === 'order' ) {
				if( ! in_array( $current_value, array( 'asc', 'desc' ), true ) ) { $current_value = $default_value; }
			
			} else if( $filter === 'group_by' ) {
				if( ! in_array( $current_value, array( 'none', 'booking_group' ), true ) ) { $current_value = $default_value; }
				
			} else if( $filter === 'user_id' ) {
				if( ! is_numeric( $filter ) && ! is_string( $filter ) ) { $current_value = $default_value; }
			}
			
			$formatted_filters[ $filter ] = $current_value;
		}
		
		return apply_filters( 'bookacti_formatted_booking_filters', $formatted_filters, $filters, $default_filters );
	}
	
	
	/**
	 * Format booking filters manually input
	 * @since 1.6.0
	 * @param array $filters
	 * @return array
	 */
	function bookacti_format_string_booking_filters( $filters = array() ) {
		// Format arrays
		$formatted_arrays = array();
		$int_arrays = array( 'templates', 'activities', 'in__booking_id', 'in__booking_group_id', 'not_in__booking_id', 'not_in__booking_group_id', 'not_in__user_id' );
		$str_arrays = array( 'status', 'order_by' );
		foreach( array_merge( $int_arrays, $str_arrays ) as $att_name ) {
			if( empty( $filters[ $att_name ] ) || is_array( $filters[ $att_name ] ) ) { continue; }
			$formatted_arrays[ $att_name ] = explode( ',', preg_replace( array(
				'/[^\d,]/',    // Matches anything that's not a comma or number.
				'/(?<=,),+/',  // Matches consecutive commas.
				'/^,+/',       // Matches leading commas.
				'/,+$/'        // Matches trailing commas.
			), '', 	$filters[ $att_name ] ) );
			if( in_array( $att_name, $int_arrays, true ) ) { $formatted_arrays[ $att_name ] = array_map( 'intval', $formatted_arrays[ $att_name ] ); }
			if( in_array( $att_name, $str_arrays, true ) ) { $formatted_arrays[ $att_name ] = array_map( 'sanitize_title_with_dashes', $formatted_arrays[ $att_name ] ); }
		}

		// Format datetime
		$from = ''; $to = '';
		if( ! empty( $filters[ 'from' ] ) || ! empty( $filters[ 'to' ] ) !== '' ) { 
			$timezone = new DateTimeZone( bookacti_get_setting_value( 'bookacti_general_settings', 'timezone' ) );
			if( ! empty( $filters[ 'from' ] ) && (bool)strtotime( $filters[ 'from' ] ) ) {
				$from_datetime = new DateTime( $filters[ 'from' ], $timezone );
				$from = $from_datetime->format( 'Y-m-d' );
			}
			if( ! empty( $filters[ 'to' ] ) && (bool)strtotime( $filters[ 'to' ] ) ) {
				$to_datetime = new DateTime( $filters[ 'to' ], $timezone );
				$to = $to_datetime->format( 'Y-m-d' );
			}
		}
		
		return apply_filters( 'bookacti_format_string_booking_filters', array_merge( $filters, array( 'from' => $from, 'to' => $to ), $formatted_arrays ), $filters );
	}
	


// PERMISSIONS
	
	// SINGLE BOOKINGS

		/**
		 * Check if user is allowed to manage a booking
		 * @version 1.6.0
		 * @param int $booking_id
		 * @param int|string $user_id
		 * @return boolean
		 */
		function bookacti_user_can_manage_booking( $booking_id, $user_id = false ) {

			$user_can_manage_booking = false;
			if( ! $user_id ) { $user_id = get_current_user_id(); }
			
			$owner = bookacti_get_booking_owner( $booking_id );
			if( user_can( $user_id, 'bookacti_edit_bookings' ) 
			||  ( $owner !== null && $owner == $user_id ) ) { 
				$user_can_manage_booking = true; 
			}

			return apply_filters( 'bookacti_user_can_manage_booking', $user_can_manage_booking, $booking_id, $user_id );
		}


		/**
		 * Check if a booking can be cancelled
		 * @version 1.6.0
		 * @param object|int $booking_id
		 * @return boolean
		 */
		function bookacti_booking_can_be_cancelled( $booking, $bypass_group_check = false ) {
			$is_allowed	= true;
			
			if( ! current_user_can( 'bookacti_edit_bookings' ) ) {
				// Get booking
				if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
				
				if( ! $booking ) { return apply_filters( 'bookacti_booking_can_be_cancelled', false, $booking ); }
				
				$is_cancel_allowed	= bookacti_get_setting_value( 'bookacti_cancellation_settings', 'allow_customers_to_cancel' );
				$is_grouped			= $bypass_group_check ? false : ! empty( $booking->group_id );
				$is_in_delay		= apply_filters( 'bookacti_bypass_delay', false, $booking ) ? true : bookacti_is_booking_in_delay( $booking );
				
				// Final check and return the actions array without invalid entries
				if( ! $is_cancel_allowed || ! $booking->active || ! $is_in_delay || $is_grouped ) { $is_allowed = false; }
			}
			
			return apply_filters( 'bookacti_booking_can_be_cancelled', $is_allowed, $booking );
		}


		/**
		 * Check if a booking is allowed to be rescheduled
		 * @version 1.6.0
		 * @param object|int $booking
		 * @return boolean
		 */
		function bookacti_booking_can_be_rescheduled( $booking ) {
			$is_allowed	= true;
			
			// Get booking
			if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
			
			if( ! current_user_can( 'bookacti_edit_bookings' ) ) {
				
				if( ! $booking ) { return apply_filters( 'bookacti_booking_can_be_rescheduled', false, $booking ); }
				
				// First check if the booking is part of a group
				$is_allowed	= empty( $booking->group_id );
				if( $is_allowed ) {
					// Init variable
					$is_reschedule_allowed	= bookacti_get_setting_value( 'bookacti_cancellation_settings', 'allow_customers_to_reschedule' );
					$is_in_delay			= apply_filters( 'bookacti_bypass_delay', false, $booking ) ? true : bookacti_is_booking_in_delay( $booking );

					if( ! $is_reschedule_allowed || ! $booking->active || ! $is_in_delay ) { $is_allowed = false; }
				}
			}
			
			// If the booked event has been removed, we cannot know its activity, then, the booking cannot be rescheduled.
			if( ! bookacti_get_event_by_id( $booking->event_id ) ) { $is_allowed = false; }
			
			return apply_filters( 'bookacti_booking_can_be_rescheduled', $is_allowed, $booking );
		}
		
		
		/**
		 * Check if a booking can be rescheduled to another event
		 * @since 1.1.0
		 * @version 1.6.0
		 * @param object|int $booking
		 * @param int $event_id
		 * @param string $event_start
		 * @param string $event_end
		 * @return boolean
		 */
		function bookacti_booking_can_be_rescheduled_to( $booking, $event_id, $event_start, $event_end ) {
			// Get booking
			if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
			
			$return_array = array( 'status' => 'success' );
			$is_allowed = bookacti_booking_can_be_rescheduled( $booking );
			if( ! $is_allowed ) {
				$return_array[ 'status' ] = 'failed';
				$return_array[ 'error' ] = 'reschedule_not_allowed';
				$return_array[ 'message' ] = esc_html__( 'You are not allowed to reschedule this event.', BOOKACTI_PLUGIN_NAME );
				return apply_filters( 'bookacti_booking_can_be_rescheduled_to', $return_array, $booking, $event_id, $event_start, $event_end );
			}
			
			$from_event	= bookacti_get_event_by_id( $booking->event_id );
			$to_event	= bookacti_get_event_by_id( $event_id );
			
			if( $from_event->activity_id !== $to_event->activity_id ) {
				$return_array[ 'status' ] = 'failed';
				$return_array[ 'error' ] = 'reschedule_to_different_activity';
				$return_array[ 'message' ] = esc_html__( 'The desired event haven\'t the same activity as the booked event.', BOOKACTI_PLUGIN_NAME );
			}
			
			return apply_filters( 'bookacti_booking_can_be_rescheduled_to', $return_array, $booking, $event_id, $event_start, $event_end );
		}


		/**
		 * Check if a booking can be refunded
		 * @version 1.6.0
		 * @param int $booking
		 * @param string $refund_action
		 * @return boolean
		 */
		function bookacti_booking_can_be_refunded( $booking, $refund_action = false ) {
			// Get booking
			if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
			
			if( ! $booking ) { return apply_filters( 'bookacti_booking_can_be_refunded', false, $booking ); }
			
			$refund_actions	= bookacti_get_refund_actions_by_booking_id( $booking );
			$true			= true;
			
			// Disallow refund in those cases:
			// -> If the booking is already marked as refunded, 
			if( $booking->state === 'refunded' 
			// -> If the booking is part of a group
			||  ! empty( $booking->group_id )
			// -> If there are no refund action available
			||  empty( $refund_actions )
			// -> If the refund action is set but doesn't exist in available refund actions list
			|| ( ! empty( $refund_action ) && ! array_key_exists( $refund_action, $refund_actions ) ) 
			// -> If the user is not an admin, the booking state has to be 'cancelled' in the first place
			|| ( ! current_user_can( 'bookacti_edit_bookings' ) && $booking->state !== 'cancelled' ) )	{ 

				$true = false; 

			}
			
			return apply_filters( 'bookacti_booking_can_be_refunded', $true, $booking );
		}


		/**
		 * Check if a booking state can be changed to another
		 * @version 1.6.0
		 * @param object|int $booking
		 * @param string $new_state
		 * @return boolean
		 */
		function bookacti_booking_state_can_be_changed_to( $booking, $new_state ) {
			$true = true;
			if( ! current_user_can( 'bookacti_edit_bookings' ) ) {
				switch ( $new_state ) {
					case 'delivered':
						$true = false;
					case 'cancelled':
						$true = bookacti_booking_can_be_cancelled( $booking );
						break;
					case 'refund_requested':
					case 'refunded':
						$true = bookacti_booking_can_be_refunded( $booking );
						break;
				}
			}
			return apply_filters( 'bookacti_booking_state_can_be_changed', $true, $booking, $new_state );
		}
	
		
	// BOOKING GROUPS

		/**
		 * Check if user is allowed to manage a booking group
		 * 
		 * @since 1.1.0
		 * 
		 * @param int $booking_group_id
		 * @param int|string $user_id
		 * @return boolean
		 */
		function bookacti_user_can_manage_booking_group( $booking_group_id, $user_id = false ) {
			
			$booking_ids = bookacti_get_booking_group_bookings_ids( $booking_group_id );
			
			$user_can_manage_booking_group = true;
			foreach( $booking_ids as $booking_id ) {
				$is_allowed = bookacti_user_can_manage_booking( $booking_id, $user_id );
				if( ! $is_allowed ) {
					$user_can_manage_booking_group = false;
					break; // If one of the booking of the group is not allowed, return false immediatly
				}
			}
			
			return apply_filters( 'bookacti_user_can_manage_booking_group', $user_can_manage_booking_group, $booking_id, $user_id );
		}


		/**
		 * Check if a booking group can be cancelled
		 * @since 1.1.0
		 * @version 1.6.0
		 * @param object $booking_group
		 * @return boolean
		 */
		function bookacti_booking_group_can_be_cancelled( $booking_group ) {
			$true = true;
			
			// Get booking group
			if( ! is_object( $booking_group ) ) { $booking_group = bookacti_get_booking_group_by_id( $booking_group ); }
			
			if( ! current_user_can( 'bookacti_edit_bookings' ) ) {
				$filters = bookacti_format_booking_filters( array( 'booking_group_id' => $booking_group->id ) );
				$bookings = bookacti_get_bookings( $filters );
				foreach( $bookings as $booking ) {
					$is_allowed = bookacti_booking_can_be_cancelled( $booking, true );
					if( ! $is_allowed ) {
						$true = false;
						break; // If one of the booking of the group is not allowed, return false immediatly
					}
				}
			}
			return apply_filters( 'bookacti_booking_group_can_be_cancelled', $true, $booking_group );
		}


		/**
		 * Check if a booking group can be refunded
		 * @since 1.1.0
		 * @version 1.6.0
		 * @param object|int $booking_group
		 * @param string $refund_action
		 * @return boolean
		 */
		function bookacti_booking_group_can_be_refunded( $booking_group, $refund_action = false ) {
			// Get booking group
			if( ! is_object( $booking_group ) ) { $booking_group = bookacti_get_booking_group_by_id( $booking_group ); }
			
			$true			= true;
			$refund_actions	= bookacti_get_refund_actions_by_booking_group_id( $booking_group );
			
			// Disallow refund in those cases:
			// -> If the booking group is already marked as refunded, 
			if( $booking_group->state === 'refunded' 
			// -> If there are no refund action available
			||  empty( $refund_actions )
			// -> If the refund action is set but doesn't exist in available refund actions list
			|| ( ! empty( $refund_action ) && ! array_key_exists( $refund_action, $refund_actions ) ) 
			// -> If the user is not an admin, the booking group state has to be 'cancelled' in the first place
			|| ( ! current_user_can( 'bookacti_edit_bookings' ) && $booking_group->state !== 'cancelled' ) )	{ 

				$true = false; 

			}

			return apply_filters( 'bookacti_booking_group_can_be_refunded', $true, $booking_group );
		}


		/**
		 * Check if a booking group state can be changed to another
		 * @since 1.1.0
		 * @version 1.6.0
		 * @param object $booking_group
		 * @param string $new_state
		 * @return boolean
		 */
		function bookacti_booking_group_state_can_be_changed_to( $booking_group, $new_state ) {
			$true = true;
			$can_edit_bookings = current_user_can( 'bookacti_edit_bookings' );
			switch ( $new_state ) {
				case 'delivered':
					$true = $can_edit_bookings;
				case 'cancelled':
					$true = bookacti_booking_group_can_be_cancelled( $booking_group );
					break;
				case 'refund_requested':
					if( ! $can_edit_bookings ) {
						$true = bookacti_booking_group_can_be_refunded( $booking_group );
					}
					break;
				case 'refunded':
					$true = bookacti_booking_group_can_be_refunded( $booking_group );
					break;
			}
			return apply_filters( 'bookacti_booking_group_state_can_be_changed', $true, $booking_group, $new_state );
		}



	
// BOOKING ACTIONS
	
	// SINGLE BOOKING
		/**
		 * Get booking actions array
		 * @since 1.6.0 (replace bookacti_get_booking_actions_array)
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @return array
		 */
		function bookacti_get_booking_actions( $admin_or_front = 'both' ) {
			$actions = apply_filters( 'bookacti_booking_actions', array(
				'change-state' => array( 
					'class'			=> 'bookacti-change-booking-state',
					'label'			=> esc_html__( 'Change booking state',  BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Change the booking state to any available state.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'admin' ),
				'cancel' => array( 
					'class'			=> 'bookacti-cancel-booking',
					'label'			=> esc_html__( 'Cancel', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Cancel the booking.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'front' ),
				'reschedule' => array( 
					'class'			=> 'bookacti-reschedule-booking',
					'label'			=> esc_html__( 'Reschedule', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Change the booking dates to any other available time slot for this event.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'both' ),
				'refund' => array( 
					'class'			=> 'bookacti-refund-booking',
					'label'			=> esc_html_x( 'Refund', 'Button label to trigger the refund action', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Refund the booking with one of the available refund method.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'both' ),
				'delete' => array( 
					'class'			=> 'bookacti-delete-booking',
					'label'			=> esc_html__( 'Delete',  BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Delete permanently the booking.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'admin' )
			), $admin_or_front );
			
			$possible_actions = array();
			foreach( $actions as $action_id => $action ){
				if( $admin_or_front === 'both' || $action[ 'admin_or_front' ] === 'both' || $admin_or_front === $action[ 'admin_or_front' ] ) {
					$possible_actions[ $action_id ] = $action;
				}
			}
			
			return $possible_actions;
		}
		
		
		/**
		 * Get booking actions according to booking id
		 * @since 1.6.0 (replace bookacti_get_booking_actions_array)
		 * @param object|int $booking
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @return array
		 */
		function bookacti_get_booking_actions_by_booking( $booking, $admin_or_front = 'both' ) {
			// Get booking
			if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
			
			$actions = bookacti_get_booking_actions( $admin_or_front );
			if( isset( $actions[ 'change-state' ] ) && ! current_user_can( 'bookacti_edit_bookings' ) ) {
				unset( $actions[ 'change-state' ] );
			}
			if( isset( $actions[ 'cancel' ] ) && ! bookacti_booking_can_be_cancelled( $booking ) ) {
				unset( $actions[ 'cancel' ] );
			}
			if( isset( $actions[ 'reschedule' ] ) && ! bookacti_booking_can_be_rescheduled( $booking ) ) {
				unset( $actions[ 'reschedule' ] );
			}
			if( isset( $actions[ 'refund' ] ) && ! bookacti_booking_can_be_refunded( $booking ) ) {
				unset( $actions[ 'refund' ] );
			}
			if( isset( $actions[ 'delete' ] ) && ! current_user_can( 'bookacti_delete_bookings' ) ) {
				unset( $actions[ 'delete' ] );
			}
			return apply_filters( 'bookacti_booking_actions_by_booking', $actions, $booking, $admin_or_front );
		}
		

		/**
		 * Get booking actions html
		 * @version 1.6.0
		 * @param object|int $booking
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @param array $actions
		 * @param boolean $return_array
		 * @param boolean $with_container
		 * @return string
		 */
		function bookacti_get_booking_actions_html( $booking, $admin_or_front = 'both', $actions = array(), $return_array = false, $with_container = false ) {
			// Get booking
			if( ! is_object( $booking ) ) { $booking = bookacti_get_booking_by_id( $booking ); }
			
			// Get booking actions
			if( ! $actions ) { $actions = bookacti_get_booking_actions_by_booking( $booking, $admin_or_front ); }
			
			$actions_html_array	= array();
			foreach( $actions as $action_id => $action ){
					$action_html	= '<a '
										. 'href="' . esc_url( $action[ 'link' ] ) . '" '
										. 'id="bookacti-booking-action-' . esc_attr( $action_id ) . '-' . esc_attr( $booking->id ) . '" '
										. 'class="button ' . esc_attr( $action[ 'class' ] ) . ' bookacti-booking-action bookacti-tip" '
										. 'data-tip="' . esc_attr( $action[ 'description' ] ) . '" '
										. 'data-booking-id="' . esc_attr( $booking->id ) . '" >';
					
					if( $admin_or_front === 'front' || $action[ 'admin_or_front' ] === 'front' ) { 
						$action_html .= esc_html( $action[ 'label' ] ); 
					}
					
					$action_html	.= '</a>';
					$actions_html_array[ $action_id ] = $action_html;
			}
			
			// Return the array of html actions
			if( $return_array ) {
				return apply_filters( 'bookacti_booking_actions_html_array', $actions_html_array, $booking, $admin_or_front );
			}
			
			$actions_html = implode( ' | ', $actions_html_array );
			
			// Add a container
			if( $with_container ) {
				$actions_html	= '<div class="bookacti-booking-actions" data-booking-id="' . esc_attr( $booking->id ) . '" >'
								.	$actions_html
								. '</div>';
			}

			return apply_filters( 'bookacti_booking_actions_html', $actions_html, $booking, $admin_or_front );
		}
	
	
	
	// BOOKING GROUPS
		/**
		 * Get booking group actions array
		 * @since 1.6.0 (replace bookacti_get_booking_group_actions_array)
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @return array
		 */
		function bookacti_get_booking_group_actions( $admin_or_front = 'both' ) {
			$actions = apply_filters( 'bookacti_booking_group_actions', array(
				'change-state' => array( 
					'class'			=> 'bookacti-change-booking-group-state',
					'label'			=> esc_html__( 'Change booking state',  BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Change the booking group state to any available state.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'admin' ),
				'edit-single' => array( 
					'class'			=> 'bookacti-show-booking-group-bookings',
					'label'			=> esc_html__( 'Edit bookings',  BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Edit each booking of the group separately.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'admin' ),
				'cancel' => array( 
					'class'			=> 'bookacti-cancel-booking-group',
					'label'			=> esc_html__( 'Cancel', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Cancel the booking group.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'front' ),
				'refund' => array( 
					'class'			=> 'bookacti-refund-booking-group',
					'label'			=> esc_html_x( 'Refund', 'Button label to trigger the refund action', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Refund the booking group with one of the available refund method.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'both' ),
				'delete' => array( 
					'class'			=> 'bookacti-delete-booking-group',
					'label'			=> esc_html__( 'Delete', BOOKACTI_PLUGIN_NAME ),
					'description'	=> esc_html__( 'Delete permanently the booking group.', BOOKACTI_PLUGIN_NAME ),
					'link'			=> '',
					'admin_or_front'=> 'admin' )
			), $admin_or_front );
			
			$possible_actions = array();
			foreach( $actions as $action_id => $action ){
				if( $admin_or_front === 'both' || $action[ 'admin_or_front' ] === 'both' || $admin_or_front === $action[ 'admin_or_front' ] ) {
					$possible_actions[ $action_id ] = $action;
				}
			}
			
			return $possible_actions;
		}
		
		
		/**
		 * Get booking actions according to booking id
		 * @since 1.6.0 (replace bookacti_get_booking_actions_array)
		 * @param object|int $booking_group
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @return array
		 */
		function bookacti_get_booking_group_actions_by_booking_group( $booking_group, $admin_or_front = 'both' ) {
			// Get booking group
			if( ! is_object( $booking_group ) ) { $booking_group = bookacti_get_booking_group_by_id( $booking_group ); }
			
			$actions = bookacti_get_booking_group_actions( $admin_or_front );
			if( ( isset( $actions[ 'change-state' ] ) || isset( $actions[ 'edit-single' ] ) ) && ! current_user_can( 'bookacti_edit_bookings' ) ) {
				if( isset( $actions[ 'change-state' ] ) ) { unset( $actions[ 'change-state' ] ); }
				if( isset( $actions[ 'edit-single' ] ) ) { unset( $actions[ 'edit-single' ] ); }
			}
			if( isset( $actions[ 'cancel' ] ) && ! bookacti_booking_group_can_be_cancelled( $booking_group ) ) {
				unset( $actions[ 'cancel' ] );
			}
			if( isset( $actions[ 'refund' ] ) && ! bookacti_booking_group_can_be_refunded( $booking_group ) ) {
				unset( $actions[ 'refund' ] );
			}
			if( isset( $actions[ 'delete' ] ) && ! current_user_can( 'bookacti_delete_bookings' ) ) {
				unset( $actions[ 'delete' ] );
			}
			return apply_filters( 'bookacti_booking_group_actions_by_booking_group', $actions, $booking_group, $admin_or_front );
		}
		
		
		/**
		 * Get booking group actions html
		 * @version 1.6.0
		 * @param object|int $booking_group
		 * @param string $admin_or_front Can be "both", "admin", "front. Default "both".
		 * @param array $actions
		 * @param boolean $return_array
		 * @param boolean $with_container
		 * @return string
		 */
		function bookacti_get_booking_group_actions_html( $booking_group, $admin_or_front = 'both', $actions = array(), $return_array = false, $with_container = false ) {
			// Get booking group
			if( ! is_object( $booking_group ) ) { $booking_group = bookacti_get_booking_group_by_id( $booking_group ); }
			
			if( ! $actions ) {
				$actions = bookacti_get_booking_group_actions_by_booking_group( $booking_group, $admin_or_front );
			}
			
			$actions_html_array	= array();
			foreach( $actions as $action_id => $action ){
				$action_html	= '<a '
									. 'href="' . esc_url( $action[ 'link' ] ) . '" '
									. 'id="bookacti-booking-group-action-' . esc_attr( $action_id ) . '-' . intval( $booking_group->id ) . '" '
									. 'class="button ' . esc_attr( $action[ 'class' ] ) . ' bookacti-booking-group-action bookacti-tip" '
									. 'data-tip="' . esc_attr( $action[ 'description' ] ) . '" '
									. 'data-booking-group-id="' . intval( $booking_group->id ) . '" >';

				if( $admin_or_front === 'front' || $action[ 'admin_or_front' ] === 'front' ) { 
					$action_html .= esc_html( $action[ 'label' ] ); 
				}

				$action_html	.= '</a>';
				$actions_html_array[] = $action_html;
			}
			
			// Return the array of html actions
			if( $return_array ) {
				return apply_filters( 'bookacti_booking_group_actions_html_array', $actions_html_array, $booking_group, $admin_or_front );
			}
			
			$actions_html = implode( ' | ', $actions_html_array );
			
			// Add a container
			if( $with_container ) {
				$actions_html	= '<div class="bookacti-booking-group-actions" data-booking-group-id="' . esc_attr( $booking_group->id ) . '" >' 
								.	$actions_html
								. '</div>';
			}
			
			return apply_filters( 'bookacti_booking_group_actions_html', $actions_html, $booking_group, $admin_or_front );
		}

	
	// BOTH SINGLE AND GROUPS
		/**
		 * Booking data that can be exported
		 * @since 1.6.0
		 * @return array
		 */
		function bookacti_get_bookings_export_columns() {
			return apply_filters( 'bookacti_bookings_export_columns_labels', array(
				'booking_id'			=> esc_html__( 'Booking ID', BOOKACTI_PLUGIN_NAME ),
				'booking_type'			=> esc_html__( 'Booking type (single or group)', BOOKACTI_PLUGIN_NAME ),
				'status'				=> esc_html__( 'Booking status', BOOKACTI_PLUGIN_NAME ),
				'payment_status'		=> esc_html__( 'Payment status', BOOKACTI_PLUGIN_NAME ),
				'quantity'				=> esc_html__( 'Quantity', BOOKACTI_PLUGIN_NAME ),
				'creation_date'			=> esc_html__( 'Creation date', BOOKACTI_PLUGIN_NAME ),
				'customer_id'			=> esc_html__( 'Customer ID', BOOKACTI_PLUGIN_NAME ),
				'customer_display_name'	=> esc_html__( 'Customer display name', BOOKACTI_PLUGIN_NAME ),
				'customer_first_name'	=> esc_html__( 'Customer first name', BOOKACTI_PLUGIN_NAME ),
				'customer_last_name'	=> esc_html__( 'Customer last name', BOOKACTI_PLUGIN_NAME ),
				'customer_email'		=> esc_html__( 'Customer Email', BOOKACTI_PLUGIN_NAME ),
				'customer_phone'		=> esc_html__( 'Customer Phone', BOOKACTI_PLUGIN_NAME ),
				'event_id'				=> esc_html__( 'Event ID', BOOKACTI_PLUGIN_NAME ),
				'event_title'			=> esc_html__( 'Event title', BOOKACTI_PLUGIN_NAME ),
				'start_date'			=> esc_html__( 'Start date', BOOKACTI_PLUGIN_NAME ),
				'end_date'				=> esc_html__( 'End date', BOOKACTI_PLUGIN_NAME ),
				'template_id'			=> esc_html__( 'Calendar ID', BOOKACTI_PLUGIN_NAME ),
				'template_title'		=> esc_html__( 'Calendar title', BOOKACTI_PLUGIN_NAME ),
				'activity_id'			=> esc_html__( 'Activity / Category ID', BOOKACTI_PLUGIN_NAME ),
				'activity_title'		=> esc_html__( 'Activity / Category title', BOOKACTI_PLUGIN_NAME ),
				'form_id'				=> esc_html__( 'Form ID', BOOKACTI_PLUGIN_NAME ),
				'order_id'				=> esc_html__( 'Order ID', BOOKACTI_PLUGIN_NAME )
			) );
		}
		
		
		/**
		 * Default booking data to export by default
		 * @since 1.6.0
		 * @return array
		 */
		function bookacti_get_bookings_export_default_columns() {
			return apply_filters( 'bookacti_bookings_export_default_columns', array(
				'booking_id',
				'booking_type',
				'status',
				'payment_status',
				'quantity',
				'creation_date',
				'customer_display_name',
				'customer_email',
				'event_title',
				'start_date',
				'end_date'
			) );
		}
		
		
		/**
		 * Convert a list of bookings to CSV format
		 * @since 1.6.0
		 * @param array $filters
		 * @param array $columns
		 * @return string
		 */
		function bookacti_convert_bookings_to_csv( $filters, $columns ) {
			
			$headers = bookacti_get_bookings_export_columns();
			$booking_list_items = bookacti_get_bookings_for_export( $filters, $columns );
			
			ob_start();
			
			// Display headers
			$count = 0;
			foreach( $columns as $i => $column_name ) {
				// Remove unknown columns
				if( ! isset( $headers[ $column_name ] ) ) { unset( $columns[ $i ] ); continue; }
				// Display comma separated column headers
				if( $count ) { echo ','; }
				++$count;
				echo str_replace( ',', '', strip_tags( $headers[ $column_name ] ) );
			}
			
			// Display rows
			foreach( $booking_list_items as $item ) {
				echo PHP_EOL;
				$count = 0;
				foreach( $columns as $column_name ) {
					if( $count ) { echo ','; }
					++$count;
					if( ! isset( $item[ $column_name ] ) ) { continue; }
					echo str_replace( ',', '', strip_tags( $item[ $column_name ] ) );
				}
			}
			
			return ob_get_clean();
		}
		
		
		/**
		 * Get an array of bookings data formatted to be exported
		 * @since 1.6.0
		 * @param array $filters
		 * @param array $columns
		 * @return array
		 */
		function bookacti_get_bookings_for_export( $filters, $columns ) {
			
			$bookings = bookacti_get_bookings( $filters );
			
			// Check if the bookings list can contain groups
			$may_have_groups = false; 
			$single_only = $filters[ 'group_by' ] === 'none';
			if( ( ! $filters[ 'booking_group_id' ] || $filters[ 'group_by' ] === 'booking_group' ) && ! $filters[ 'booking_id' ] ) {
				$may_have_groups = true;
			}
			
			// Check if we will need user data
			$has_user_data = false;
			foreach( $columns as $column_name ) {
				if( $column_name !== 'customer_id' && substr( $column_name, 0, 9 ) === 'customer_' ) { 
					$has_user_data = true; break; 
				} 
			}
			
			if( ( $may_have_groups || $single_only ) || $has_user_data ) {
				$group_ids = array();
				$user_ids = array();
				foreach( $bookings as $booking ) {
					if( $booking->user_id && is_numeric( $booking->user_id ) && ! in_array( $booking->user_id, $user_ids, true ) ) { $user_ids[] = $booking->user_id; }
					if( $booking->group_id && ! in_array( $booking->group_id, $group_ids, true ) )	{ $group_ids[] = $booking->group_id; }
				}
			}
			
			// Retrieve the required groups data only
			$booking_groups		= array();
			$displayed_groups	= array();
			if( ( $may_have_groups || $single_only ) && $group_ids ) {
				// Get only the groups that will be displayed
				$group_filters = bookacti_format_booking_filters( array( 'in__booking_group_id' => $group_ids, 'templates' => '' ) );
				
				// If the bookings are grouped by booking groups, 
				// booking group meta will already be attached to the booking representing its group 
				$group_filters[ 'fetch_meta' ] = $filters[ 'group_by' ] !== 'booking_group';
				
				$booking_groups = bookacti_get_booking_groups( $group_filters );
			}
			
			// Retrieve information about users and stock them into an array sorted by user id
			$users = array();
			if( $has_user_data ) {
				$users = bookacti_get_users_data( array( 'include' => $user_ids ) );
			}
			$unknown_user_id = esc_attr( apply_filters( 'bookacti_unknown_user_id', 'unknown_user' ) );
			
			// Build booking list
			$booking_items = array();
			foreach( $bookings as $booking ) {
				
				$group = $booking->group_id && ! empty( $booking_groups[ $booking->group_id ] ) ? $booking_groups[ $booking->group_id ] : null;
				
				// Display one single row for a booking group, instead of each bookings of the group
				if( $booking->group_id && $may_have_groups && ! $single_only ) {
					// If the group row has already been displayed, or if it is not found, continue
					if( isset( $displayed_groups[ $booking->group_id ] ) )	{ continue; }
					if( empty( $booking_groups[ $booking->group_id ] ) )	{ continue; }
					
					$booking_type	= 'group';
					$id				= $group->id;
					$user_id		= $group->user_id;
					$status			= $group->state;
					$paid			= $group->payment_status;
					$event_id		= $group->event_group_id;
					$title			= $group->group_title;
					$start			= $group->start;
					$end			= $group->end;
					$quantity		= $group->quantity;
					$form_id		= $group->form_id;
					$order_id		= $group->order_id;
					$activity_id	= $group->category_id;
					$activity_title	= $group->category_title;
					
					$displayed_groups[ $booking->group_id ] = $booking->id;
				
				// Single booking
				} else {
					$booking_type	= 'single';
					$id				= $booking->id;
					$user_id		= $booking->user_id;
					$status			= $booking->state;
					$paid			= $booking->payment_status;
					$title			= $booking->event_title;
					$event_id		= $booking->event_id;
					$start			= $booking->event_start;
					$end			= $booking->event_end;
					$quantity		= $booking->quantity;
					$form_id		= $booking->form_id;
					$order_id		= $booking->order_id;
					$activity_id	= $booking->activity_id;
					$activity_title	= $booking->activity_title;
				}
				
				$booking_data = array( 
					'booking_id'			=> $id,
					'booking_type'			=> $booking_type,
					'status'				=> $status,
					'payment_status'		=> $paid,
					'quantity'				=> $quantity,
					'creation_date'			=> $booking->creation_date,
					'event_id'				=> $event_id,
					'event_title'			=> apply_filters( 'bookacti_translate_text', $title ),
					'start_date'			=> $start,
					'end_date'				=> $end,
					'template_id'			=> $booking->template_id,
					'template_title'		=> apply_filters( 'bookacti_translate_text', $booking->template_title ),
					'activity_id'			=> $activity_id,
					'activity_title'		=> apply_filters( 'bookacti_translate_text', $activity_title ),
					'form_id'				=> $form_id,
					'order_id'				=> $order_id,
					'customer_id'			=> $user_id
				);
				
				// Format customer column
				$user = null;
				if( $has_user_data ) {
					// If the customer has an account
					if( ! empty( $users[ $user_id ] ) ) {
						$user = $users[ $user_id ];
						$booking_data = array_merge( $booking_data, array(
							'customer_display_name'	=> $user->display_name,
							'customer_first_name'	=> ! empty( $user->first_name ) ? $user->first_name : '',
							'customer_last_name'	=> ! empty( $user->last_name ) ? $user->last_name : '',
							'customer_email'		=> ! empty( $user->user_email ) ? $user->user_email : '',
							'customer_phone'		=> ! empty( $user->phone ) ? $user->phone : ''
						));
						
					// If the booking was made without account
					} else if( $user_id === $unknown_user_id || is_email( $user_id ) ) {
						$booking_meta = $group && $filters[ 'group_by' ] !== 'booking_group' ? $group : $booking;
						$booking_data = array_merge( $booking_data, array(
							'customer_display_name'	=> '',
							'customer_first_name'	=> ! empty( $booking_meta->user_first_name ) ? $booking_meta->user_first_name : '',
							'customer_last_name'	=> ! empty( $booking_meta->user_last_name ) ? $booking_meta->user_last_name : '',
							'customer_email'		=> ! empty( $booking_meta->user_email ) ? $booking_meta->user_email : '',
							'customer_phone'		=> ! empty( $booking_meta->user_phone ) ? $booking_meta->user_phone : ''
						));
						$booking_data[ 'customer_display_name' ] .= ! empty( $booking_data[ 'customer_first_name' ] ) ? $booking_data[ 'customer_first_name' ] : '';
						$booking_data[ 'customer_display_name' ] .= empty( $booking_data[ 'customer_first_name' ] ) && ! empty( $booking_data[ 'customer_last_name' ] ) ? ' ' : '';
						$booking_data[ 'customer_display_name' ] .= ! empty( $booking_data[ 'customer_last_name' ] ) ? $booking_data[ 'customer_last_name' ] : '';
					}
				}
					
				/**
				 * Third parties can add or change columns content, but do your best to optimize your process
				 * @since 1.6.0
				 */
				$booking_item = apply_filters( 'bookacti_booking_export_columns_content', $booking_data, $booking, $group, $user, $filters, $columns );
				
				$booking_items[ $booking->id ] = $booking_item;
			}
			
			/**
			 * Third parties can add or change rows and columns, but do your best to optimize your process
			 * @since 1.6.0
			 */
			return apply_filters( 'bookacti_booking_items_to_export', $booking_items, $bookings, $booking_groups, $displayed_groups, $users, $filters, $columns );
		}




// REFUND BOOKING

	/**
	 * Get available actions user can take to be refunded 
	 * 
	 * @return array
	 */
	function bookacti_get_refund_actions(){
		$possible_actions_array = array(
			'email' => array( 
				'id'			=> 'email',
				'label'			=> __( 'Email', BOOKACTI_PLUGIN_NAME ),
				'description'	=> __( 'Send a refund request by email to the administrator.', BOOKACTI_PLUGIN_NAME ) )
		);

		return apply_filters( 'bookacti_refund_actions', $possible_actions_array );
	}
	
	
	/**
	 * Get refund actions for a specific booking
	 * @version 1.6.0
	 * @param int|object $booking
	 * @return array
	 */
	function bookacti_get_refund_actions_by_booking_id( $booking ) {
		return bookacti_get_refund_actions_by_booking_type( $booking, 'single' );
	}
	
	
	/**
	 * Get refund actions for a specific booking group
	 * 
	 * @since 1.1.0
	 * 
	 * @param int|object $booking_group
	 * @return array
	 */
	function bookacti_get_refund_actions_by_booking_group_id( $booking_group ) {
		return bookacti_get_refund_actions_by_booking_type( $booking_group, 'group' );
	}

	/**
	 * Get refund actions for a specific booking or booking group
	 * @since 1.1.0
	 * @version 1.6.0
	 * @param int|object $booking
	 * @param string $booking_type Defined if the given id is a booking id or a booking group id. Accepted values are 'single' and 'group'.
	 * @return array
	 */
	function bookacti_get_refund_actions_by_booking_type( $booking, $booking_type = 'single' ) {
		
		$possible_actions = bookacti_get_refund_actions();
		
		// If current user is a customer
		if( ! current_user_can( 'bookacti_edit_bookings' ) ) {
			// Keep only allowed action
			$allowed_actions = bookacti_get_setting_value( 'bookacti_cancellation_settings', 'refund_actions_after_cancellation' );
			if( ! is_array( $allowed_actions ) ) {
				if( ! empty( $allowed_actions ) ) {
					$allowed_actions = array( $allowed_actions );
				} else {
					$allowed_actions = array();
				}
			}
			// Keep all possible actions that are allowed
			$possible_actions = array_intersect_key( $possible_actions, array_flip( $allowed_actions ) );
		
		// If current user is an admin
		} else {
			// Email action is useless, remove it
			if( isset( $possible_actions[ 'email' ] ) ) { unset( $possible_actions[ 'email' ] ); }
		}
		
		if( $booking_type === 'single' ) {
			$possible_actions = apply_filters( 'bookacti_refund_actions_by_booking', $possible_actions, $booking );
		} else if( $booking_type === 'group' ) {
			$possible_actions = apply_filters( 'bookacti_refund_actions_by_booking_group', $possible_actions, $booking );
		}
		
		return $possible_actions;
	}
	
	
	/**
	 * Get dialog refund text for a specific booking
	 * 
	 * @version 1.1.0
	 * 
	 * @param int $booking_id
	 * @return string
	 */
	function bookacti_get_refund_dialog_html_by_booking_id( $booking_id ) {
		return bookacti_get_refund_dialog_html_by_booking_type( $booking_id, 'single' );
	}
	
	
	/**
	 * Get dialog refund text for a specific booking
	 * 
	 * @version 1.1.0
	 * 
	 * @param int $booking_group_id
	 * @return string
	 */
	function bookacti_get_refund_dialog_html_by_booking_group_id( $booking_group_id ) {
		return bookacti_get_refund_dialog_html_by_booking_type( $booking_group_id, 'group' );
	}
	
	
	/**
	 * Get dialog refund text for a specific booking
	 * 
	 * @since 1.1.0
	 * 
	 * @param int $booking_or_booking_group_id
	 * @param string $booking_type Defined if the given id is a booking id or a booking group id. Accepted values are 'single' and 'group'.
	 * @return string
	 */
	function bookacti_get_refund_dialog_html_by_booking_type( $booking_or_booking_group_id, $booking_type = 'single' ) {

		$possible_actions = bookacti_get_refund_actions_by_booking_type( $booking_or_booking_group_id, $booking_type );

		$actions_list = '';
		foreach( $possible_actions as $possible_action ){
			$actions_list .= '<div class="bookacti-refund-option" >'
								. '<span class="bookacti-refund-option-radio" >'
									. '<input '
										. ' type="radio" '
										. ' name="refund-action" '
										. ' value="' . esc_attr( $possible_action['id'] ) . '" '
										. ' id="bookacti-refund-action-' . esc_attr( $possible_action['id'] ) . '" '
										. ' class="bookacti-refund-action" '
									. '/>'
								. '</span>'
								. '<label for="bookacti-refund-action-' . esc_attr( $possible_action['id'] ) . '" class="bookacti-refund-option-label-and-description" >'
									. '<strong class="bookacti-refund-option-label" >' . esc_html( $possible_action['label'] ). ':</strong> '
									. '<span class="bookacti-refund-option-description" >' . esc_html( $possible_action['description'] ) . '</span>'
								. '</label>'
							. '</div>';
		}

		// Define title and add actions list
		$html_to_return		= '';
		if( empty( $possible_actions ) ) {
			$html_to_return .= '<div id="bookacti-no-refund-option" >';
			$html_to_return .= esc_html__( 'Sorry, no available refund option were found. Please contact the administrator.', BOOKACTI_PLUGIN_NAME );
			$html_to_return .= '</div>';
		} else {

			$html_to_return .= apply_filters( 'bookacti_before_refund_actions', '', $booking_or_booking_group_id, $booking_type );

			$html_to_return .= '<div id="bookacti-refund-option-title" >';
			if( count( $possible_actions ) === 1 ) {
				$html_to_return .= esc_html__( 'There is only one available refund option:', BOOKACTI_PLUGIN_NAME );
			} else {
				$html_to_return .= esc_html__( 'Pick a refund option:', BOOKACTI_PLUGIN_NAME );
			}
			$html_to_return .= '</div>';

			$html_to_return .= '</div><form id="bookacti-refund-options" >';
			$html_to_return .= wp_nonce_field( 'bookacti_refund_booking', 'nonce_refund_booking', true, false );
			$html_to_return .= $actions_list;
			$html_to_return .= '</form>';
		}

		return $html_to_return;
	}


	/**
	 * Send a refund request by email for a specific booking
	 * @version 1.6.0
	 * @param int $booking_id
	 * @param string $booking_type Defined if the given id is a booking id or a booking group id. Accepted values are 'single' and 'group'.
	 * @param string $user_message
	 * @return boolean
	 */
	function bookacti_send_email_refund_request( $booking_id, $booking_type, $user_message = false ) {
		
		$to = apply_filters( 'bookacti_refund_request_email_to', array( get_option( 'admin_email' ) ), $booking_id, $booking_type );
		
		/* translators: %1$s is the booking id */
		$subject	= $booking_type === 'group' ? esc_html__( 'Refund request for booking group %1$s', BOOKACTI_PLUGIN_NAME ) : esc_html__( 'Refund request for booking %1$s', BOOKACTI_PLUGIN_NAME );
		$subject	= apply_filters( 'bookacti_refund_request_email_subject', sprintf( $subject, $booking_id ), $booking_id, $booking_type );
		
		$data = array();
		
		// Single booking
		if( $booking_type === 'single' ) {
			
			$booking = bookacti_get_booking_by_id( $booking_id );
			if( $booking ) {
				$data['booking']					= array();
				$data['booking']['calendar_id']		= $booking->template_id;
				$data['booking']['activity_name']	= apply_filters( 'bookacti_translate_text', $booking->title ) . ' (' . _x( 'id', 'An id is a unique identification number' ) . ': ' . $booking->activity_id . ')';
				$data['booking']['event_start']		= bookacti_format_datetime( $booking->event_start );
				$data['booking']['event_end']		= bookacti_format_datetime( $booking->event_end );
				$data['booking']['quantity']		= $booking->quantity;
				$data['booking']['status']			= $booking->state;
			}
			
		// Booking Group
		} else if( $booking_type === 'group' ) {
			
			$booking_group	= bookacti_get_booking_group_by_id( $booking_id );
			$bookings		= bookacti_get_bookings_by_booking_group_id( $booking_id );
			if( $booking_group || $bookings ) {
				$data['booking_group'] = array();
			}
			if( $bookings ) {
				$data['booking_group']['calendar_id']	= $bookings[0]->template_id;
				$data['booking_group']['events']		= bookacti_get_formatted_booking_events_list( $bookings, 'show' );
			}
			if( $booking_group ) {
				$data['booking_group']['status'] = $booking_group->state;
			}
		}
		
		$user_id = ! empty( $booking_group ) ? $booking_group->user_id : ( ! empty( $booking ) ? $booking->user_id : '' );
		if( $user_id ) {
			$data['user'] = array();
			$user = is_numeric( $user_id ) ? get_user_by( 'id', $user_id ) : null;
			
			if( $user ) {
				$data['user']['name']	= isset( $user->first_name ) && isset( $user->last_name ) ? $user->first_name . ' ' . $user->last_name : $user->user_login;
				$data['user']['name']	= '<a href="' . esc_url( get_edit_user_link() ) . '">' . esc_html( $data['user']['name'] ) . '</a>';
				$data['user']['email']	= '<a href="mailto:' . esc_attr( $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a>';
			} else {
				$object_type	= $booking_type === 'group' ? 'booking_group' : 'booking';
				$booking_meta	= bookacti_get_metadata( $object_type, $booking_id );
				$user_email		= ! empty( $booking_meta[ 'user_email' ] ) ? $booking_meta[ 'user_email' ] : '';
				
				$data['user']['name'] = '';
				$data['user']['name'] .= ! empty( $booking_meta[ 'user_first_name' ] ) ? $booking_meta[ 'user_first_name' ] : '';
				$data['user']['name'] .= empty( $booking_meta[ 'user_first_name' ] ) && ! empty( $booking_meta[ 'user_last_name' ] ) ? ' ' : '';
				$data['user']['name'] .= ! empty( $booking_meta[ 'user_last_name' ] ) ? $booking_meta[ 'user_last_name' ] : '';
				$data['user']['email']	= is_email( $user_email ) ? '<a href="mailto:' . esc_attr( $user_email ) . '">' . esc_html( $user_email ) . '</a>' : $user_email;
			}
		}
		

		$data = apply_filters( 'bookacti_refund_request_email_data', $data, $booking_id, $booking_type );

		/* translators: %1$s is a user name and %2$s is the booking ID. */
		$message = '<h3>' . sprintf( esc_html__( '%1$s wants to be refunded for booking %2$s', BOOKACTI_PLUGIN_NAME ), $data['user']['name'], $booking_id ) . '</h3>';
		foreach( $data as $category_name => $category_data ) {
			$message .= '<h4>' . esc_html( ucfirst ( str_replace( '_', ' ', $category_name ) ) ) . '</h4>';
			$message .= '<table style="border: none;" >';
			foreach( $category_data as $name => $value ) {
				$message .= '<tr><td style="border: none; width: 135px; padding-right: 15px;">' . esc_html( ucfirst ( str_replace( '_', ' ', $name ) ) ) . '</td><td>' . $value . '</td>';
			}
			$message .= '</table>';
		}

		/* translators: Message left by the user */
		if( $user_message ) {
			$message	.= '<h4>' . esc_html__( 'User message', BOOKACTI_PLUGIN_NAME ). '</h4>';
			$message	.= '<em>' . esc_html( $user_message ) . '</em><br/>';
		}

		$message	= apply_filters( 'bookacti_refund_request_email_message', $message, $booking_id, $booking_type, $data, $user_message );
		$headers	= apply_filters( 'bookacti_refund_request_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ) );

		$sent = wp_mail( $to, $subject, $message, $headers );

		return $sent;
	}




// FORMATTING
	
	/**
	 * Retrieve booking states labels and display data
	 * @version 1.6.0
	 * @return array
	 */
	function bookacti_get_booking_state_labels() {
		return apply_filters( 'bookacti_booking_states_labels_array', array(
			'delivered'			=> array( 'display_state' => 'good',	'label' => esc_html__( 'Delivered', BOOKACTI_PLUGIN_NAME ) ),
			'booked'			=> array( 'display_state' => 'good',	'label' => esc_html__( 'Booked', BOOKACTI_PLUGIN_NAME ) ),
			'pending'			=> array( 'display_state' => 'warning',	'label' => esc_html__( 'Pending', BOOKACTI_PLUGIN_NAME ) ),
			'cancelled'			=> array( 'display_state' => 'bad',		'label' => esc_html__( 'Cancelled', BOOKACTI_PLUGIN_NAME ) ),
			'refunded'			=> array( 'display_state' => 'bad',		'label' => esc_html__( 'Refunded', BOOKACTI_PLUGIN_NAME ) ),
			'refund_requested'	=> array( 'display_state' => 'bad',		'label' => esc_html__( 'Refund requested', BOOKACTI_PLUGIN_NAME ) )
		) );
	}
	
	
	/**
	 * Retrieve payment status labels and display data
	 * @since 1.3.0
	 * @version 1.6.0
	 * @return array
	 */
	function bookacti_get_payment_status_labels() {
		return apply_filters( 'bookacti_payment_status_labels_array', array(
			'none'	=> array( 'display_state' => 'disabled','label' => esc_html__( 'No payment required', BOOKACTI_PLUGIN_NAME ) ),
			'owed'	=> array( 'display_state' => 'warning',	'label' => esc_html__( 'Owed', BOOKACTI_PLUGIN_NAME ) ),
			'paid'	=> array( 'display_state' => 'good',	'label' => esc_html__( 'Paid', BOOKACTI_PLUGIN_NAME ) )
		) );
	}
	
	
	/**
	 * Give a the formatted and translated booking state
	 * 
	 * @version 1.3.0
	 * @param string $state
	 * @param boolean $icon_only
	 * @return string
	 */
	function bookacti_format_booking_state( $state, $icon_only = false ) {
		$booking_states_labels = bookacti_get_booking_state_labels();
		
		$formatted_value = '';
		if( isset( $booking_states_labels[ $state ] ) ) {
			if( $icon_only ) {
				$formatted_value = '<span class="bookacti-booking-state bookacti-booking-state-' . esc_attr( $booking_states_labels[ $state ][ 'display_state' ] ) . ' bookacti-tip" data-booking-state="' . esc_attr( $state ) . '" data-tip="'. esc_html( $booking_states_labels[ $state ][ 'label' ] ) . '" ></span>';
			} else {
				$formatted_value = '<span class="bookacti-booking-state bookacti-booking-state-' . esc_attr( $booking_states_labels[ $state ][ 'display_state' ] ) . '" data-booking-state="' . esc_attr( $state ) . '" >' . esc_html( $booking_states_labels[ $state ][ 'label' ] ) . '</span>';
			}
		} else if( $state ) {
			$formatted_value = '<span class="bookacti-booking-state" data-booking-state="' . esc_attr( $state ) . '" >' . esc_html__( $state, BOOKACTI_PLUGIN_NAME ) . '</span>';
		}

		return apply_filters( 'bookacti_booking_states_display', $formatted_value, $state, $icon_only );
	}
	
	
	/**
	 * Give a the formatted and translated payment status
	 * 
	 * @since 1.3.0
	 * @param string $status
	 * @param boolean $icon_only
	 * @return string
	 */
	function bookacti_format_payment_status( $status, $icon_only = false ) {
		$payment_status_labels = bookacti_get_payment_status_labels();
		
		$formatted_value = '';
		if( isset( $payment_status_labels[ $status ] ) ) {
			if( $icon_only ) {
				$formatted_value = '<span class="bookacti-payment-status bookacti-payment-status-' . esc_attr( $payment_status_labels[ $status ][ 'display_state' ] ) . ' bookacti-tip" data-payment-status="' . esc_attr( $status ) . '" data-tip="'. esc_html( $payment_status_labels[ $status ][ 'label' ] ) . '" ></span>';
			} else {
				$formatted_value = '<span class="bookacti-payment-status bookacti-payment-status-' . esc_attr( $payment_status_labels[ $status ][ 'display_state' ] ) . '" data-payment-status="' . esc_attr( $status ) . '" >' . esc_html( $payment_status_labels[ $status ][ 'label' ] ) . '</span>';
			}
		} else if( $status ) {
			$formatted_value = '<span class="bookacti-payment-status" data-payment-status="' . esc_attr( $status ) . '" >' . esc_html__( $status, BOOKACTI_PLUGIN_NAME ) . '</span>';
		}

		return apply_filters( 'bookacti_payment_status_display', $formatted_value, $status, $icon_only );
	}

	
	/**
	 * Give an array of all ACTIVE booking state, every other booking states will be considered as INACTIVE
	 * @version 1.6.0
	 * @return array
	 */
	function bookacti_get_active_booking_states() {
		return apply_filters( 'bookacti_active_booking_states', array( 'delivered', 'booked', 'pending' ) );
	}	




// SHORTCODE BOOKING LIST

/**
 * Get booking list columns 
 * 
 * @since 1.3.0
 * @param int|string $user_id
 * @return array
 */
function bookacti_get_booking_list_columns( $user_id = 0 ) {
	
	// Set up booking list columns
	$columns = apply_filters( 'bookacti_user_bookings_list_columns_titles', array(
		10	=> array( 'id' => 'id',			'title' => esc_html_x( 'id', 'An id is a unique identification number' ), BOOKACTI_PLUGIN_NAME ),
		20	=> array( 'id' => 'activity',	'title' => esc_html__( 'Activity', BOOKACTI_PLUGIN_NAME ) ),
		40	=> array( 'id' => 'quantity',	'title' => esc_html__( 'Quantity', BOOKACTI_PLUGIN_NAME ) ),
		50	=> array( 'id' => 'state',		'title' => esc_html_x( 'State', 'State of a booking', BOOKACTI_PLUGIN_NAME ) ),
		100 => array( 'id' => 'actions',	'title' => esc_html__( 'Actions', BOOKACTI_PLUGIN_NAME ) )
	), $user_id );
	
	// Order columns
	ksort( $columns );
	
	return $columns;
}
	
/**
 * Get booking list rows
 * @since 1.3.0
 * @version 1.6.0
 * @param array $bookings
 * @param array $columns
 * @param int|string $user_id
 * @return string
 */
function bookacti_get_booking_list_rows( $bookings, $columns = array(), $user_id = 0 ) {
	
	if( ! $columns ) { $columns = bookacti_get_booking_list_columns( $user_id ); }
	
	$list_items				= array();
	$groups_already_added	= array();
	$group_ids				= array();
	$booking_groups			= array();
	
	// Get required booking groups
	foreach( $bookings as $booking ) {
		if( ! empty( $booking->group_id ) && ! in_array( $booking->group_id, $group_ids, true ) ) {
			$group_ids[] = $booking->group_id;
		}
	}
	
	if( $group_ids ) {
		$group_filters = bookacti_format_booking_filters( array( 'in__booking_group_id' => $group_ids, 'templates' => '' ) );
		
		// If the booking are grouped by booking groups, 
		// booking group meta will already be attached to the booking representing its group 
		if( $group_filters[ 'fetch_meta' ] && $group_filters[ 'group_by' ] === 'booking_group' ) { $group_filters[ 'fetch_meta' ] = false; }

		$booking_groups = bookacti_get_booking_groups( $group_filters );
	}
	
	// Build an array of bookings rows
	foreach( $bookings as $booking ) {
		// Single Bookings
		if( empty( $booking->group_id ) ) {

			$list_items[] = apply_filters( 'bookacti_user_bookings_list_columns_value', array(
				'id'		=> $booking->id,
				'activity'	=> bookacti_get_formatted_booking_events_list( array( $booking ) ),
				'quantity'	=> $booking->quantity,
				'state'		=> bookacti_format_booking_state( $booking->state ),
				'actions'	=> bookacti_get_booking_actions_html( $booking, 'front' ),
				'type'		=> 'single'
			), $booking, $user_id );

		// Booking groups
		} else if( ! in_array( $booking->group_id, $groups_already_added, true ) ) {
			
			$group_filters = bookacti_format_booking_filters( array( 'in__booking_group_id' => $booking->group_id, 'templates' => '' ) );
			$group_bookings = bookacti_get_bookings( $group_filters ); 

			$list_items[] = apply_filters( 'bookacti_user_bookings_list_columns_value', array(
				'id'		=> $booking->group_id,
				'activity'	=> bookacti_get_formatted_booking_events_list( $group_bookings ),
				'quantity'	=> $booking_groups[ $booking->group_id ]->quantity,
				'state'		=> bookacti_format_booking_state( $booking_groups[ $booking->group_id ]->state ),
				'actions'	=> bookacti_get_booking_group_actions_html( $booking_groups[ $booking->group_id ], 'front' ),
				'type'		=> 'group'
			), $booking, $user_id );

			// Flag the group as 'already added' to make it appears only once in the list
			$groups_already_added[] = $booking->group_id;
		}
	}

	
	// Build the HTML booking rows
	$rows = '';
	foreach( $list_items as $list_item ) {
		$rows .= "<tr>";
		foreach( $columns as $column ) {

			// Format output values
			switch ( $column[ 'id' ] ) {
				case 'id':
					$value = isset( $list_item[ 'id' ] ) ? intval( $list_item[ 'id' ] ) : '';
					break;
				case 'activity':
					$value = isset( $list_item[ 'activity' ] ) ? $list_item[ 'activity' ] : '';
					break;
				case 'quantity':
					$value = isset( $list_item[ 'quantity' ] ) ? intval( $list_item[ 'quantity' ] ) : '';
					break;
				case 'state':
				case 'actions':
				default:
					$value = isset( $list_item[ $column[ 'id' ] ] ) ? $list_item[ $column[ 'id' ] ] : '';
			}
			
			$column_id	 = sanitize_title_with_dashes( $column[ 'id' ] );
			$class_empty = empty( $value ) ? 'bookacti-empty-column' : '';
			$class_group = $list_item[ 'type' ] === 'group' ? 'bookacti-booking-group-' . $column_id : '';
			
			$rows .=  "<td data-title='" . esc_attr( $column[ 'title' ] ) 
					. "' class='bookacti-column-" . $column_id . ' ' . $class_empty . "' >"
					.	"<div class='bookacti-booking-" . $column_id . " " . $class_group . "' >"  
					.		$value 
					.	"</div>"
					. "</td>";
		} 
		$rows .= "</tr>";
	}
	
	// If there are no booking rows
	if( empty( $list_items ) ) {
		$rows	.= '<tr>'
				.	'<td colspan="' . esc_attr( count( $columns ) ) . '">' . esc_html__( "You don't have any bookings.", BOOKACTI_PLUGIN_NAME ) . '</td>'
				. '</tr>';
	}
	
	return $rows;
}