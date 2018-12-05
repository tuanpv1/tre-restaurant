<?php
// new post meta data callback
function lionthemes_post_meta_box_callback( $post ) {
	wp_nonce_field( 'lionthemes_meta_box', 'lionthemes_meta_box_nonce' );
	$value = get_post_meta( $post->ID, 'lionthemes_featured_post_value', true );
	echo '<label for="lionthemes_post_intro">';
	esc_html_e( 'This content will be used to replace the featured image, use shortcode here', 'lionthemes' );
	echo '</label><br />';
	wp_editor( $value, 'lionthemes_post_intro', $settings = array() );
}
// register new meta box
add_action( 'add_meta_boxes', 'lionthemes_add_post_meta_box' );
function lionthemes_add_post_meta_box(){
	$screens = array( 'post' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'lionthemes_post_intro_section',
			esc_html__( 'Post featured content', 'lionthemes' ),
			'lionthemes_post_meta_box_callback',
			$screen
		);
	}
	add_meta_box(
		'lionthemes_page_config_section',
		esc_html__( 'Page config', 'lionthemes' ),
		'lionthemes_page_meta_box_callback',
		'page',
		'normal',
		'high'
	);
}
// new page meta data callback
function lionthemes_page_meta_box_callback( $post ) {
	wp_nonce_field( 'lionthemes_meta_box', 'lionthemes_meta_box_nonce' );
	$header_val = get_post_meta( $post->ID, 'lionthemes_header_page', true );
	$layout_val = get_post_meta( $post->ID, 'lionthemes_layout_page', true );
	$logo_val = get_post_meta( $post->ID, 'lionthemes_logo_page', true );
	$banner_val = get_post_meta( $post->ID, 'lionthemes_page_banner', true );
	$footer_val = get_post_meta( $post->ID, 'lionthemes_footer_page', true );
	echo '<div class="bootstrap">';
		echo '<div class="option row">';
			echo '<div class="option_label col-md-3 col-sm-12"><label for="custom_header_option">' . esc_html__('Custom header:', 'lionthemes') . '</label></div>';
			echo '<div class="option_field col-md-9 col-sm-12"><select id="custom_header_option" name="lionthemes_header_page">';
			echo '<option value="">'. esc_html__('Inherit theme options', 'lionthemes') .'</option>';
			echo '<option value="first"'. (($header_val == 'first') ? ' selected="selected"' : '') .'>'. esc_html__('Header first (Default)', 'lionthemes') .'</option>';
			echo '<option value="second"'. (($header_val == 'second') ? ' selected="selected"' : '') .'>'. esc_html__('Header second', 'lionthemes') .'</option>';
			echo '<option value="third"'. (($header_val == 'third') ? ' selected="selected"' : '') .'>'. esc_html__('Header third', 'lionthemes') .'</option>';
			echo '<option value="fourth"'. (($header_val == 'fourth') ? ' selected="selected"' : '') .'>'. esc_html__('Header fourth', 'lionthemes') .'</option>';
			echo '</select></div>';
		echo '</div>';
		
		echo '<div class="option row">';
			echo '<div class="option_label col-md-3 col-sm-12"><label for="custom_footer_option">' . esc_html__('Custom footer:', 'lionthemes') . '</label></div>';
			echo '<div class="option_field col-md-9 col-sm-12"><select id="custom_footer_option" name="lionthemes_footer_page">';
			echo '<option value="">'. esc_html__('Inherit theme options', 'lionthemes') .'</option>';
			echo '<option value="first"'. (($footer_val == 'first') ? ' selected="selected"' : '') .'>'. esc_html__('Footer first', 'lionthemes') .'</option>';
			echo '<option value="second"'. (($footer_val == 'second') ? ' selected="selected"' : '') .'>'. esc_html__('Footer second', 'lionthemes') .'</option>';
			echo '<option value="third"'. (($footer_val == 'third') ? ' selected="selected"' : '') .'>'. esc_html__('Footer third', 'lionthemes') .'</option>';
			echo '<option value="four"'. (($footer_val == 'four') ? ' selected="selected"' : '') .'>'. esc_html__('Footer four', 'lionthemes') .'</option>';
			echo '<option value="fifth"'. (($footer_val == 'fifth') ? ' selected="selected"' : '') .'>'. esc_html__('Footer fifth', 'lionthemes') .'</option>';
			echo '<option value="sixth"'. (($footer_val == 'sixth') ? ' selected="selected"' : '') .'>'. esc_html__('Footer sixth', 'lionthemes') .'</option>';
			echo '</select></div>';
		echo '</div>';
		
		echo '<div class="option row">';
			echo '<div class="option_label col-md-3 col-sm-12"><label for="custom_layout_option">' . esc_html__('Custom layout:', 'lionthemes') . '</label></div>';
			echo '<div class="option_field col-md-9 col-sm-12"><select id="custom_layout_option" name="lionthemes_layout_page">';
			echo '<option value="">'. esc_html__('Inherit theme options', 'lionthemes') .'</option>';
			echo '<option value="full"'. (($layout_val == 'full') ? ' selected="selected"' : '') .'>'. esc_html__('Full (Default)', 'lionthemes') .'</option>';
			echo '<option value="box"'. (($layout_val == 'box') ? ' selected="selected"' : '') .'>'. esc_html__('Box', 'lionthemes') .'</option>';
			echo '</select></div>';
		echo '</div>';
		
		echo '<div class="option row">';
			echo '<div class="option_label col-md-3 col-sm-12"><label for="custom_logo_option">' . esc_html__('Custom logo:', 'lionthemes') . '</label></div>';
			echo '<div class="option_field col-md-9 col-sm-12"><input type="hidden" name="lionthemes_logo_page" id="custom_logo_option" value="'. esc_attr($logo_val) . '" />';
			echo '<div class="wp-media-buttons"><button id="lionthemes_media_button" class="button" type="button"/>'. esc_html__('Upload Logo', 'lionthemes') .'</button><button id="lionthemes_remove_media_button" class="button" type="button">'. esc_html__('Remove', 'lionthemes') .'</button></div>';
			echo '<div id="lionthemes_page_selected_media">'. (($logo_val) ? '<img width="150" src="'. esc_url($logo_val) .'" />':'') .'</div>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="option row">';
			echo '<div class="option_label col-md-3 col-sm-12"><label for="custom_page_banner">' . esc_html__('Custom banner:', 'lionthemes') . '</label></div>';
			echo '<div class="option_field col-md-9 col-sm-12"><input type="hidden" name="lionthemes_page_banner" id="custom_page_banner" value="'. esc_attr($banner_val) . '" />';
			echo '<div class="wp-media-buttons"><button id="lionthemes_media_banner_button" class="button" type="button"/>'. esc_html__('Upload Banner', 'lionthemes') .'</button><button id="lionthemes_remove_media_banner_button" class="button" type="button">'. esc_html__('Remove', 'lionthemes') .'</button></div>';
			echo '<div id="lionthemes_page_selected_media_banner">'. (($banner_val) ? '<img style="max-width: 400px" src="'. esc_url($banner_val) .'" />':'') .'</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
}
// save new meta box value
add_action( 'save_post', 'lionthemes_save_meta_box_data' );
function lionthemes_save_meta_box_data( $post_id ) {
	// Check if our nonce is set.
	if ( ! isset( $_POST['lionthemes_meta_box_nonce'] ) ) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['lionthemes_meta_box_nonce'], 'lionthemes_meta_box' ) ) {
		return;
	}
	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( isset( $_POST['lionthemes_post_intro'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_post_intro'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_featured_post_value', $my_data );
	}
	if ( isset( $_POST['lionthemes_header_page'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_header_page'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_header_page', $my_data );
	}
	if ( isset( $_POST['lionthemes_footer_page'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_footer_page'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_footer_page', $my_data );
	}
	if ( isset( $_POST['lionthemes_layout_page'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_layout_page'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_layout_page', $my_data );
	}
	if ( isset( $_POST['lionthemes_page_banner'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_page_banner'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_page_banner', $my_data );
	}
	if ( isset( $_POST['lionthemes_logo_page'] ) ) {
		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['lionthemes_logo_page'] );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'lionthemes_logo_page', $my_data );
	}
	
	return;
}


function lionthemes_custom_media_upload_field_js($hook) {
	global $post;
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	
	if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		if('page' === $post->post_type){
			$media_upload_js = '
				var file_frame;
				jQuery(document).on(\'click\', \'#lionthemes_remove_media_button\', function(e){
					e.preventDefault();
					jQuery(\'#custom_logo_option\').val(\'\');
					jQuery(\'#lionthemes_page_selected_media\').html(\'\');
				});
				jQuery(document).on(\'click\', \'#lionthemes_media_button\', function(e){
					
					if (file_frame){
						file_frame.open();
						return;
					}
					file_frame = wp.media.frames.file_frame = wp.media({
						title: jQuery(this).data(\'uploader_title\'),
						button: {
							text: jQuery(this).data(\'uploader_button_text\'),
						},
						multiple: false
					});
					file_frame.on(\'select\', function(){
						attachment = file_frame.state().get(\'selection\').first().toJSON();
						var url = attachment.url;
						var field = document.getElementById("custom_logo_option");
						field.value = url;
						jQuery(\'#lionthemes_page_selected_media\').html(\'<img width="150" src="\'+ url +\'" />\');
						file_frame.close();
					});
					file_frame.open();
					e.preventDefault();
				});
				jQuery(document).on(\'click\', \'#lionthemes_remove_media_banner_button\', function(e){
					e.preventDefault();
					jQuery(\'#custom_page_banner\').val(\'\');
					jQuery(\'#lionthemes_page_selected_media_banner\').html(\'\');
				});
				jQuery(document).on(\'click\', \'#lionthemes_media_banner_button\', function(e){
					
					if (file_frame){
						file_frame.open();
						return;
					}
					file_frame = wp.media.frames.file_frame = wp.media({
						title: jQuery(this).data(\'uploader_title\'),
						button: {
							text: jQuery(this).data(\'uploader_button_text\'),
						},
						multiple: false
					});
					file_frame.on(\'select\', function(){
						attachment = file_frame.state().get(\'selection\').first().toJSON();
						var url = attachment.url;
						var field = document.getElementById("custom_page_banner");
						field.value = url;
						jQuery(\'#lionthemes_page_selected_media_banner\').html(\'<img style="max-width: 400px" src="\'+ url +\'" />\');
						file_frame.close();
					});
					file_frame.open();
					e.preventDefault();
				});
			';
			wp_add_inline_script( 'media-upload', $media_upload_js );
		}
	}
}
add_action('admin_enqueue_scripts','lionthemes_custom_media_upload_field_js', 10, 1);