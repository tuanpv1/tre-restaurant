<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// GENERAL

	/**
	 * Add woocommerce related translations
	 * 
	 * @since 1.0.0
	 * 
	 * @param type $translation_array
	 * @return type
	 */
	function bookacti_woocommerce_translation_array( $translation_array ) {
		
		$site_booking_method = bookacti_get_setting_value( 'bookacti_general_settings',	'booking_method' );
		
		$translation_array[ 'expired_min' ]						= esc_html__( 'expired', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'expired' ]							= esc_html__( 'Expired', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'in_cart' ]							= esc_html__( 'In cart', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'days' ]							= esc_html__( 'days', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'day' ]								= esc_html_x( 'day', 'singular of days',BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'error_remove_expired_cart_item' ]	= esc_html__(  'Error occurs while trying to remove expired cart item.', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'error_cart_expired' ]				= esc_html__( 'Your cart has expired.', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'coupon_code' ]						= esc_html__( 'Coupon', BOOKACTI_PLUGIN_NAME );
		/* translators: %1$s is the coupon code. Ex: AAB12. */
		$translation_array[ 'advice_coupon_code' ]				= esc_html__( 'The coupon code is %1$s. Use it on your next cart!', BOOKACTI_PLUGIN_NAME );
		/* translators: %1$s is the amount of the coupon. Ex: $10. */
		$translation_array[ 'advice_coupon_created' ]			= esc_html__( 'A %1$s coupon has been created. You can use it once for any order at any time.', BOOKACTI_PLUGIN_NAME );
		$translation_array[ 'add_product_to_cart_button_text' ]	= esc_html__( 'Add to cart', 'woocommerce' );
		$translation_array[ 'add_booking_to_cart_button_text' ]	= bookacti_get_message( 'booking_form_submit_button' );
		$translation_array[ 'site_booking_method' ]				= $site_booking_method;
		
		return $translation_array;
	}
	add_filter( 'bookacti_translation_array', 'bookacti_woocommerce_translation_array', 10, 1 );

	
	/**
	 * Change 'user_id' of bookings from customer id to user id when he logs in
	 * 
	 * @since 1.0.0
	 * @version 1.4.0
	 * @global WooCommerce $woocommerce
	 * @param string $user_login
	 * @param WP_User $user
	 */
	function bookacti_change_customer_id_to_user_id( $user_login, $user ) {
		
		global $woocommerce;
		$customer_id = $woocommerce->session->get_customer_id();
		
		// Make sure the customer was not logged in (it could be a user switching between two accounts)
		if( ! bookacti_user_id_exists( $customer_id ) ) {
			// update customer id to user id
			bookacti_update_bookings_user_id( $user->ID, $customer_id );
		}
					
		// Update the cart expiration date if the user is logged in
		$is_per_product_expiration	= bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_per_product' );
		if( ! $is_per_product_expiration ) {
			$cart_expiration_date = bookacti_get_cart_expiration_date_per_user( $user->ID );
			update_user_meta( $user->ID, 'bookacti_expiration_cart', $cart_expiration_date );
		}
		
		// Check if user's cart is still valid or change it if necessary (according to min and max bookings restrictions)
		bookacti_update_cart_item_quantity_according_to_booking_restrictions( $user->ID );
	}
	add_action( 'wp_login', 'bookacti_change_customer_id_to_user_id', 20, 2 );
	
	
	
// LOOP PRODUCTS PAGE
	
	/**
	 * Add 'activity' class to activity product in the products loop
	 * @param string $classes
	 * @param string $class
	 * @param int $post_id
	 * @return string
	 */
	function bookacti_add_activity_post_class( $classes, $class, $post_id ) {
		$is_activity = bookacti_product_is_activity( $post_id );
		if( $is_activity ) { $classes[] = 'bookacti-activity'; }
		return $classes;
	}
	add_filter( 'post_class', 'bookacti_add_activity_post_class', 10, 3 );
	
	
	/**
	 * Disable AJAX add to cart support for activities
	 * @param boolean $enabled
	 * @param string $feature
	 * @param WC_Product $product
	 * @return boolean
	 */
	function bookacti_disable_ajax_add_to_cart_support_for_activities( $enabled, $feature, $product ){
		if( $feature === 'ajax_add_to_cart' && $enabled ){
			if( bookacti_product_is_activity( $product ) ){
				$enabled = false;
			}
		}
		return $enabled;
	}
	add_filter( 'woocommerce_product_supports', 'bookacti_disable_ajax_add_to_cart_support_for_activities', 100, 3 );
	
	
	/**
	 * Change 'Add to cart' button URL to the single product page URL for activities
	 * @param string $url
	 * @param WC_Product $product
	 * @return string
	 */
	function bookacti_change_add_to_cart_url_for_activities( $url, $product ){
		if( bookacti_product_is_activity( $product ) ){
			$url = get_permalink( $product->get_id() );
		}
		return $url;
	}
	add_filter( 'woocommerce_product_add_to_cart_url', 'bookacti_change_add_to_cart_url_for_activities', 100, 2 );
	
	
	/**
	 * Change 'Add to cart' text for activities by user defined string
	 * @version 1.2.0
	 * @param string $text
	 * @param WC_Product $product
	 * @return string
	 */
	function bookacti_change_add_to_cart_text_for_activities( $text, $product ){
		if( bookacti_product_is_activity( $product ) ){
			$text = bookacti_get_message( 'booking_form_submit_button' );
		}
		return $text;
	}
	add_filter( 'woocommerce_product_add_to_cart_text', 'bookacti_change_add_to_cart_text_for_activities', 100, 2 );
	add_filter( 'woocommerce_product_single_add_to_cart_text', 'bookacti_change_add_to_cart_text_for_activities', 100, 2 );

	

// ADD TO CART (SINGLE PRODUCT PAGE)
	
	/**
	 * Add booking forms to single product page (front-end)
	 * @version 1.6.0
	 * @global WC_Product $product
	 */
	function bookacti_add_booking_system_in_single_product_page() {
		global $product;
		
		$is_activity = bookacti_product_is_activity( $product );
		if( ! $is_activity ) { return; }
		
		// Check if the product or one of its available variation is bound to a booking form
		$form_id = 0;
		if( $product->is_type( 'simple' ) ) {
			$form_id = get_post_meta( $product->get_id(), '_bookacti_form', true );
		}
		else if( $product->is_type( 'variable' ) ) {
			$variations = $product->get_available_variations();
			foreach( $variations as $variation ) {
				if( empty( $variation[ 'bookacti_is_activity' ] ) || empty( $variation[ 'bookacti_form_id' ] ) ) { continue; }
				$form_id = $variation[ 'bookacti_form_id' ];
				break;
			}
		}
		
		/** BACKWARD COMPATIBILITY < 1.5 **/
		if( ! $form_id ) {
			$booking_method			= get_post_meta( $product->get_id(), '_bookacti_booking_method', true );
			$template_id			= get_post_meta( $product->get_id(), '_bookacti_template', true );
			$activity_id			= get_post_meta( $product->get_id(), '_bookacti_activity', true );
			$group_categories		= get_post_meta( $product->get_id(), '_bookacti_group_categories', true );
			$groups_only			= get_post_meta( $product->get_id(), '_bookacti_groups_only', true );
			$groups_single_events	= get_post_meta( $product->get_id(), '_bookacti_groups_single_events', true );

			// Convert 'site' booking methods to actual booking method
			// And make sure the resulting booking method exists
			$available_booking_methods = bookacti_get_available_booking_methods();
			if( ! in_array( $booking_method, array_keys( $available_booking_methods ), true ) ) {
				if( $booking_method === 'site' ) {
					$site_booking_method = bookacti_get_setting_value( 'bookacti_general_settings', 'booking_method' );
					if( in_array( $site_booking_method, array_keys( $available_booking_methods ), true ) ) {
						$booking_method = $site_booking_method;
					} else {
						$booking_method = 'calendar';
					}
				} else {
					$booking_method = 'calendar';
				}
			}

			$atts = array( 
				'calendars'				=> is_numeric( $template_id ) ? array( $template_id ) : $template_id,
				'activities'			=> is_numeric( $activity_id ) ? array( $activity_id ) : $activity_id,
				'group_categories'		=> is_numeric( $group_categories ) ? array( $group_categories ) : $group_categories,
				'groups_only'			=> $groups_only === 'yes' ? 1 : 0,
				'groups_single_events'	=> $groups_single_events === 'yes' ? 1 : 0,
				'method'				=> $booking_method,
				'auto_load'				=> $product->is_type( 'variable' ) ? 0 : 1,
				'id'					=> 'bookacti-booking-system-product-' . $product->get_id(),
				'class'					=> 'bookacti-frontend-booking-system bookacti-woocommerce-product-booking-system'
			);
			bookacti_get_booking_system( $atts, true );
			return;
		} 
		/** END BACKWARD COMPATIBILITY < 1.5 **/
		
		
		$form_instance_id = '';
		// Show form on single product page or on variable product with a default value
		if( $product->is_type( 'simple' ) ) {
			$form_instance_id = 'product-' . $product->get_id();
		}
		else if( $product->is_type( 'variable' ) ) {
			$default_attributes = bookacti_get_product_default_attributes( $product );
			if( $default_attributes ) { 
				$default_variation_id = bookacti_get_product_variation_matching_attributes( $product, $default_attributes );
				if( $default_variation_id ) { 
					$form_id = get_post_meta( $default_variation_id, 'bookacti_variable_form', true );
					if( $form_id ) { 
						$form_instance_id = 'product-variation-' . $default_variation_id;
					}	
				}
			}
			
		} else if( $product->is_type( 'variation' ) ) {
			$form_id = get_post_meta( $product->get_id(), 'bookacti_variable_form', true );
			if( $form_id ) { 
				$form_instance_id = 'product-variation-' . $default_variation_id;
			}
		}
		
		// If no default variation are selected, or if if it's not an activity, or if doesn't have a form bound
		// display an empty form fields container
		if( ! $form_instance_id ) { 
			?>
				<div class='bookacti-form-fields'></div>
			<?php
			return;
		}
		
		?>
		<div class='bookacti-form-fields' 
			 data-product-id='<?php echo $product->get_id(); ?>'
			 data-variation-id='<?php if( ! empty( $default_variation_id ) ) { echo $default_variation_id; } ?>'
			 data-form-id='<?php echo $form_id; ?>'>
			<?php 
				$form_html = bookacti_display_form( $form_id, $form_instance_id, 'wc_product_init', false ); 
				echo $form_html;
				if( ! empty( $default_variation_id ) ) {
				?>
					<script>
						if( typeof bookacti.form_fields === 'undefined' ) { bookacti.form_fields = []; }
						bookacti.form_fields[ '<?php echo $form_id; ?>' ] = <?php echo json_encode( $form_html ); ?>;
					</script>
				<?php
				}
			?>			
		</div>
		<?php
	}
	add_action( 'woocommerce_before_add_to_cart_button', 'bookacti_add_booking_system_in_single_product_page', 20, 0 );
	
	
	/**
	 * Remove WC unsupported fields on product pages
	 * @since 1.5.0
	 * @version 1.5.2
	 * @param array $fields
	 * @param array $form
	 * @param string $instance_id
	 * @param string $context
	 * @return array
	 */
	function bookacti_remove_unsupported_fields_from_product_page( $fields, $form, $instance_id, $context ) {
		if( $context !== 'wc_product_init' && $context !== 'wc_switch_variation' ) { return $fields; }
		
		$unsupported_fields = bookacti_get_wc_unsupported_form_fields();
		foreach( $fields as $i => $field ) {
			if( in_array( $field[ 'type' ], $unsupported_fields, true ) ) {
				unset( $fields[ $i ] );
			}
		}
		return $fields;
	}
	add_filter( 'bookacti_displayed_form_fields', 'bookacti_remove_unsupported_fields_from_product_page', 10, 4 );
	
	
	/**
	 * Force auto-load calendar when variation are switched
	 * @since 1.5.2
	 * @param array $fields
	 * @param array $form
	 * @param string $instance_id
	 * @param string $context
	 * @return array
	 */
	function bookacti_force_auto_load_calendar_while_switching_variations( $fields, $form, $instance_id, $context ) {
		if( $context !== 'wc_switch_variation' ) { return $fields; }
		
		foreach( $fields as $i => $field ) {
			if( $field[ 'name' ] === 'calendar' ) {
				$fields[ $i ][ 'auto_load' ] = 1;
			}
		}
		return $fields;
	}
	add_filter( 'bookacti_displayed_form_fields', 'bookacti_force_auto_load_calendar_while_switching_variations', 20, 4 );
	
	
	/**
	 * Display a specific 'calendar' field on product pages
	 * @since 1.5.0
	 * @version 1.5.2
	 * @param array $field
	 * @param int $form_id
	 * @param string $instance_id
	 * @param string $context
	 */
	function bookacti_display_form_field_calendar_on_wc_product_page( $field, $instance_id, $context ) {
		if( $context !== 'wc_product_init' && $context !== 'wc_switch_variation' ) { return; }
		
		// Remove default behavior
		remove_action( 'bookacti_display_form_field_calendar', 'bookacti_display_form_field_calendar', 10 );
		
		// Do not keep ID and class (already used for the container)
		$field[ 'id' ] = $instance_id; 
		$field[ 'class' ] = 'bookacti-woocommerce-product-booking-system';

		// Display the booking system
		bookacti_get_booking_system( $field, true );
	}
	add_action( 'bookacti_display_form_field_calendar', 'bookacti_display_form_field_calendar_on_wc_product_page', 5, 3 );
	
	
	/**
	 * Add cart item data (all sent in one array)
	 * 
	 * @version 1.5.4
	 * @param array $cart_item_data
	 * @param int $product_id
	 * @param int $variation_id
	 * @return array
	 */
	function bookacti_add_item_data( $cart_item_data, $product_id, $variation_id ) {
		if( ! isset( $_POST[ 'bookacti_booking_id' ] ) && ! isset( $_POST[ 'bookacti_booking_group_id' ] ) ) { return $cart_item_data; }
			
		$new_value = array();

		// Single event
		if( isset( $_POST[ 'bookacti_booking_id' ] ) ) {
			$booking_id	= intval( $_POST[ 'bookacti_booking_id' ] );
			$event = bookacti_get_booking_event_data( $booking_id );
			$new_value[ '_bookacti_options' ][ 'bookacti_booking_id' ]		= $booking_id;
			$new_value[ '_bookacti_options' ][ 'bookacti_booked_events' ]	= json_encode( array( $event ) );

		// Group of events
		} else {
			$booking_group_id = intval( $_POST[ 'bookacti_booking_group_id' ] );
			$events = bookacti_get_booking_group_events_data( $booking_group_id );
			$new_value[ '_bookacti_options' ][ 'bookacti_booking_group_id' ]= $booking_group_id;
			$new_value[ '_bookacti_options' ][ 'bookacti_booked_events' ]	= json_encode( $events );
		}
		
		return array_merge( $cart_item_data, $new_value );
	}
	add_filter( 'woocommerce_add_cart_item_data', 'bookacti_add_item_data', 10, 3 );

	
	/**
	 * Attaches cart item data to the item
	 * @param array $item
	 * @param array $values
	 * @param string $key
	 * @return array
	 */
	function bookacti_get_cart_items_from_session( $item, $values, $key ) {
		if ( array_key_exists( '_bookacti_options', $values ) ) {
			$item[ '_bookacti_options' ] = $values[ '_bookacti_options' ];
		}
		return $item;
	}
	add_filter( 'woocommerce_get_cart_item_from_session', 'bookacti_get_cart_items_from_session', 10, 3 );
	
	
	/**
	 * Add the information as meta data so that it can be seen as part of the order 
	 * (to hide any meta data from the customer just start it with an underscore)
	 * To be used before WC 3.0 only, on woocommerce_add_order_item_meta hook
	 * 
	 * @version 1.5.4
	 * @param int $item_id
	 * @param array $values
	 */
	function bookacti_add_values_to_order_item_meta( $item_id, $values ) {
		if( ! array_key_exists( '_bookacti_options', $values ) ) { return; }
			
		// Single event data
		if( isset( $values['_bookacti_options']['bookacti_booking_id'] ) ) {

			$state = bookacti_get_booking_state( $values['_bookacti_options']['bookacti_booking_id'] );
			wc_add_order_item_meta( $item_id, 'bookacti_booking_id', intval( $values['_bookacti_options']['bookacti_booking_id'] ) );

		// Group of events data
		} else if( isset( $values['_bookacti_options']['bookacti_booking_group_id'] ) ) {

			$state	= bookacti_get_booking_group_state( $values['_bookacti_options']['bookacti_booking_group_id'] );
			wc_add_order_item_meta( $item_id, 'bookacti_booking_group_id', intval( $values['_bookacti_options']['bookacti_booking_group_id'] ) );
		}

		// Common data
		wc_add_order_item_meta( $item_id, 'bookacti_booked_events', $values['_bookacti_options']['bookacti_booked_events'] );
		wc_add_order_item_meta( $item_id, 'bookacti_state', sanitize_title_with_dashes( $state ) );
	}
	
	/**
	 * Add the information as meta data so that it can be seen as part of the order 
	 * (to hide any meta data from the customer just start it with an underscore)
	 * To be used since WC 3.0 instead of bookacti_add_values_to_order_item_meta, on woocommerce_checkout_create_order_line_item hook
	 * 
	 * @since 1.1.0
	 * @version 1.4.3
	 * 
	 * @param WC_Order_Item_Product $item
	 * @param string $cart_item_key
	 * @param array $values
	 * @param WC_Order $order
	 */
	function bookacti_save_order_item_metadata( $item, $cart_item_key, $values, $order ) {
		
		// Do not process non-booking metadata
		if( ! array_key_exists( '_bookacti_options', $values ) ) { return; }
		
		// Single event data
		if( isset( $values['_bookacti_options']['bookacti_booking_id'] ) ) {

			$state = bookacti_get_booking_state( $values['_bookacti_options']['bookacti_booking_id'] );
			$item->add_meta_data( 'bookacti_booking_id', intval( $values['_bookacti_options']['bookacti_booking_id'] ), true );
		
		// Group of events data
		} else if( isset( $values['_bookacti_options']['bookacti_booking_group_id'] ) ) {

			$state	= bookacti_get_booking_group_state( $values['_bookacti_options']['bookacti_booking_group_id'] );
			$item->add_meta_data( 'bookacti_booking_group_id', intval( $values['_bookacti_options']['bookacti_booking_group_id'] ), true );
		}

		// Common data
		$item->add_meta_data( 'bookacti_booked_events', $values['_bookacti_options']['bookacti_booked_events'], true );
		$item->add_meta_data( 'bookacti_state', sanitize_title_with_dashes( $state ), true );
	}
	

	/**
	 * Validate add to cart form and temporarily book the event
	 * @version 1.5.4
	 * @global WooCommerce $woocommerce
	 * @param boolean $true
	 * @param int $product_id
	 * @param int $quantity
	 * @return boolean
	 */
	function bookacti_validate_add_to_cart_and_book_temporarily( $true, $product_id, $quantity ) {
		if( ! $true ) { return $true; }
		
		if( isset( $_POST[ 'variation_id' ] ) ) {
			$is_activity = bookacti_product_is_activity( intval( $_POST[ 'variation_id' ] ) );
		} else {
			$is_activity = bookacti_product_is_activity( $product_id );
		}

		if( ! $is_activity ) { return $true; }

		// Check if a group id or an event id + start + end are set
		if( ( empty( $_POST[ 'bookacti_event_id' ] ) && empty( $_POST[ 'bookacti_group_id' ] ) )
			|| ( isset( $_POST[ 'bookacti_group_id' ] ) && ! is_numeric( $_POST[ 'bookacti_group_id' ] ) && $_POST[ 'bookacti_group_id' ] !== 'single' )
			|| ( $_POST[ 'bookacti_group_id' ] === 'single'
				&& (empty( $_POST[ 'bookacti_event_id' ] )
				||	empty( $_POST[ 'bookacti_event_start' ] ) 
				||	empty( $_POST[ 'bookacti_event_end' ] ) ) ) ) {
			wc_add_notice(  __( 'You haven\'t picked any event. Please pick an event first.', BOOKACTI_PLUGIN_NAME ), 'error' ); 
			return false;
		}

		global $woocommerce;
		$user_id = $woocommerce->session->get_customer_id();

		if( is_user_logged_in() ) { $user_id = get_current_user_id(); }
		
		// Get product form ID
		$variation_id = isset( $_POST[ 'variation_id' ] ) ? intval( $_POST[ 'variation_id' ] ) : 0;
		if( $variation_id ) {
			$form_id = get_post_meta( $variation_id, 'bookacti_variable_form', true );
		} else {
			$form_id = get_post_meta( $product_id, '_bookacti_form', true );
		}
		
		// Sanitize the variables
		$form_id		= is_numeric( $form_id ) ? intval( $form_id ) : 0;
		$group_id		= is_numeric( $_POST[ 'bookacti_group_id' ] ) ? intval( $_POST[ 'bookacti_group_id' ] ) : 'single';
		$event_id		= intval( $_POST[ 'bookacti_event_id' ] );
		$event_start	= bookacti_sanitize_datetime( $_POST[ 'bookacti_event_start' ] );
		$event_end		= bookacti_sanitize_datetime( $_POST[ 'bookacti_event_end' ] );

		// Check if data are correct before booking
		$response = bookacti_validate_booking_form( $group_id, $event_id, $event_start, $event_end, $quantity, $form_id );

		// Display error message
		if( $response[ 'status' ] !== 'success' ) {
			wc_add_notice( $response[ 'message' ], 'error' );
			return false;
		}
			
		// Book a single event temporarily
		if( $group_id === 'single' ) {
			
			// Book temporarily the event
			$response = bookacti_add_booking_to_cart( $product_id, $variation_id, $user_id, $event_id, $event_start, $event_end, $quantity, $form_id );

			// If the event is booked, add the booking ID to the corresponding hidden field
			if( $response[ 'status' ] === 'success' ) {
				$_POST[ 'bookacti_booking_id' ] = intval( $response[ 'id' ] );
				return true;
			}

		// Book a groups of events temporarily
		} else if( is_numeric( $group_id ) ) {

			// Book temporarily the group of event
			$response = bookacti_add_booking_group_to_cart( $product_id, $variation_id, $user_id, $group_id, $quantity, $form_id );

			// If the events are booked, add the booking group ID to the corresponding hidden field
			if( $response[ 'status' ] === 'success' ) {
				$_POST[ 'bookacti_booking_group_id' ] = intval( $response[ 'id' ] );
				return true;
			}
		}

		// Display error message
		if( isset( $response[ 'message' ] ) && $response[ 'message' ] ) { 
			wc_add_notice( $response[ 'message' ], 'error' ); 
		}

		// Return false at this point
		return false;
	}
	add_filter( 'woocommerce_add_to_cart_validation', 'bookacti_validate_add_to_cart_and_book_temporarily', 1000, 3 );
	
	
	/**
	 * If an activity is added to cart with the same booking data (same product, same variation, same booking) as an existing cart item
	 * Merge the old cart items to the new one
	 * @since 1.5.4
	 * @version 1.5.7
	 * @global WooCommerce $woocommerce
	 * @param array $cart_item_data
	 * @param string $cart_item_key
	 * @return array
	 */
	function bookacti_merge_cart_items_with_same_booking_data( $cart_item_data, $cart_item_key ) {
		if( empty( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) && empty( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) ) { return $cart_item_data; }
		
		$product_id		= $cart_item_data[ 'product_id' ];
		$variation_id	= $cart_item_data[ 'variation_id' ];
		$quantity		= $cart_item_data[ 'quantity' ];
		$new_quantity	= $quantity;
		$old_cart_items	= array();
		
		global $woocommerce;
		$cart_contents = $woocommerce->cart->get_cart();
		foreach( $cart_contents as $the_key => $the_item ) {
			// Same product
			if( $product_id !== $the_item[ 'product_id' ] ) { continue; }
			// Same variation
			if( ( empty( $variation_id ) && ! empty( $the_item[ 'variation_id' ] ) )
			||  ( ! empty( $variation_id ) && ( empty( $the_item[ 'variation_id' ] ) || $variation_id !== $the_item[ 'variation_id' ] ) ) ) { continue; }
			// Same booking
			if( isset( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) ) {
				if( empty( $the_item[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) || $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_id' ] !== $the_item[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) { continue; }
			} else if( isset( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) ) {
				if( empty( $the_item[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) || $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] !== $the_item[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) { continue; }
			}
			// Same Third-party data
			if( ! apply_filters( 'bookacti_merge_cart_item', true, $the_item, $product_id, $variation_id, $quantity ) ) { continue; }
			
			$old_cart_items[ $the_key ] = $the_item;
			do_action( 'bookacti_merge_cart_item_before', $the_item, $cart_item_data );
			
			// Add the quantity of the old cart item to the new one
			$new_quantity += $the_item[ 'quantity' ];
			
			// Remove the old cart item
			$woocommerce->cart->set_quantity( $the_key, 0, false );
		}
		
		// Set the new cart item quantity if it has merged with other cart items
		if( $new_quantity !== $quantity ) {
			$cart_item_data[ 'quantity' ] = $new_quantity;
			$cart_item_data = apply_filters( 'bookacti_cart_item_merged', $cart_item_data, $old_cart_items );
		}
		
		return $cart_item_data;
	}
	add_filter( 'woocommerce_add_cart_item', 'bookacti_merge_cart_items_with_same_booking_data', 15, 2 );
	
	
	/**
	 * Set the timeout for a product added to cart
	 * @version 1.5.2
	 * @global WooCommerce $woocommerce
	 * @param string $cart_item_key
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @param array $variation
	 * @param array $cart_item_data
	 */
	function bookacti_set_timeout_to_cart_item( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		
		if( $variation_id !== 0 ) {
			$is_activity = bookacti_product_is_activity( $variation_id );
		} else {
			$is_activity = bookacti_product_is_activity( $product_id );
		}
		
		if( ! $is_activity ) { return; }
			
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );

		if( ! $is_expiration_active ) { return; }
		
		// If all cart item expire at once, set cart expiration date
		$is_per_product_expiration = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_per_product' );
		
		if( $is_per_product_expiration ) { return; }
		
		global $woocommerce;
		$reset_timeout_on_change	= bookacti_get_setting_value( 'bookacti_cart_settings', 'reset_cart_timeout_on_change' );
		$timeout					= bookacti_get_setting_value( 'bookacti_cart_settings', 'cart_timeout' );
		$cart_expiration_date		= bookacti_get_cart_timeout();
		$expiration_date			= date( 'c', strtotime( '+' . $timeout . ' minutes' ) );
		
		// Reset cart timeout and its items timeout
		if(	$reset_timeout_on_change 
		||	is_null ( $cart_expiration_date ) 
		||	strtotime( $cart_expiration_date ) <= time()
		||  $woocommerce->cart->get_cart_contents_count() === $quantity ) {

			// Reset global cart timeout
			bookacti_set_cart_timeout( $expiration_date );

			// If there are others items in cart, we need to change their expiration dates
			if( $reset_timeout_on_change ) {
				bookacti_reset_cart_expiration_dates( $expiration_date );
			}
		}
	}
	add_action( 'woocommerce_add_to_cart', 'bookacti_set_timeout_to_cart_item', 30, 6 ); 
	
	
	/**
	 * Load filters depending on WC version
	 * Used for WOOCOMMERCE 3.0.0 backward compatibility
	 * 
	 * @since 1.0.4
	 * @version 1.1.0
	 */
	function bookacti_load_filters_with_backward_compatibility() {
		if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
			add_filter( 'woocommerce_get_stock_html', 'bookacti_dont_display_instock_in_variation', 10, 2 );
			add_filter( 'wc_add_to_cart_message_html', 'bookacti_add_to_cart_message_html', 10, 2 );
			add_action( 'woocommerce_checkout_create_order_line_item', 'bookacti_save_order_item_metadata', 10, 4 );
		} else {
			add_filter( 'woocommerce_stock_html', 'bookacti_deprecated_dont_display_instock_in_variation', 10, 3 );
			add_filter( 'wc_add_to_cart_message', 'bookacti_deprecated_add_to_cart_message_html', 10, 2 );
			add_action( 'woocommerce_add_order_item_meta', 'bookacti_add_values_to_order_item_meta', 10, 2 );
		}
	}
	add_action( 'woocommerce_loaded', 'bookacti_load_filters_with_backward_compatibility' );
	
	
	/**
	 * Notice the user that his activity has been reserved and will expire, along with the add to cart confirmation
	 * @since 1.0.4
	 * @version 1.5.2
	 * @param string $message
	 * @param array $products
	 * @return string
	 */
	function bookacti_add_to_cart_message_html( $message, $products ) {
		
		// If no activity has been added to cart, return the default message
		if( ! isset( $_POST[ 'bookacti_booking_id' ] ) 
		&&  ! isset( $_POST[ 'bookacti_booking_group_id' ] ) ) {
			return $message;
		}
		
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );

		if( ! $is_expiration_active ) { return $message; }
		
		// Check if there is at least one activity 
		$total_added_qty	= 0;
		$is_activity		= false;		
		foreach ( $products as $product_id => $qty ) {
			// Totalize added qty 
			$total_added_qty += $qty;

			if( $is_activity ) { continue; }
			
			$is_activity = bookacti_product_is_activity( $product_id );
		}

		if( ! $is_activity ) { return $message; }

		$temporary_book_message = bookacti_get_message( 'temporary_booking_success' );

		// If no message, return WC message only
		if( ! $temporary_book_message ) { return $message; }

		// If the message has no countdown, return the basic messages
		if( strpos( $temporary_book_message, '{time}' ) === false ) {
			return $message . '<br/>' . $temporary_book_message;
		}
		
		// Single event
		if( isset( $_POST[ 'bookacti_booking_id' ] ) ) {
			$booking_id		= intval( $_POST[ 'bookacti_booking_id' ] );
			$booking_type	= 'single';
		// Group of events
		} else if( isset( $_POST[ 'bookacti_booking_group_id' ] ) ) {
			$booking_id		= intval( $_POST[ 'bookacti_booking_group_id' ] );
			$booking_type	= 'group';
		}
		
		$expiration_date	= bookacti_get_new_booking_expiration_date( $booking_id, $booking_type, $total_added_qty );
		$remaining_time		= bookacti_get_formatted_time_before_expiration( $expiration_date );
		
		$message .= '<br/>' . str_replace( '{time}', $remaining_time, $temporary_book_message );
		
		return $message;
	}
	
	
	/**
	 * Notice the user that an activity has been reserved and will expire, along with the add to cart confirmation
	 *
	 * Only use it for WOOCOMMERCE 3.0.0 backward compatibility 
	 * 
	 * @since 1.0.4
	 * 
	 * @param string $message
	 * @param int $product_id
	 * @return string
	 */
	function bookacti_deprecated_add_to_cart_message_html( $message, $product_id ) {
		$products = array( $product_id => 1 );
		return bookacti_add_to_cart_message_html( $message, $products );
	}
	
	
	/**
	 * Do not display "In stock" for activities in variable product pages
	 * 
	 * Only use it for WOOCOMMERCE 3.0.0 backward compatibility 
	 * 
	 * @since 1.0.4
	 * 
	 * @param string $availability_html
	 * @param string $availability
	 * @param WC_Product $variation
	 * @return string
	 */
	function bookacti_deprecated_dont_display_instock_in_variation( $availability_html, $availability, $variation ) {
		if( $variation->stock_status === 'instock' ) {
			$is_activity = get_post_meta( $variation->variation_id, 'bookacti_variable_is_activity', true ) === 'yes';
			if( $is_activity ) {
				$availability_html = '';
			}
		}
		return $availability_html;
	}
	
	
	/**
	 * Do not display "In stock" for activities in variable product pages
	 *
	 * @since 1.0.4
	 * 
	 * @param string $availability_html
	 * @param WC_Product $variation
	 * @return string
	 */
	function bookacti_dont_display_instock_in_variation( $availability_html, $variation ) {
		if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
			if( $variation->get_stock_status() === 'instock' ) {
				$is_activity = get_post_meta( $variation->get_id(), 'bookacti_variable_is_activity', true ) === 'yes';
				if( $is_activity ) {
					$availability_html = '';
				}
			}
			return $availability_html;
		} else {
			return bookacti_deprecated_dont_display_instock_in_variation( $availability_html, null, $variation );
		}
	}
			

	
// CART & CHECKOUT

	/**
	 * Add the timeout to cart and checkout
	 * 
	 * @since 1.0.0
	 * @version 1.1.0
	 * 
	 * @global WooCommerce $woocommerce
	 */
	function bookacti_add_timeout_to_cart() { 
		
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );

		if( $is_expiration_active ) {
		
			$is_per_product_expiration = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_per_product' );

			if( ! $is_per_product_expiration ) {
				
				//Check if cart contains at least one item with the 'in_cart' state
				$cart_contains_expirables_items = false;
				
				global $woocommerce;
				$cart_contents = $woocommerce->cart->get_cart();
				if( ! empty( $cart_contents ) ) {

					$cart_keys = array_keys( $cart_contents );
					
					foreach ( $cart_keys as $key ) {
						// Single event
						if( isset( $cart_contents[$key]['_bookacti_options'] ) && isset( $cart_contents[$key]['_bookacti_options']['bookacti_booking_id'] ) ) {
							$booking_id = $cart_contents[$key]['_bookacti_options']['bookacti_booking_id'];
							if( ! is_null( $booking_id ) ) {

								$is_in_cart_state = bookacti_get_booking_state( $booking_id ) === 'in_cart';

								if( $is_in_cart_state ) {
									$cart_contains_expirables_items = true;
									break;
								}
							}
						
						// Group of events
						} else if( isset( $cart_contents[$key]['_bookacti_options'] ) && isset( $cart_contents[$key]['_bookacti_options']['bookacti_booking_group_id'] ) ) {
							$booking_group_id = $cart_contents[$key]['_bookacti_options']['bookacti_booking_group_id'];
							if( ! is_null( $booking_group_id ) ) {

								$is_in_cart_state = bookacti_get_booking_group_state( $booking_group_id ) === 'in_cart';

								if( $is_in_cart_state ) {
									$cart_contains_expirables_items = true;
									break;
								}
							}
						}
					}
				}
				
				if( $cart_contains_expirables_items ) {
					
					$expiration_date = bookacti_get_cart_timeout();
					
					if( strtotime( $expiration_date ) > time() ) {

						$timeout = '<div class="bookacti-cart-expiration-container woocommerce-info">' . bookacti_get_message( 'cart_countdown' ) . '</div>';
						$timeout = str_replace( '{countdown}', '<span class="bookacti-countdown bookacti-cart-expiration" data-expiration-date="' . esc_attr( $expiration_date ) . '" ></span>', $timeout );
						
						echo $timeout;
					}
				} else {
					bookacti_set_cart_timeout( null );
				}
			}
		}
	}
	add_action( 'woocommerce_before_cart', 'bookacti_add_timeout_to_cart', 10, 0 );
	add_action( 'woocommerce_checkout_before_order_review', 'bookacti_add_timeout_to_cart', 10, 0 );

	
	/**
	 * Add the timeout to each cart item
	 * 
	 * @since 1.0.0
	 * @ersion 1.1.0
	 * 
	 * @global WooCommerce $woocommerce
	 * @param string $sprintf
	 * @param string $cart_item_key
	 * @return string
	 */
	function bookacti_add_timeout_to_cart_item( $sprintf, $cart_item_key ) { 
		
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );
		
		if( $is_expiration_active ) {
		
			global $woocommerce;
			$item = $woocommerce->cart->get_cart_item( $cart_item_key );
			
			if( isset( $item['_bookacti_options'] ) 
			&&  (  ( isset( $item['_bookacti_options']['bookacti_booking_id'] )			&& ! empty( $item['_bookacti_options']['bookacti_booking_id'] ) )
				|| ( isset( $item['_bookacti_options']['bookacti_booking_group_id'] )	&& ! empty( $item['_bookacti_options']['bookacti_booking_group_id'] ) )
				) 
			  ) {
					
					$timeout = bookacti_get_cart_item_timeout( $cart_item_key );
					
					$base = "<div class='bookacti-remove-cart-item-container'>" . $sprintf . "</div>";
					return $base . $timeout; 
			}
		}
		
		return $sprintf;
	}
	add_filter( 'woocommerce_cart_item_remove_link', 'bookacti_add_timeout_to_cart_item', 10, 2 );
	
	
	/**
	 * Delete cart items if they are expired (trigger on cart, on checkout, on mini-cart)
	 * @version 1.1.0
	 * @global WooCommerce $woocommerce
	 */
	function bookacti_remove_expired_product_from_cart() {
		
		// Return if not frontend
		if( is_admin() ) { return; }
		
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );
		
		// Return if expiration is not active
		if( ! $is_expiration_active ) { return; }

		global $woocommerce;
		
		// Return if woocommerce is not instanciated
		if( empty( $woocommerce ) ) { return; }
		
		// Return if cart is null
		if( empty( $woocommerce->cart ) ) { return; }
		
		$cart_contents = $woocommerce->cart->get_cart();
		
		// Return if cart is already empty
		if( empty( $cart_contents ) ) { return; }


		// Check if each cart item has expired, and if so, reduce its quantity to 0 (delete it)
		$nb_deleted_cart_item = 0;
		$cart_keys = array_keys( $cart_contents );
		foreach ( $cart_keys as $key ) {

			if( isset( $cart_contents[$key]['_bookacti_options'] ) ) {
				// Single event
				if( isset( $cart_contents[$key]['_bookacti_options']['bookacti_booking_id'] ) ) {
					$booking_id = $cart_contents[$key]['_bookacti_options']['bookacti_booking_id'];
					if( ! empty( $booking_id ) ) {
						// Check if the booking related to the cart item has expired
						$is_expired = bookacti_is_expired_booking( $booking_id );
					}

				// Group of events
				} else if( isset( $cart_contents[$key]['_bookacti_options']['bookacti_booking_group_id'] ) ) {
					$booking_group_id = $cart_contents[$key]['_bookacti_options']['bookacti_booking_group_id'];
					if( ! empty( $booking_group_id ) ) {
						// Check if the bookings related to the cart item have expired
						$is_expired = bookacti_is_expired_booking_group( $booking_group_id );
					}
				}

				if( $is_expired ) {
					// Set quantity to zero to remove the product
					$is_deleted = $woocommerce->cart->set_quantity( $key , 0 , true );
					if( $is_deleted ) {
						$nb_deleted_cart_item++;
					}
				}
			}
		}

		// Display feedback to tell user that (part of) his cart has expired
		if( $nb_deleted_cart_item > 0 ) {
			/* translators: %d is a variable number of products */
			$message = sprintf( _n(	'%d product has expired and has been automatically removed from cart.', 
									'%d products have expired and have been automatically removed from cart.', 
									$nb_deleted_cart_item, 
									BOOKACTI_PLUGIN_NAME ), $nb_deleted_cart_item );

			// display feedback
			wc_add_notice( $message, 'error' );
		}
	}
	add_action( 'wp_loaded', 'bookacti_remove_expired_product_from_cart', 100, 0 );
	/**
	 * If you don't want to check cart expiration on each page load, please call at least these 4 actions instead:
	 * 
	 * add_action( 'woocommerce_check_cart_items', 'bookacti_remove_expired_product_from_cart', 10, 0 );
	 * add_action( 'woocommerce_review_order_before_cart_contents', 'bookacti_remove_expired_product_from_cart', 10, 0 );
	 * add_action( 'woocommerce_before_mini_cart', 'bookacti_remove_expired_product_from_cart', 10, 0 );
	 * add_action( 'woocommerce_checkout_process', 'bookacti_remove_expired_product_from_cart', 10, 0 );
	 */
	


	/**
	 * If quantity changes in cart, temporarily book the extra quantity if possible
	 * @version 1.5.8
	 * @global WooCommerce $woocommerce
	 * @param int $wc_stock_amount
	 * @param string $cart_item_key
	 * @return int
	 */
	function bookacti_update_quantity_in_cart( $wc_stock_amount, $cart_item_key ) { 
		
		global $woocommerce;
		$item = $woocommerce->cart->get_cart_item( $cart_item_key );
			
		// Item data
		$old_quantity	= $item[ 'quantity' ];
		$new_quantity	= $wc_stock_amount;
		$is_in_cart		= false;
		
		if( ! isset( $item['_bookacti_options'] ) || $new_quantity === $old_quantity ) { 
			return $wc_stock_amount;
		}
		
		// Single event
		if( ! empty( $item['_bookacti_options']['bookacti_booking_id'] ) ) {
			$booking_id = $item['_bookacti_options']['bookacti_booking_id'];
			$is_in_cart = bookacti_get_booking_state( $booking_id ) === 'in_cart';
			
			if( $is_in_cart ) {

				$response = bookacti_controller_update_booking_quantity( $booking_id, $new_quantity );
				
				while( $response[ 'status' ] === 'failed' && $response[ 'error' ] === 'qty_sup_to_avail' ) {
					$new_quantity = intval( $response[ 'availability' ] );
					$woocommerce->cart->set_quantity( $cart_item_key, $new_quantity, true );
					$response = bookacti_controller_update_booking_quantity( $booking_id, $new_quantity );
				}
				
				if( $response[ 'status' ] !== 'success' ) {
					$new_quantity = $old_quantity;
				}
			}

		// Group of events
		} else if( ! empty( $item['_bookacti_options']['bookacti_booking_group_id'] ) ) {
			$booking_group_id	= $item['_bookacti_options']['bookacti_booking_group_id'];
			$is_in_cart			= bookacti_get_booking_group_state( $booking_group_id ) === 'in_cart';
			
			if( $is_in_cart ) {
				$response = bookacti_controller_update_booking_group_quantity( $booking_group_id, $new_quantity );
				
				while( $response[ 'status' ] === 'failed' && $response[ 'error' ] === 'qty_sup_to_avail' ) {
					$new_quantity = intval( $response[ 'availability' ] );
					$woocommerce->cart->set_quantity( $cart_item_key, $new_quantity, true );
					$response = bookacti_controller_update_booking_group_quantity( $booking_group_id, $new_quantity );
				}
				
				if( $response[ 'status' ] !== 'success' ) {
					$new_quantity = $old_quantity;
				}
			}
			
		}
		
		// If the product is not "in_cart", it means that the order in already in process (maybe waiting for payment)
		if( ! $is_in_cart ) {
			$new_quantity = $old_quantity;
			wc_add_notice( __( "You can't update quantity since this product is temporarily booked on an order pending payment. Please, first cancel the order or remove this product from cart.", BOOKACTI_PLUGIN_NAME ), 'error' );
		}
		
		return $new_quantity;
	}
	add_filter( 'woocommerce_stock_amount_cart_item', 'bookacti_update_quantity_in_cart', 20, 2 ); 

	
	/**
	 * Remove in_cart bookings when cart items are removed from cart
	 * @version 1.5.8
	 * @global WooCommerce $woocommerce
	 * @param string $cart_item_key
	 * @param WC_Cart $cart
	 */
	function bookacti_remove_bookings_of_removed_cart_item( $cart_item_key, $cart ) { 
		global $woocommerce;
		$item = $woocommerce->cart->get_cart_item( $cart_item_key );
		bookacti_remove_cart_item_bookings( $item );
	}
	add_action( 'woocommerce_remove_cart_item', 'bookacti_remove_bookings_of_removed_cart_item', 10, 2 ); 
	
	
	/**
	 * Remove corrupted cart items bookings when they are removed from cart
	 * @since 1.5.8
	 * @global WooCommerce $woocommerce
	 * @param string $cart_item_key
	 * @param array $item
	 */
	function bookacti_remove_bookings_of_corrupted_cart_items( $cart_item_key, $item ) {
		bookacti_remove_cart_item_bookings( $item );
	}
	add_action( 'woocommerce_remove_cart_item_from_session', 'bookacti_remove_bookings_of_corrupted_cart_items', 10, 2 );

	
	/**
	 * Remove cart item bookings
	 * @since 1.5.8
	 * @param array $item
	 */
	function bookacti_remove_cart_item_bookings( $item ) {
		if( ! isset( $item['_bookacti_options'] ) ) { return; }
		
		// Single event
		if( ! empty( $item['_bookacti_options']['bookacti_booking_id'] ) ) {
			$booking_id = $item['_bookacti_options']['bookacti_booking_id'];
			$is_in_cart = bookacti_get_booking_state( $booking_id ) === 'in_cart';
			if( $is_in_cart ) {
				bookacti_update_booking_quantity( $booking_id, 0 );
			}

		// Group of events
		} else if( ! empty( $item['_bookacti_options']['bookacti_booking_group_id'] ) ) {
			$booking_group_id = $item['_bookacti_options']['bookacti_booking_group_id'];
			$is_in_cart = bookacti_get_booking_group_state( $booking_group_id ) === 'in_cart';
			if( $is_in_cart ) {
				bookacti_update_booking_group_quantity( $booking_group_id, 0 );
			}
		}
	}
	
	 
	/**
	 * Restore the booking if user change his mind after deleting one
	 * @version 1.5.8
	 * @global WooCommerce $woocommerce
	 * @param string $cart_item_key
	 * @param WC_Cart $cart
	 */
	function bookacti_restore_bookings_of_removed_cart_item( $cart_item_key, $cart ) { 
		global $woocommerce;
		$item = $woocommerce->cart->get_cart_item( $cart_item_key );
		
		if( isset( $item['_bookacti_options'] ) ) { 
			
			// Item data
			$item			= $woocommerce->cart->get_cart_item( $cart_item_key );
			$new_quantity	= $item[ 'quantity' ];
			
			// Single event
			if( ! empty( $item['_bookacti_options']['bookacti_booking_id'] ) ) {
				$booking_id = $item['_bookacti_options']['bookacti_booking_id'];
				$is_removed	= bookacti_get_booking_state( $booking_id ) === 'removed';

				if( $is_removed ) {
					$response = bookacti_controller_update_booking_quantity( $booking_id, $new_quantity );

					while( $response[ 'status' ] === 'failed' && $response[ 'error' ] === 'qty_sup_to_avail' ) {
						$new_quantity = intval( $response[ 'availability' ] );
						$woocommerce->cart->set_quantity( $cart_item_key, $new_quantity, true );
						$response = bookacti_controller_update_booking_quantity( $booking_id, $new_quantity );
					}
					
					$is_restored = apply_filters( 'bookacti_restore_bookings_of_restored_cart_item', in_array( $response[ 'status' ], array( 'success', 'no_change' ), true ), $cart_item_key, $new_quantity );
					
					if( ! $is_restored ) {
						$woocommerce->cart->set_quantity( $cart_item_key, 0, true );
						bookacti_controller_update_booking_quantity( $booking_id, 0 );
					} else {
						do_action( 'bookacti_bookings_of_restored_cart_item_restored', $cart_item_key, $new_quantity );
					}
				}
			
			// Group of events
			} else if( ! empty( $item['_bookacti_options']['bookacti_booking_group_id'] ) ) {
				$booking_group_id = $item['_bookacti_options']['bookacti_booking_group_id'];
				$is_removed	= bookacti_get_booking_group_state( $booking_group_id ) === 'removed';

				if( $is_removed ) {
					$response = bookacti_controller_update_booking_group_quantity( $booking_group_id, $new_quantity );
					
					while( $response[ 'status' ] === 'failed' && $response[ 'error' ] === 'qty_sup_to_avail' ) {
						$new_quantity = intval( $response[ 'availability' ] );
						$woocommerce->cart->set_quantity( $cart_item_key, $new_quantity, true );
						$response = bookacti_controller_update_booking_group_quantity( $booking_group_id, $new_quantity );
					}
					
					$is_restored = apply_filters( 'bookacti_restore_bookings_of_restored_cart_item', in_array( $response[ 'status' ], array( 'success', 'no_change' ), true ), $cart_item_key, $new_quantity );
					
					if( ! $is_restored ) {
						$woocommerce->cart->set_quantity( $cart_item_key, 0, true );
						bookacti_controller_update_booking_group_quantity( $booking_group_id, 0 );
					} else {
						do_action( 'bookacti_bookings_of_restored_cart_item_restored', $cart_item_key, $new_quantity );
					}
				}
			}
		}
	}
	add_action( 'woocommerce_cart_item_restored', 'bookacti_restore_bookings_of_removed_cart_item', 10, 2 );

	
	/**
	 * Tell how to display the custom metadata in cart and checkout
	 * @version 1.1.0
	 * @param array $item_data
	 * @param array $cart_item_data
	 * @return array
	 */
	function bookacti_get_item_data( $item_data, $cart_item_data ) {
		
		if( isset( $cart_item_data[ '_bookacti_options' ] ) ) {
			
			// Single event
			if( ( isset( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) && ! empty( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_id' ] ) )
			||  ( isset( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) && ! empty( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booking_group_id' ] ) ) ) {
				$events	= json_decode( $cart_item_data[ '_bookacti_options' ][ 'bookacti_booked_events' ] );
				$events_list = bookacti_get_formatted_booking_events_list( $events );
				
				$item_data[] = array( 
					'key' => _n( 'Booked event', 'Booked events', count( $events ), BOOKACTI_PLUGIN_NAME ), 
					'value' => $events_list );
			}
		}

		return $item_data;
	}
	add_filter( 'woocommerce_get_item_data', 'bookacti_get_item_data', 10, 2 );

	
	/**
	 * Format label of custom metadata in cart and checkout
	 * @version 1.1.0
	 * @param string $label
	 * @param string $name
	 * @return string
	 */
	function bookacti_define_label_of_item_data( $label, $name ) {
		
		if( $label === '_bookacti_booking_id' 
		||  $label === 'bookacti_booking_id' )			{ $label = __( 'Booking number', BOOKACTI_PLUGIN_NAME ); }
		if( $label === '_bookacti_booking_group_id' 
		||  $label === 'bookacti_booking_group_id' )	{ $label = __( 'Booking group number', BOOKACTI_PLUGIN_NAME ); }
		if( $label === 'bookacti_booked_events' )		{ $label = __( 'Booked events', BOOKACTI_PLUGIN_NAME ); }
		if( $label === 'bookacti_state' )				{ $label = __( 'Status', BOOKACTI_PLUGIN_NAME ); }
		if( $label === '_bookacti_refund_method' )		{ $label = __( 'Refund method', BOOKACTI_PLUGIN_NAME ); }
		if( $label === 'bookacti_refund_coupon' )		{ $label = __( 'Coupon code', BOOKACTI_PLUGIN_NAME ); }
		
		// Deprecated data
		if( $label === '_bookacti_event_id' )			{ $label = __( 'Event ID', BOOKACTI_PLUGIN_NAME ); }
		if( $label === 'bookacti_event_start' )			{ $label = __( 'Start', BOOKACTI_PLUGIN_NAME ); }
		if( $label === 'bookacti_event_end' )			{ $label = __( 'End', BOOKACTI_PLUGIN_NAME ); }
		
		return $label;
	}
	add_filter( 'woocommerce_attribute_label', 'bookacti_define_label_of_item_data', 10, 2 );
	
	
	/**
	 * Format value of custom metadata in order review
	 * @version 1.5.4
	 * @param array $formatted_meta
	 * @param WC_Order_Item_Meta $order_item_meta
	 * @return array
	 */
	function bookacti_format_order_meta_data( $formatted_meta, $order_item_meta ) {
		foreach( $formatted_meta as $key => $meta ) {
			if( substr( $meta[ 'key' ], 0, 9 ) !== 'bookacti_' ) { continue; }
			
			$value = $meta[ 'value' ];
			
			// Booking (group) state
			if( $meta[ 'key' ] === 'bookacti_state' ) {
				$formatted_meta[ $key ][ 'value' ] = bookacti_format_booking_state( $value );
			} 
			
			// Booked events
			else if( $meta[ 'key' ] === 'bookacti_booked_events' ) {
				$events	= json_decode( $value );
				$formatted_meta[ $key ][ 'value' ] = bookacti_get_formatted_booking_events_list( $events );
			} 
			
			// Deprecated data
			// Event start and end
			else if( $meta[ 'key' ] === 'bookacti_event_start' || $meta[ 'key' ] === 'bookacti_event_end' ) {
				$formatted_meta[ $key ][ 'value' ] = bookacti_format_datetime( $value );
			}
		}
		return $formatted_meta;
	}
	add_filter( 'woocommerce_order_items_meta_get_formatted', 'bookacti_format_order_meta_data', 10, 2 );
	
		
	/**
	 * Format order item mata values in order received page
	 * Must be used since WC 3.0.0
	 * @since 1.0.4
	 * @version 1.5.4
	 * @param string $html
	 * @param WC_Item $item
	 * @param array $args
	 * @return string
	 */
	function bookacti_format_order_item_meta( $formatted_meta, $item ) {
		foreach( $formatted_meta as $meta_id => $meta ) {
			if( substr( $meta->key, 0, 9 ) !== 'bookacti_' ) { continue; }
			
			// Format booking id
			if( $meta->key === 'bookacti_booking_id' || $meta->key === 'bookacti_booking_group_id' ) {
				$meta->display_value = intval( $meta->value );
			}
			
			// Format booking state
			else if( $meta->key === 'bookacti_state' ) {
				$meta->display_value = bookacti_format_booking_state( $meta->value );
			}
			
			// Format event list
			else if( $meta->key === 'bookacti_booked_events' ) {
				$events	= json_decode( $meta->value );
				$meta->display_key = _n( 'Booked event', 'Booked events', count( $events ), BOOKACTI_PLUGIN_NAME );
				$meta->display_value = bookacti_get_formatted_booking_events_list( $events );
			}
			
			// Deprecated data
			// Format datetime
			else if( $meta->key === 'bookacti_event_start' 
			||  $meta->key === 'bookacti_event_end' ) {
				$meta->display_value = bookacti_format_datetime( $meta->value );
			}
		}
		return $formatted_meta;
	}
	add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'bookacti_format_order_item_meta', 10, 2 );
	
	
	/**
	 * Add class to activity cart item to identify them
	 * 
	 * @param string $classes
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	function bookacti_add_class_to_activity_cart_item( $classes, $cart_item, $cart_item_key ) {
		
		if( isset( $cart_item['_bookacti_options'] ) ) {
			// Single booking
			if ( isset( $cart_item['_bookacti_options']['bookacti_booking_id'] ) && ! empty( $cart_item['_bookacti_options']['bookacti_booking_id'] ) ) {
				
				$classes .= ' bookacti-cart-item-activity bookacti-single-booking';
				
			// Group of bookings
			} else if( isset( $cart_item['_bookacti_options']['bookacti_booking_group_id'] ) && ! empty( $cart_item['_bookacti_options']['bookacti_booking_group_id'] ) ) {
				
				$classes .= ' bookacti-cart-item-activity bookacti-booking-group';
			}
		}
		
		return $classes;
	}
	add_filter( 'woocommerce_cart_item_class', 'bookacti_add_class_to_activity_cart_item', 10, 3 );
	
	
	/**
	 * Add class to activity order item to identify them on order received page
	 * @since 1.1.0
	 * @version 1.5.8
	 * @param string $classes
	 * @param WC_Order_Item $item
	 * @param WC_Order $order
	 * @return string
	 */
	function bookacti_add_class_to_activity_order_item( $classes, $item, $order ) {
		
		if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
			$booking_id = wc_get_order_item_meta( $item->get_id(), 'bookacti_booking_id', true );
			if( $booking_id ) {
				$classes .= ' bookacti-order-item-activity bookacti-single-booking';
			} else if( wc_get_order_item_meta( $item->get_id(), 'bookacti_booking_group_id', true ) ) {
				$classes .= ' bookacti-order-item-activity bookacti-booking-group';
			}
		} else {
			foreach ( $item[ 'item_meta' ] as $meta_key => $meta_array ) {
				// Single booking
				if( $meta_key === 'bookacti_booking_id' ) {
					$classes .= ' bookacti-order-item-activity bookacti-single-booking';

				// Group of bookings
				} else if( $meta_key === 'bookacti_booking_group_id' ) {
					$classes .= ' bookacti-order-item-activity bookacti-booking-group';
				}
			}
		}
		
		return $classes;
	}
	add_filter( 'woocommerce_order_item_class', 'bookacti_add_class_to_activity_order_item', 10, 3 );
	
	
	
// CHECKOUT
	
	/**
	 * Add the timeout to each cart item in the checkout review
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $product_name
	 * @param array $values
	 * @param string $cart_item_key
	 * @return string
	 */
	function bookacti_add_timeout_to_cart_item_in_checkout_review( $product_name, $values, $cart_item_key ) { 
		
		$is_expiration_active = bookacti_get_setting_value( 'bookacti_cart_settings', 'is_cart_expiration_active' );
		
		if( $is_expiration_active ) {
		
			if ( array_key_exists( '_bookacti_options', $values ) && is_checkout() ) {
				$timeout = bookacti_get_cart_item_timeout( $cart_item_key );
				return $timeout . $product_name;
			}
		}
		
		return $product_name;
	}
	add_filter( 'woocommerce_cart_item_name', 'bookacti_add_timeout_to_cart_item_in_checkout_review', 10, 3 );
	
	
	/**
	 * Check bookings availability before validating checkout 
	 * in case that "in_cart" state is not active
	 * 
	 * @since  1.3.0
	 * @version 1.5.0
	 * @param  array $posted_data An array of posted data.
	 * @param  WP_Error $errors
	 */
	function bookacti_availability_check_before_checkout( $posted_data, $errors = null ) {
		// Do not make this check if "in_cart" bookings are active, because they already hold their own booking quantity 
		if( in_array( 'in_cart', bookacti_get_active_booking_states(), true ) ) { return; }
		
		// Check availability
		global $woocommerce;
		$cart_contents = $woocommerce->cart->get_cart();
		
		foreach( $cart_contents as $cart_item ) {
			// Initialize on success for non-activity products
			$validated = array( 'status' => 'success' );
			
			// Get product form ID
			if( ! empty( $cart_item[ 'variation_id' ] ) ) {
				$form_id = get_post_meta( $cart_item[ 'variation_id' ], 'bookacti_variable_form', true );
			} else if( ! empty( $cart_item[ 'product_id' ] ) ) {
				$form_id = get_post_meta( $cart_item[ 'product_id' ], '_bookacti_form', true );
			}
			$form_id = is_numeric( $form_id ) ? intval( $form_id ) : 0;
			
			// Single event
			if( isset( $cart_item['_bookacti_options'] ) && isset( $cart_item['_bookacti_options']['bookacti_booking_id'] ) ) {
				$booking_id = $cart_item['_bookacti_options']['bookacti_booking_id'];
				if( ! is_null( $booking_id ) ) {
					$event		= json_decode( $cart_item['_bookacti_options']['bookacti_booked_events'] );
					$validated	= bookacti_validate_booking_form( 'single', $event[0]->event_id, $event[0]->event_start, $event[0]->event_end, $cart_item['quantity'], $form_id );
				}

			// Group of events
			} else if( isset( $cart_item['_bookacti_options'] ) && isset( $cart_item['_bookacti_options']['bookacti_booking_group_id'] ) ) {
				$booking_group_id = $cart_item['_bookacti_options']['bookacti_booking_group_id'];
				if( ! is_null( $booking_group_id ) ) {
					$event			= json_decode( $cart_item['_bookacti_options']['bookacti_booked_events'] );
					$booking_group	= bookacti_get_booking_group_by_id( $booking_group_id );
					$validated		= bookacti_validate_booking_form( $booking_group->event_group_id, $event[0]->event_id, $event[0]->event_start, $event[0]->event_end, $cart_item['quantity'], $form_id );
				}
			}
			
			// Display the error and stop checkout processing
			if( $validated[ 'status' ] !== 'success' && isset( $validated[ 'message' ] ) ) {
				if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
					if( ! isset( $validated[ 'error' ] ) ) { $validated[ 'error' ] = 'unknown_error'; }
					$errors->add( $validated[ 'error' ], $validated[ 'message' ] );

				// WOOCOMMERCE 3.0.0 backward compatibility
				} else {
					wc_add_notice( $validated[ 'message' ], 'error' );
				}
			}
		}
	}
	add_action( 'woocommerce_after_checkout_validation', 'bookacti_availability_check_before_checkout', 10, 2 );
	
	
	/**
	 * Change bookings state after user validate checkout
	 * 
	 * @since 1.2.2 (was bookacti_delay_expiration_date_for_payment before)
	 * @version 1.6.0
	 * @param int $order_id
	 * @param array $order_details
	 * @param WC_Order $order
	 */
	function bookacti_change_booking_state_after_checkout( $order_id, $order_details, $order = null ) {
		if( ! $order ) { $order = wc_get_order( $order_id ); }

		// Bind order and user id to the bookings and turn its state to 
		// 'pending' for payment
		// <user defined state> if no payment are required
		if( WC()->cart->needs_payment() ) { 
			$state = 'pending'; 
			$payment_status = 'owed';
			$alert_admin = false; 
		} else { 
			$state = bookacti_get_setting_value( 'bookacti_general_settings', 'default_booking_state' ); 
			$payment_status = bookacti_get_setting_value( 'bookacti_general_settings', 'default_payment_status' ); 
			$alert_admin = true;
		}
		
		bookacti_turn_order_bookings_to( $order, $state, $payment_status, $alert_admin, array( 'is_new_order' => true ) );
		
		// If the user has no account, bind the user data to the bookings
		$user_id = $order->get_user_id( 'edit' );
		if( $user_id && is_int( $user_id ) ) { return; }
		
		bookacti_save_order_data_as_booking_meta( $order );
	}
	add_action( 'woocommerce_checkout_order_processed', 'bookacti_change_booking_state_after_checkout', 10, 3 );
	
	
	
	
// BOOKINGS LIST
	
	/**
	 * Add a column called 'Price' to user bookings list
	 * @param array $columns
	 * @param int|string $user_id
	 * @return array
	 */
	function bookacti_add_woocommerce_price_column_to_bookings_list( $columns, $user_id ) {
		$columns[ 60 ] = array( 'id' => 'price', 'title' => __( 'Price', BOOKACTI_PLUGIN_NAME ) );
		return $columns;
	}
	add_filter( 'bookacti_user_bookings_list_columns_titles', 'bookacti_add_woocommerce_price_column_to_bookings_list', 10, 2 );
	
	
	/**
	 * Add values in bookings list (refund, price)
	 * @version 1.6.0
	 * @param array $columns_value
	 * @param object $booking
	 * @param int|string $user_id
	 * @return array
	 */
	function bookacti_add_woocommerce_prices_in_bookings_list( $columns_value, $booking, $user_id ) {
		$item = bookacti_get_order_item_by_booking_id( $booking->id );
		if( ! empty( $item ) ) {
			// Add Price column value
			// WOOCOMMERCE 3.0.0 backward compatibility
			if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
				$total	= $item->get_total();
				$tax	= $item->get_total_tax();
			} else {
				$total	= $item[ 'line_total' ];
				$tax	= $item[ 'line_tax' ];
			}
			
			$price_value = apply_filters( 'bookacti_user_bookings_list_order_item_price', wc_price( (float) $total + (float) $tax ), $item, $columns_value, $booking, $user_id );
			$columns_value[ 'price' ] = $price_value ? $price_value : '/';
			
			// Add refund coupon code in "Status" column
			if( $booking->state === 'refunded' ) {
				$coupon_code = '';
				
				// WOOCOMMERCE 3.0.0 backward compatibility
				if( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
					$meta_data = $item->get_formatted_meta_data();
					if( $meta_data ) {
						foreach( $meta_data as $meta_id => $meta ) {
							if( $meta->key === 'bookacti_refund_coupon' && ! empty( $meta->value ) ) {
								$coupon_code = $meta->value;
								break;
							}
						}
					}
				} else if( ! empty( $item[ 'item_meta' ] ) ) {
					foreach( $item[ 'item_meta' ] as $meta_key => $meta_value ) {
						if( $meta_key === 'bookacti_refund_coupon' && ! empty( $meta_value ) ) {
							$coupon_code = $meta_value;
							break;
						}
					}
				}
				
				if( $coupon_code ) {
					/* translators: %s is the coupon code used for the refund */
					$coupon_label = sprintf( esc_html__( 'Refunded with coupon %s', BOOKACTI_PLUGIN_NAME ), $coupon_code );
					$columns_value[ 'state' ] = '<span class="bookacti-booking-state bookacti-booking-state-bad bookacti-booking-state-refunded bookacti-converted-to-coupon bookacti-tip" data-booking-state="refunded">' . $coupon_label . '</span>';
				}
			}
		}

		return $columns_value;
	}
	add_filter( 'bookacti_user_bookings_list_columns_value', 'bookacti_add_woocommerce_prices_in_bookings_list', 20, 3 );