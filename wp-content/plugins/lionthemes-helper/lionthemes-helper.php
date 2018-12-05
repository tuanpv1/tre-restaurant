<?php
/**
 * Plugin Name: LionThemes Helper
 * Plugin URI: http://lion-themes.com/
 * Description: The helper plugin for LionThemes themes.
 * Version: 1.0.1
 * Author: LionThemes
 * Author URI: http://lion-themes.com/
 * Text Domain: lionthemes
 * License: GPL/GNU.
 *  Copyright 2016  LionThemes  (email : support@lion-themes.com)
*/

define('IMPORT_LOG_PATH', plugin_dir_path( __FILE__ ) . 'wbc_importer');

if ( file_exists( plugin_dir_path( __FILE__ ). 'inc/custom-fields.php' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'inc/custom-fields.php' );
}
if ( file_exists( plugin_dir_path( __FILE__ ). 'inc/widgets.php' ) ) {
	require_once( plugin_dir_path( __FILE__ ). 'inc/widgets.php' );
}
if( class_exists('Vc_Manager') && file_exists( plugin_dir_path( __FILE__ ). 'inc/composer-shortcodes.php' ) ){
	require_once( plugin_dir_path( __FILE__ ). 'inc/composer-shortcodes.php' );
}


// add placeholder for input social icons 
add_action("redux/field/outstock_opt/sortable/fieldset/after/outstock_opt", 'lionthemes_helper_redux_add_placeholder_sortable', 0);
function lionthemes_helper_redux_add_placeholder_sortable($data){
	$fieldset_id = $data['id'] . '-list';
	$base_name = 'outstock_opt['. $data['id'] .']';
	echo "<script type=\"text/javascript\">
			jQuery('#$fieldset_id li input[type=\"text\"]').each(function(){
				var my_name = jQuery(this).attr('name');
				placeholder = my_name.replace('$base_name', '').replace('[','').replace(']','');
				jQuery(this).attr('placeholder', placeholder);
				jQuery(this).next('span').attr('title', placeholder);
			});
		</script>";
}

//Redux wbc importer for import data one click.
function lionthemes_helper_redux_register_extension_loader($ReduxFramework) {
	
	if ( ! class_exists( 'ReduxFramework_extension_wbc_importer' ) ) {
		$class_file = plugin_dir_path( __FILE__ ) . 'wbc_importer/extension_wbc_importer.php';
		$class_file = apply_filters( 'redux/extension/' . $ReduxFramework->args['opt_name'] . '/wbc_importer', $class_file );
		if ( $class_file ) {
			require_once( $class_file );
		}
	}
	if ( ! isset( $ReduxFramework->extensions[ 'wbc_importer' ] ) ) {
		$ReduxFramework->extensions[ 'wbc_importer' ] = new ReduxFramework_extension_wbc_importer( $ReduxFramework );
	}
}
add_action("redux/extensions/outstock_opt/before", 'lionthemes_helper_redux_register_extension_loader', 0);

// Import slider, setup menu locations, setup home page
function lionthemes_helper_wbc_extended_example( $demo_active_import , $demo_directory_path ) {

	reset( $demo_active_import );
	$current_key = key( $demo_active_import );

	// Revolution Slider import all
	if ( class_exists( 'RevSlider' ) ) {
		$wbc_sliders_array = array(
			'Outstock' => array('home-1-slider.zip', 'home-2-slider.zip', 'home-4-slider.zip', 'home-6-slider.zip'),
		);

		if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_sliders_array ) ) {
			$wbc_slider_import = $wbc_sliders_array[$demo_active_import[$current_key]['directory']];
			foreach($wbc_slider_import as $file_backup){
				if ( file_exists( $demo_directory_path . $file_backup ) ) {
					$slider = new RevSlider();
					$slider->importSliderFromPost( true, true, $demo_directory_path . $file_backup );
				}
			}
		}
	}
	// menu localtion settings
	$wbc_menu_array = array( 'Outstock' );

	if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && in_array( $demo_active_import[$current_key]['directory'], $wbc_menu_array ) ) {
		$primary_menu = get_term_by( 'name', 'Main menu', 'nav_menu' );
		
		if ( isset( $primary_menu->term_id )) {
			set_theme_mod( 'nav_menu_locations', array(
					'primary' => $primary_menu->term_id,
					'mobilemenu'  => $primary_menu->term_id,
				)
			);
		}
	}
	
	// megamenu options
	global $mega_main_menu;
	
	$exported_file = $demo_directory_path . 'mega-main-menu-settings.json';
	
	if ( file_exists( $exported_file ) ) {
		$backup_file_content = file_get_contents ( $exported_file );
		
		if ( $backup_file_content !== false && ( $options_backup = json_decode( $backup_file_content, true ) ) ) {
			update_option( $mega_main_menu->constant[ 'MM_OPTIONS_NAME' ], $options_backup );
		}
	}

	// Home page setup default
	$wbc_home_pages = array(
		'Outstock' => 'Home page 1',
	);
	$wbc_blog_page = array(
		'Outstock' => 'Blog',
	);

	if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_home_pages ) ) {
		$page = get_page_by_title( $wbc_home_pages[$demo_active_import[$current_key]['directory']] );
		$blogpage = get_page_by_title( $wbc_blog_page[$demo_active_import[$current_key]['directory']] );
		if ( isset( $page->ID ) ) {
			update_option( 'page_on_front', $page->ID );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_for_posts', $blogpage->ID );
		}
	}
	update_option( 'yith_woocompare_compare_button_in_products_list', 'no' );
}
add_action( 'wbc_importer_after_content_import', 'lionthemes_helper_wbc_extended_example', 10, 2 );


//admin datepicker lib
add_action('admin_head', 'lionthemes_helper_datepicker_script');
function lionthemes_helper_datepicker_script(){
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
}

add_action("redux/outstock_opt/panel/after", 'lionthemes_helper_redux_after_panel_gender', 0);
function lionthemes_helper_redux_after_panel_gender(){
	echo "<script type=\"text/javascript\">
			jQuery(document).ready(function($){
				$('#new_pro_from').datepicker({
					dateFormat : 'yy-mm-dd'
				});
			});
		</script>";
}

//Less compiler
function compileLess($input, $output, $params){
    // input and output location
	$inputFile = get_template_directory().'/less/'.$input;
	$outputFile = get_template_directory().'/css/'.$output;
	if(!file_exists($inputFile)) return;
	// include Less Lib
	if(file_exists( plugin_dir_path( __FILE__ ) . 'less/lessc.inc.php' )){
		require_once( plugin_dir_path( __FILE__ ) . 'less/lessc.inc.php' );
		try{
			$less = new lessc;
			$less->setVariables($params);
			$less->setFormatter("compressed");
			$cache = $less->cachedCompile($inputFile);
			file_put_contents($outputFile, $cache["compiled"]);
			$last_updated = $cache["updated"];
			$cache = $less->cachedCompile($cache);
			if ($cache["updated"] > $last_updated) {
				file_put_contents($outputFile, $cache["compiled"]);
			}
		}catch(Exception $e){
			$error_message = $e->getMessage();
			echo $error_message;
		}
	}
	return;
}
$shortcodes = array(
	'brands.php',
	'blogposts.php',
	'products.php',
	'productscategory.php',
	'testimonials.php',
	'countdown.php',
	'featurecontent.php',
	'mailchimp.php',
);
//Shortcodes for Visual Composer
foreach($shortcodes as $shortcode){
	if ( file_exists( plugin_dir_path( __FILE__ ). 'shortcodes/' . $shortcode ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'shortcodes/' . $shortcode;
	}
}


// install table when active plugin
register_activation_hook( __FILE__, 'lionthemes_new_like_post_table' );
function lionthemes_new_like_post_table(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'lionthemes_user_like_ip';
	if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
		 //table not in database. Create new table
		 $charset_collate = $wpdb->get_charset_collate();
		 $sql = "CREATE TABLE `{$table_name}` (
			  `post_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
			  `user_ip` VARCHAR(100) NOT NULL DEFAULT '',
			  PRIMARY KEY (`post_id`,`user_ip`)
		 ) {$charset_collate}";
		 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		 dbDelta( $sql );
	}
}
// function display number like of posts.
function lionthemes_get_liked($postID){
	global $wpdb;
    $table_name = $wpdb->prefix . 'lionthemes_user_like_ip';
	if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
		lionthemes_new_like_post_table();
		return 0;
	}else{
		$safe_sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE post_id = %s", $postID);
		$results = $wpdb->get_var( $safe_sql );
		return $results;
	}
}

function lionthemes_make_id($length = 5){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

//ajax like count
add_action( 'wp_footer', 'lionthemes_add_js_like_post');
function lionthemes_add_js_like_post(){
	?>
    <script type="text/javascript">
    jQuery(document).on('click', 'a.lionthemes_like_post', function(e){
		var like_title;
		if(jQuery(this).hasClass('liked')){
			jQuery(this).removeClass('liked');
			like_title = jQuery(this).data('unliked_title');
		}else{
			jQuery(this).addClass('liked');
			like_title = jQuery(this).data('liked_title');
		}
        var post_id = jQuery(this).data("post_id");
		var me = jQuery(this);
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: 'action=lionthemes_update_like&post_id=' + post_id, 
			success: function(data){
				me.children('.number').text(data);
				me.parent('.likes-counter').attr('title', '').attr('data-original-title', like_title);
            }
        });
		e.preventDefault();
        return false;
    });
    </script>
<?php 
} 
add_action( 'wp_ajax_lionthemes_update_like', 'lionthemes_update_like' );
add_action( 'wp_ajax_nopriv_lionthemes_update_like', 'lionthemes_update_like' );
function lionthemes_get_the_user_ip(){
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function lionthemes_check_liked_post($postID){
	global $wpdb;
    $table_name = $wpdb->prefix . 'lionthemes_user_like_ip';
	if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
		lionthemes_new_like_post_table();
		return 0;
	}else{
		$user_ip = lionthemes_get_the_user_ip();
		$safe_sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE post_id = %s AND user_ip = %s", $postID, $user_ip);
		$results = $wpdb->get_var( $safe_sql );
		return $results;
	}
}

function lionthemes_update_like(){
	$count_key = 'post_like_count';
	if(empty($_POST['post_id'])){
	   die('0');
	}else{
		global $wpdb;
		$table_name = $wpdb->prefix . 'lionthemes_user_like_ip';
		$postID = intval($_POST['post_id']);
		$check = lionthemes_check_liked_post($postID);
		$ip = lionthemes_get_the_user_ip();
		$data = array('post_id' => $postID, 'user_ip' => $ip);
		if($check){
			//remove like record
			$wpdb->delete( $table_name, $data ); 
		}else{
			//add new like record
			$wpdb->insert( $table_name, $data );
		}
		echo lionthemes_get_liked($postID);
		die();
	}
}
add_action('lionthemes_like_button', 'lionthemes_like_button_html');
function lionthemes_like_button_html($id){
	$liked = lionthemes_check_liked_post($id); ?>
	<div class="likes-counter" title="<?php echo (!$liked) ?  esc_html__('Like this post', 'outstock') : esc_html__('Unlike this post', 'outstock'); ?>" data-toggle="tooltip">
		<a class="lionthemes_like_post<?php echo ($liked) ? ' liked':''; ?>" href="javascript:void(0)" data-post_id="<?php echo $id; ?>" data-liked_title="<?php echo esc_html__('Unlike this post', 'outstock') ?>" data-unliked_title="<?php echo esc_html__('Like this post', 'outstock') ?>">
			<i class="fa fa-heart"></i><span class="number"><?php echo lionthemes_get_liked($id); ?></span>
		</a>
	</div>
	<?php
}

// function display number view of posts.
function lionthemes_get_post_viewed($postID){
    $count_key = 'post_views_count';
	delete_post_meta($postID, 'post_like_count');
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return 0;
    }
    return $count;
}

add_action('lionthemes_view_count_button', 'lionthemes_view_count_button_html');
function lionthemes_view_count_button_html($id){
	?>
	<div class="post-views" title="<?php echo esc_html__('Total Views', 'outstock') ?>" data-toggle="tooltip">
		<i class="fa fa-eye"></i><?php echo lionthemes_get_post_viewed(get_the_ID()); ?>
	</div>
	<?php
}

add_action('lionthemes_track_view_count', 'lionthemes_set_post_view');
// function to count views.
function lionthemes_set_post_view($postID){
    $count_key = 'post_views_count';
    $count = (int)get_post_meta($postID, $count_key, true);
    if(!$count){
        $count = 1;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, $count);
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

// remove redux ads
add_action('admin_enqueue_scripts','lionthemes_remove_redux_ads', 10, 1);
function lionthemes_remove_redux_ads(){
	$remove_redux = 'jQuery(document).ready(function($){
						setTimeout(
							function(){
								$(".rAds, .redux-notice, .vc_license-activation-notice, #js_composer-update").remove();
								$("tr[data-slug=\"js_composer\"]").removeClass("update");
							}, 500);
					});';
	if ( ! wp_script_is( 'jquery', 'done' ) ) {
		wp_enqueue_script( 'jquery' );
	}
	wp_add_inline_script( 'jquery-migrate', $remove_redux );
}


add_action('lionthemes_quickview_after_product_info', 'lionthemes_product_sharing');
add_action( 'woocommerce_share', 'lionthemes_product_sharing', 40 );
//social share products
function lionthemes_product_sharing() {
	global $outstock_opt;
	if(isset($_POST['data'])) { // for the quickview
		$postid = intval( $_POST['data'] );
	} else {
		$postid = get_the_ID();
	}
	if(isset($outstock_opt['pro_social_share']) && is_array($outstock_opt['pro_social_share'])){
		$pro_social_share = array_filter($outstock_opt['pro_social_share']);
	}
	if(!empty($pro_social_share)){
		$share_url = get_permalink( $postid );

		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), 'large' );
		$postimg = $large_image_url[0];
		$posttitle = get_the_title( $postid );
		?>
		<div class="social-sharing">
			<div class="widget widget_socialsharing_widget">
				<h3 class="widget-title"><?php if(isset($outstock_opt['product_share_title'])) { echo esc_html($outstock_opt['product_share_title']); } else { esc_html_e('Share this product', 'outstock'); } ?></h3>
				<ul class="social-icons">
					<?php if(!empty($outstock_opt['pro_social_share']['facebook'])){ ?>
						<li><a class="facebook social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://www.facebook.com/sharer/sharer.php?u='.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Facebook', 'outstock'); ?>"><i class="fa fa-facebook"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['pro_social_share']['twitter'])){ ?>
						<li><a class="twitter social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://twitter.com/home?status='.$posttitle.'&nbsp;'.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Twitter', 'outstock'); ?>" ><i class="fa fa-twitter"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['pro_social_share']['pinterest'])){ ?>
						<li><a class="pinterest social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://pinterest.com/pin/create/button/?url='.$share_url.'&amp;media='.$postimg.'&amp;description='.$posttitle; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Pinterest', 'outstock'); ?>"><i class="fa fa-pinterest"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['pro_social_share']['gplus'])){ ?>
					<li><a class="gplus social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://plus.google.com/share?url='.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Google +', 'outstock'); ?>"><i class="fa fa-google-plus"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['pro_social_share']['linkedin'])){ ?>
						<li><a class="linkedin social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://www.linkedin.com/shareArticle?mini=true&amp;url='.$share_url.'&amp;title='.$posttitle; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('LinkedIn', 'outstock'); ?>"><i class="fa fa-linkedin"></i></a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
	}
}

add_action('lionthemes_end_single_post', 'lionthemes_blog_sharing');
//social share blog
function lionthemes_blog_sharing() {
	global $outstock_opt;
	
	if(isset($outstock_opt['post_social_share']) && is_array($outstock_opt['post_social_share'])){
		$post_social_share = array_filter($outstock_opt['post_social_share']);
	}
	if(empty($post_social_share)) {
		$postid = get_the_ID();
		
		$share_url = get_permalink( $postid );

		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), 'large' );
		$postimg = $large_image_url[0];
		$posttitle = get_the_title( $postid );
		?>
		<div class="social-sharing">
			<div class="widget widget_socialsharing_widget">
				<ul class="social-icons">
					<?php if(!empty($outstock_opt['post_social_share']['facebook'])){ ?>
					<li><a class="facebook social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://www.facebook.com/sharer/sharer.php?u='.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Facebook', 'outstock'); ?>"><i class="fa fa-facebook"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['post_social_share']['twitter'])){ ?>
					<li><a class="twitter social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://twitter.com/home?status='.$posttitle.'&nbsp;'.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Twitter', 'outstock'); ?>"><i class="fa fa-twitter"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['post_social_share']['pinterest'])){ ?>
					<li><a class="pinterest social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://pinterest.com/pin/create/button/?url='.$share_url.'&amp;media='.$postimg.'&amp;description='.$posttitle; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Pinterest', 'outstock'); ?>"><i class="fa fa-pinterest"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['post_social_share']['gplus'])){ ?>
					<li><a class="gplus social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://plus.google.com/share?url='.$share_url; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('Google +', 'outstock'); ?>"><i class="fa fa-google-plus"></i></a></li>
					<?php } ?>
					<?php if(!empty($outstock_opt['post_social_share']['linkedin'])){ ?>
					<li><a class="linkedin social-icon" href="javascript:void(0)" onclick="javascript:window.open('<?php echo 'https://www.linkedin.com/shareArticle?mini=true&amp;url='.$share_url.'&amp;title='.$posttitle; ?>', '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php echo esc_attr__('LinkedIn', 'outstock'); ?>"><i class="fa fa-linkedin"></i></a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
	}
}
add_action('lionthemes_page_banner', 'lionthemes_page_banner_html');
function lionthemes_page_banner_html(){
	$lionthemes_banner = '';
	global $wp_query, $page_id;
	if (!$page_id) $page_id = $wp_query->get_queried_object_id();
	
	if(get_post_meta( $page_id, 'lionthemes_page_banner', true )){
		$lionthemes_banner = get_post_meta( $page_id, 'lionthemes_page_banner', true );
	}
	$page_h = get_post_meta( $page_id, 'lionthemes_page_heading', true );
	$page_heading = ($page_h) ? $page_h : get_the_title($page_id);
	if($lionthemes_banner){
		echo '<div class="page-banner">
			<div class="image-wrap"><img src="' . esc_url($lionthemes_banner) . '" alt="" /></div>
			<div class="page-banner-content">
				<h1 class="entry-title">'. $page_heading .'</h1>';
				outstock_breadcrumb();
		echo '</div></div>';
	}else{
		echo '<div class="container"><div class="row"><div class="col-xs-12">';
		outstock_breadcrumb();
		echo '</div></div></div>';
	}
}


//popup onload home page
add_action( 'wp_footer', 'lionthemes_popup_onload');
function lionthemes_popup_onload(){
	global $outstock_opt;
	if(!empty($outstock_opt['enable_popup'])) {
		if (is_front_page() && (!empty($outstock_opt['popup_onload_form']) || !empty($outstock_opt['popup_onload_content']))) {
			$no_again = 0; 
			if(isset($_COOKIE['no_again'])) $no_again = $_COOKIE['no_again'];
			if(!$no_again){
		?>
			<div class="popup-content" id="popup_onload">
				<div class="overlay-bg-popup"></div>
				<div class="popup-content-wrapper">
					<a class="close-popup" href="javascript:void(0)"><i class="fa fa-times"></i></a>
					<div class="popup-container">
						<div class="row">
							<div class="">
								<?php if(!empty($outstock_opt['popup_onload_content'])){ ?>
								<div class="popup-content-text">
									<?php echo ''. $outstock_opt['popup_onload_content']; ?>
								</div>
								<?php } ?>
								<?php if(!empty($outstock_opt['popup_onload_form']) && shortcode_exists('mc4wp_form')){ ?>
								<div class="newletter-form">
									<?php echo do_shortcode( '[mc4wp_form id="'. intval($outstock_opt['popup_onload_form']) .'"]' ); ?>
								</div>
								<?php } ?>
								<label class="not-again"><input type="checkbox" value="1" name="not-again" /><span><?php echo esc_html__('Do not show this popup again', 'outstock'); ?></span></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } 
		}
	}
}