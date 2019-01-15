<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Initialize Booking Activities shortcodes
 */
add_shortcode( 'bookingactivities_calendar', 'bookacti_shortcode_calendar' );
add_shortcode( 'bookingactivities_form', 'bookacti_shortcode_booking_form' );
add_shortcode( 'bookingactivities_list', 'bookacti_shortcode_bookings_list' );


/**
 * Display a calendar of activities / templates via shortcode
 * Eg: [bookingactivities_calendar	id='my-cal'					// Any id you want
 *									classes='full-width'			// Any class you want
 *									calendars='2'				// Comma separated calendar ids
 *									activities='1,2,10'			// Comma separated activity ids
 *									group_categories='5,10'		// Comma separated group category ids
 *									groups_only='0'				// Only display groups
 *									groups_single_events='0'		// Allow to book grouped events as single events
 *									method='calendar' ]			// Display method
 * 
 * @version 1.1.0
 * @deprecated since 1.5.0
 * @param array $atts [id, classes, calendars, activities, groups, method]
 * @param string $content
 * @param string $tag Should be "bookingactivities_calendar"
 * @return string The calendar corresponding to given parameters
 */
function bookacti_shortcode_calendar( $atts = array(), $content = null, $tag = '' ) {
	
	// normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
	
	$output = '<div class="bookacti-booking-system-calendar-only" >'
			.		bookacti_get_booking_system( $atts )
			. '</div>';
	
    return apply_filters( 'bookacti_shortcode_' . $tag . '_output', $output, $atts, $content );
}


/**
 * Display a booking form via shortcode
 * Eg: [bookingactivities_form form="Your form ID" id="Your form instance CSS ID"]
 * 
 * @version 1.5.0
 * 
 * @param array $atts [form, id]
 * @param string $content
 * @param string $tag Should be "bookingactivities_form"
 * @return string The booking form corresponding to given parameters
 */
function bookacti_shortcode_booking_form( $atts = array(), $content = null, $tag = '' ) {
	
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
	if( ! empty( $atts[ 'form' ] ) ) {
		$default_atts = array(
			'form' => 0,
			'id' => ''
		);
		$atts = shortcode_atts( $default_atts, $atts, $tag );
		
		// display the booking form
		return bookacti_display_form( $atts[ 'form' ], $atts[ 'id' ], 'display', false );
	}
	
	
	/** BACKWARD COMPATIBILITY < 1.5 **/
	
	// Format booking system attributes
	$bs_atts = bookacti_format_booking_system_attributes( $atts );
	
	// Format form attributes
	$atts = array();
	$atts[ 'url' ]		= ! empty( $atts[ 'url' ] ) ? esc_url( $atts[ 'url' ] ) : '';
	$atts[ 'button' ]	= ! empty( $atts[ 'button' ] ) ? esc_html( sanitize_text_field( $atts[ 'button' ] ) ) : bookacti_get_message( 'booking_form_submit_button' );
	$atts[ 'id' ]		= ! empty( $atts[ 'id' ] ) ? esc_attr( $atts[ 'id' ] ) : rand();
	$atts = array_merge( $bs_atts, $atts );
	
	$output = "<form action='" . $atts[ 'url' ] . "' 
					class='bookacti-booking-form' 
					id='bookacti-form-" . $atts[ 'id' ] . "' >
				  <input type='hidden' name='action' value='bookactiSubmitBookingFormBWCompat' />
				  <input type='hidden' name='nonce_booking_form' value='" . wp_create_nonce( 'bookacti_booking_form' ) . "' />"

				  . bookacti_get_booking_system( $atts ) .

				  "<div class='bookacti-form-field-container' >
					  <label for='bookacti-quantity-booking-form-" . $atts[ 'id' ] . "' class='bookacti-form-field-label' >"
						  . __( 'Quantity', BOOKACTI_PLUGIN_NAME ) .
					  "</label>
					  <input name='quantity'
							 id='bookacti-quantity-booking-form-" . $atts[ 'id' ] . "'
							 class='bookacti-form-field bookacti-quantity'
							 type='number' 
							 min='1'
							 value='1' />
				  </div>"

				  .  apply_filters( 'bookacti_booking_form_fields', '', $atts, $content ) .

				  "<div class='bookacti-form-field-container bookacti-form-field-name-submit' >
					  <input type='submit' 
							 class='button' 
							 value='" . $atts[ 'button' ] . "' />
				  </div>
				  <div class='bookacti-notices' style='display:none;'></div>
			  </form>";
	
    return apply_filters( 'bookacti_shortcode_' . $tag . '_output', $output, $atts, $content );
}


/**
 * Display a user related booking list via shortcode
 * @version 1.6.0
 * @param array $atts [user_id, per_page, status, and any booking filter such as 'from', 'to', 'activities'...]
 * @param string $content
 * @param string $tag Should be "bookingactivities_list"
 * @return string The booking list corresponding to given parameters
 */
function bookacti_shortcode_bookings_list( $atts = array(), $content = null, $tag = '' ) {
	if( ! is_user_logged_in() ) { return apply_filters( 'bookacti_shortcode_' . $tag . '_output', '', $atts, $content ); }
	
	// normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
	
	// Format 'user_id' attribute
	if( isset( $atts[ 'user_id' ] ) ) {
		$atts[ 'user_id' ] = esc_attr( $atts[ 'user_id' ] );
		
	// Backward Compatibility for "user" attribute (instead of "user_id")
	} else if( isset( $atts[ 'user' ] ) ) {
		$atts[ 'user_id' ] = esc_attr( $atts[ 'user' ] );
		unset( $atts[ 'user' ] );
	}
	
	$default_atts = array_merge( bookacti_get_default_booking_filters(), array(
		'user_id'	=> get_current_user_id(),
		'per_page'	=> 10,
		'status'	=> apply_filters( 'bookacti_booking_list_displayed_status', array( 'delivered', 'booked', 'pending', 'cancelled', 'refunded', 'refund_requested' ) ),
		'group_by'	=> 'booking_group'
	) );
    $atts = shortcode_atts( $default_atts, $atts, $tag );
	
	$atts = bookacti_format_string_booking_filters( $atts );
	$templates = $atts[ 'templates' ];
	$atts[ 'templates' ] = false;
	
	// Format booking filters
	$filters = bookacti_format_booking_filters( $atts );
	// Allow to filter by any template
	if( ! empty( $templates ) && is_array( $templates ) ) { $filters[ 'templates' ] = $templates; }
	$filters = apply_filters( 'bookacti_user_booking_list_booking_filters', $filters, $atts, $content );
	
	$bookings_nb = bookacti_get_number_of_booking_rows( $filters );	
	
	// Pagination
	$page_nb				= ! empty( $_GET[ 'bookacti_booking_list_paged' ] ) ? intval( $_GET[ 'bookacti_booking_list_paged' ] ) : 1;
	$per_page				= intval( $atts[ 'per_page' ] );
	$page_max				= ceil( $bookings_nb / $per_page );
	$filters[ 'per_page' ]	= $per_page;
	$filters[ 'offset' ]	= ( $page_nb - 1 ) * $filters[ 'per_page' ];
	
	// TABLE OUTPUT
	ob_start();
	?>
	<div id='bookacti-user-bookings-list-<?php echo $filters[ 'user_id' ]; ?>' class='bookacti-user-bookings-list'>
		<table>
			<thead>
				<tr>
				<?php
					$columns = bookacti_get_booking_list_columns( $filters[ 'user_id' ] );
					foreach( $columns as $column ) {
					?>
						<th class='bookacti-column-<?php echo sanitize_title_with_dashes( $column[ 'id' ] ); ?>'>
							<div class='bookacti-booking-<?php echo $column[ 'id' ]; ?>-title' >
								<?php echo $column[ 'title' ]; ?>
							</div>
						</th>
					<?php
					} 
				?>
				</tr>
			</thead>
			<tbody>
			<?php
				$bookings = bookacti_get_bookings( $filters );
				echo bookacti_get_booking_list_rows( $bookings, $columns, $filters[ 'user_id' ] ); 
			?>
			</tbody>
		</table>
		<?php if( $page_max > 1 ) { ?>
		<div class='bookacti-user-booking-list-pagination'>
		<?php
			if( $page_nb > 1 ) {
			?>
				<span class='bookacti-user-booking-list-previous-page'>
					<a href='<?php echo esc_url( add_query_arg( 'bookacti_booking_list_paged', ( $page_nb - 1 ) ) ); ?>' class='button'>
						<?php esc_html_e( 'Previous', BOOKACTI_PLUGIN_NAME ); ?>
					</a>
				</span>
			<?php
			}
			?>
			<span class='bookacti-user-booking-list-current-page'>
				<strong><?php echo $page_nb; ?></strong> / <em><?php echo $page_max; ?></em>
			</span>
			<?php
			if( $page_nb < $page_max ) {
			?>
				<span class='bookacti-user-booking-list-next-page'>
					<a href='<?php echo esc_url( add_query_arg( 'bookacti_booking_list_paged', ( $page_nb + 1 ) ) ); ?>' class='button'>
						<?php esc_html_e( 'Next', BOOKACTI_PLUGIN_NAME ); ?>
					</a>
				</span>
			<?php
			}
		?>
		</div>
		<?php } ?>
	</div>
	<?php
	// Include bookings dialogs if they are not already
	include_once( WP_PLUGIN_DIR . '/' . BOOKACTI_PLUGIN_NAME . '/view/view-bookings-dialogs.php' );
	
	return apply_filters( 'bookacti_shortcode_' . $tag . '_output', ob_get_clean(), $atts, $content );
}


/**
 * Check if booking form is correct and then book the event, or send the error message
 * 
 * @since 1.5.0 (was bookacti_controller_validate_booking_form)
 * @version 1.6.0
 * @deprecated since version 1.5.0
 */
function bookacti_deprecated_controller_validate_booking_form() {
	
	// Check nonce and capabilities
	$is_nonce_valid = check_ajax_referer( 'bookacti_booking_form', 'nonce_booking_form', false );
	$is_allowed		= is_user_logged_in();
	
	if( $is_nonce_valid && $is_allowed ) { 

		// Gether the form variables
		$booking_form_values = apply_filters( 'bookacti_booking_form_values', array(
			'user_id'			=> intval( get_current_user_id() ),
			'group_id'			=> is_numeric( $_POST[ 'bookacti_group_id' ] ) ? intval( $_POST[ 'bookacti_group_id' ] ) : 'single',
			'event_id'			=> intval( $_POST[ 'bookacti_event_id' ] ),
			'event_start'		=> bookacti_sanitize_datetime( $_POST[ 'bookacti_event_start' ] ),
			'event_end'			=> bookacti_sanitize_datetime( $_POST[ 'bookacti_event_end' ] ),
			'quantity'			=> intval( $_POST[ 'quantity' ] ),
			'default_state'		=> bookacti_get_setting_value( 'bookacti_general_settings', 'default_booking_state' ), 
			'payment_status'	=> bookacti_get_setting_value( 'bookacti_general_settings', 'default_payment_status' )
		) );

		//Check if the form is ok and if so Book temporarily the event
		$response = bookacti_validate_booking_form( $booking_form_values[ 'group_id' ], $booking_form_values[ 'event_id' ], $booking_form_values[ 'event_start' ], $booking_form_values[ 'event_end' ], $booking_form_values[ 'quantity' ] );
		
		if( $booking_form_values[ 'user_id' ] != get_current_user_id() && ! current_user_can( 'bookacti_edit_bookings' ) ) {
			$response[ 'status' ] = 'failed';
			$response[ 'message' ] = __( "You can't make a booking for someone else.", BOOKACTI_PLUGIN_NAME );
		}
		
		if( $response[ 'status' ] === 'success' ) {
						
			// Single Booking
			if( $booking_form_values[ 'group_id' ] === 'single' ) {
			
				$booking_id = bookacti_insert_booking(	$booking_form_values[ 'user_id' ], 
														$booking_form_values[ 'event_id' ], 
														$booking_form_values[ 'event_start' ],
														$booking_form_values[ 'event_end' ], 
														$booking_form_values[ 'quantity' ], 
														$booking_form_values[ 'default_state' ],
														$booking_form_values[ 'payment_status' ],
														null,
														$booking_form_values[ 'group_id' ] );
			
				if( ! empty( $booking_id ) ) {

					do_action( 'bookacti_booking_form_validated', $booking_id, $booking_form_values, 'single', 0 );
					
					$message = bookacti_get_message( 'booking_success' );
					wp_send_json( array( 'status' => 'success', 'message' => esc_html( $message ), 'booking_id' => $booking_id ) );
				}
			
			// Booking group
			} else {
				
				// Book all events of the group
				$booking_group_id = bookacti_book_group_of_events(	$booking_form_values[ 'user_id' ], 
																	$booking_form_values[ 'group_id' ], 
																	$booking_form_values[ 'quantity' ], 
																	$booking_form_values[ 'default_state' ], 
																	$booking_form_values[ 'payment_status' ], 
																	NULL );
				
				if( ! empty( $booking_group_id ) ) {

					do_action( 'bookacti_booking_form_validated', $booking_group_id, $booking_form_values, 'group', 0 );
					
					$message = __( 'Your events have been booked successfully!', BOOKACTI_PLUGIN_NAME );
					wp_send_json( array( 'status' => 'success', 'message' => esc_html( $message ), 'booking_group_id' => $booking_group_id ) );
				}
			}
			
			$message = __( 'An error occurred, please try again.', BOOKACTI_PLUGIN_NAME );
			
		} else {
			$message = $response[ 'message' ];
		}
		
	} else {
		$message = __( 'You are not allowed to do that.', BOOKACTI_PLUGIN_NAME );
		if( ! $is_allowed ) {
			$message = __( 'You are not logged in. Please create an account and log in first.', BOOKACTI_PLUGIN_NAME );
		}
		
		$response = array( 'status' => 'failed', 'error' => 'not_allowed', 'message' => $message );
	}
	
	$return_array = apply_filters( 'bookacti_booking_form_error', array( 'status' => 'failed', 'message' => $message ), $response );
	
	wp_send_json( array( 'status' =>  $return_array[ 'status' ], 'message' => esc_html( $return_array[ 'message' ] ) ) );
}
add_action( 'wp_ajax_bookactiSubmitBookingFormBWCompat', 'bookacti_deprecated_controller_validate_booking_form' );
add_action( 'wp_ajax_nopriv_bookactiSubmitBookingFormBWCompat', 'bookacti_deprecated_controller_validate_booking_form' );