<?php
/**
* Theme stylesheet & javascript registration
*
* @package WordPress
* @subpackage Outstock_theme
* @since Outstock Themes 1.2
*/

//Outstock theme style and script 
function outstock_register_script()
{
	global $outstock_opt, $woocommerce;
	$default_font = "Poppins, Helvetica, sans-serif";
	$body_font = (!empty($outstock_opt['bodyfont']['font-family'])) ? $outstock_opt['bodyfont']['font-family'] : $default_font;
	$heading_font = (!empty($outstock_opt['headingfont']['font-family'])) ? $outstock_opt['headingfont']['font-family'] : $default_font;
	$menu_font = (!empty($outstock_opt['menufont']['font-family'])) ? $outstock_opt['menufont']['font-family'] : $default_font;
	$heading_font_weight = (!empty($outstock_opt['headingfont']['font-weight'])) ? $outstock_opt['headingfont']['font-weight'] : '700';
	
	$demos = array(
		2 => array(
			'header_bg' => '#FFF',
			'footer_newsletter_bg' => '#f5f5f5'
		),
		3 => array(
			'header_bg' => '#FFF',
			'footer_bg' => '#232323'
		),
		4 => array(
			'background_opt' => array('background-color' => '#f5f5f5 !important;'),
			'footer_bg' => '#f5f5f5',
			'header_bg' => '#f5f5f5',
			'footer_newsletter_bg' => '#FFF',
			'footertitle_color' => '#1f1f1f',
		),
		5 => array(
			'header_bg' => '#FFF',
			'footer_bg' => '#fff',
			'footer_newsletter_bg' => '#f5f5f5',
			'footertitle_color' => '#1f1f1f',
			'copyright_bg' => '#fff',
		),
		6 => array(
			'header_bg' => '#f5f5f5',
			'footer_bg' => '#232323',
			'copyright_bg' => '#fff',
			'background_opt' => array('background-color' => '#f5f5f5 !important;'),
		),
	);
	if(isset($_GET['demo']) && !empty($demos[intval($_GET['demo'])])){
		foreach($demos[intval($_GET['demo'])] as $property=>$value) {
			if(!is_array($value)){
				$outstock_opt[$property] = $value;
			}else{
				foreach($value as $key=>$val){
					$outstock_opt[$property][$key] = $val;
				}
			}
		}
	}
	$params = array(
		'heading_font'=> $heading_font,
		'heading_color'=> ((!empty($outstock_opt['headingfont']['color'])) ? $outstock_opt['headingfont']['color'] : '#201f1f'),
		'heading_font_weight'=> $heading_font_weight,
		'menu_font'=> $menu_font,
		'menu_font_size'=> ((!empty($outstock_opt['menufont']['font-size'])) ? $outstock_opt['menufont']['font-size'] : '14px'),
		'menu_font_weight'=> ((!empty($outstock_opt['menufont']['font-weight'])) ? $outstock_opt['menufont']['font-weight'] : '400'),
		'menu_text_color'=> ((!empty($outstock_opt['menufont']['color'])) ? $outstock_opt['menufont']['color'] : '#a3a3a3'),
		'sub_menu_bg'=> ((!empty($outstock_opt['sub_menu_bg'])) ? $outstock_opt['sub_menu_bg'] : '#FFFFFF'),
		'sub_menu_color'=> ((!empty($outstock_opt['sub_menu_color'])) ? $outstock_opt['sub_menu_color'] : '#7d7d7d'),
		'body_font'=> $body_font,
		'body_font_size'=> ((!empty($outstock_opt['bodyfont']['font-size'])) ? $outstock_opt['bodyfont']['font-size'] : '14px'),
		'text_color'=> ((!empty($outstock_opt['bodyfont']['color'])) ? $outstock_opt['bodyfont']['color'] : '#606060'),
		'primary_color' => (!empty($outstock_opt['primary_color']) ? $outstock_opt['primary_color'] : '#bd8348'),
		'sale_color' => ((!empty($outstock_opt['sale_color'])) ? $outstock_opt['sale_color'] : '#535353'),
		'saletext_color' => ((!empty($outstock_opt['saletext_color'])) ? $outstock_opt['saletext_color'] : '#ffffff'),
		'price_font' => (!empty($outstock_opt['pricefont']['font-family']) ? $outstock_opt['pricefont']['font-family'] : 'Rubik, sans-serif'),
		'price_color' => (!empty($outstock_opt['pricefont']['color']) ? $outstock_opt['pricefont']['color'] : '#23232c'),
		'rate_color' => ((!empty($outstock_opt['rate_color'])) ? $outstock_opt['rate_color'] : '#181818'),
		'page_width' => (!empty($outstock_opt['box_layout_width'])) ? $outstock_opt['box_layout_width'] . 'px' : '1270px',
		'body_bg_color' => ((!empty($outstock_opt['background_opt']['background-color'])) ? $outstock_opt['background_opt']['background-color'] : '#fff'),
		'popup_bg_color' => ((!empty($outstock_opt['background_popup']['background-color'])) ? $outstock_opt['background_popup']['background-color'] : '#fff'),
		'popup_bg_img' => ((!empty($outstock_opt['background_popup']['background-image'])) ? 'url("' . $outstock_opt['background_popup']['background-image'] . '")' : 'none'),
		'popup_bg_img_repeat' => ((!empty($outstock_opt['background_popup']['background-repeat'])) ? $outstock_opt['background_popup']['background-repeat'] : 'no-repeat'),
		'popup_bg_img_position' => ((!empty($outstock_opt['background_popup']['background-position'])) ? $outstock_opt['background_popup']['background-position'] : 'left top'),
		'popup_bg_img_size' => ((!empty($outstock_opt['background_popup']['background-size'])) ? $outstock_opt['background_popup']['background-size'] : 'auto'),
		'footer_bg' => ((!empty($outstock_opt['footer_bg'])) ? $outstock_opt['footer_bg'] : '#232323'),
		'copyright_bg' => ((!empty($outstock_opt['copyright_bg'])) ? $outstock_opt['copyright_bg'] : '#232323'),
		'footertext_color' => ((!empty($outstock_opt['footertext_color'])) ? $outstock_opt['footertext_color'] : '#8e8e8e'),
		'copyrighttext_color' => ((!empty($outstock_opt['copyrighttext_color'])) ? $outstock_opt['copyrighttext_color'] : '#8e8e8e'),
		'footer_border_color' => ((!empty($outstock_opt['footer_border_color'])) ? $outstock_opt['footer_border_color'] : '#383838'),
		'footer_newsletter_bg' => ((!empty($outstock_opt['footer_newsletter_bg'])) ? $outstock_opt['footer_newsletter_bg'] : '#FFF'),
		'header_bg' => ((!empty($outstock_opt['header_bg'])) ? $outstock_opt['header_bg'] : '#f5f5f5'),
		'pro_bg_color' => ((!empty($outstock_opt['pro_bg_color'])) ? $outstock_opt['pro_bg_color'] : '#f5f5f5'),
	);
	
	if( function_exists('compileLess') ){
		if(isset($_GET['demo']) && !empty($demos[intval($_GET['demo'])])){
			compileLess('theme.less', 'theme-demo-' . intval($_GET['demo']) . '.css', $params);
		}else{
			compileLess('theme.less', 'theme.css', $params);
		}
	}
	wp_enqueue_style( 'base-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'bootstrap-style', get_template_directory_uri() . '/css/bootstrap.min.css' );
	wp_enqueue_style( 'bootstrap-theme', get_template_directory_uri() . '/css/bootstrap-theme.min.css' );
	wp_enqueue_style( 'awesome-font', get_template_directory_uri() . '/css/font-awesome.min.css' );
	wp_enqueue_style( 'font-ionicons', get_template_directory_uri() . '/css/ionicons.min.css' );
	wp_enqueue_style( 'owl-css', get_template_directory_uri() . '/owl-carousel/owl.carousel.css' );
	wp_enqueue_style( 'owl-theme', get_template_directory_uri() . '/owl-carousel/owl.theme.css' );
	wp_enqueue_style( 'owl-transitions', get_template_directory_uri() . '/owl-carousel/owl.transitions.css' );
	wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css' );
	wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/fancybox/jquery.fancybox.css' );
	if (empty($outstock_opt['bodyfont']['font-family'])) {
		wp_enqueue_style( 'font-poppins', get_template_directory_uri() . '/css/font-poppins.css' );
	}
	if ( is_singular() ) wp_enqueue_script( "comment-reply" );
	if(isset($_GET['demo']) && !empty($demos[$_GET['demo']])){
		if(file_exists( get_template_directory() . '/css/theme-demo-' . intval($_GET['demo']) . '.css' )){
			wp_enqueue_style( 'theme-options', get_template_directory_uri() . '/css/theme-demo-' . intval($_GET['demo']) . '.css' );
		}
	}else{
		if(file_exists( get_template_directory() . '/css/theme.css' )){
			wp_enqueue_style( 'theme-options', get_template_directory_uri() . '/css/theme.css', array(), filemtime( get_template_directory() . '/css/theme.css' )  );
		}
	}
	
	// add add-to-cart-variation js to all other pages without detail. it help quickview work with variable products
	if( class_exists('WooCommerce') && !is_product() ) {
		wp_enqueue_script( 'wc-add-to-cart-variation', $woocommerce->plugin_url() . '/assets/js/frontend/add-to-cart-variation.js', array('jquery'), '', true );
    }
    wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'owl-wow-js', get_template_directory_uri() . '/js/jquery.wow.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'owl-modernizr-js', get_template_directory_uri() . '/js/modernizr.custom.js', array('jquery'), '', true );
    wp_enqueue_script( 'owl-carousel-js', get_template_directory_uri() . '/owl-carousel/owl.carousel.js', array('jquery'), '', true );
    wp_enqueue_script( 'auto-grid', get_template_directory_uri() . '/js/autoGrid.min.js', array('jquery'), '', true );
    wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/fancybox/jquery.fancybox.pack.js', array('jquery'), '', true );
    wp_enqueue_script( 'outstock-theme-js', get_template_directory_uri() . '/js/custom.js', array('jquery'), filemtime( get_template_directory() . '/js/custom.js'), true );
	
	// add ajaxurl
	$ajaxurl = 'var ajaxurl = "'. esc_js(admin_url('admin-ajax.php')) .'";';
	wp_add_inline_script( 'outstock-theme-js', $ajaxurl, 'before' );
	
	//sticky header
	if(!empty($outstock_opt['sticky_menu'])){
		$sticky_header_js = '
			jQuery(document).ready(function($){
				$(window).scroll(function() {
					var start = 100;
					' . ((is_admin_bar_showing()) ? '$(".main-wrapper > header").addClass("has_admin");':'') . '
					if ($(this).scrollTop() > start){  
						$(".main-wrapper > header").addClass("sticky");
					}
					else{
						$(".main-wrapper > header").removeClass("sticky");
					}
				});
			});';
		wp_add_inline_script( 'outstock-theme-js', $sticky_header_js );
	}
	
	// add newletter popup js
	if(isset($outstock_opt['enable_popup']) && $outstock_opt['enable_popup']){
		if (is_front_page() && (!empty($outstock_opt['popup_onload_form']) || !empty($outstock_opt['popup_onload_content']))) {
			$newletter_js = 'jQuery(document).ready(function($){
								if($(\'#popup_onload\').length){
									$(\'#popup_onload\').fadeIn(400);
								}
								$(document).on(\'click\', \'#popup_onload .close-popup, #popup_onload .overlay-bg-popup\', function(){
									var not_again = $(this).closest(\'#popup_onload\').find(\'.not-again input[type="checkbox"]\').prop(\'checked\');
									if(not_again){
										var datetime = new Date();
										var exdays = '. ((!empty($outstock_opt['popup_onload_expires'])) ? intval($outstock_opt['popup_onload_expires']) : 7) . ';
										datetime.setTime(datetime.getTime() + (exdays*24*60*60*1000));
										document.cookie = \'no_again=1; expires=\' + datetime.toUTCString();
									}
									$(this).closest(\'#popup_onload\').fadeOut(400);
								});
							});';
			wp_add_inline_script( 'outstock-theme-js', $newletter_js );
		}
	}
	
	
	// add remove top cart item
	$remove_cartitem_js = 'jQuery(document).on(\'click\', \'.mini_cart_item .remove\', function(e){
							var product_id = jQuery(this).data("product_id");
							var item_li = jQuery(this).closest(\'li\');
							var a_href = jQuery(this).attr(\'href\');
							jQuery.ajax({
								type: \'POST\',
								dataType: \'json\',
								url: ajaxurl,
								data: \'action=outstock_product_remove&\' + (a_href.split(\'?\')[1] || \'\'), 
								success: function(data){
									if(typeof(data) != \'object\'){
										alert(\'' . esc_html__('Could not remove cart item.', 'outstock') . '\');
										return;
									}
									jQuery(\'.topcart .cart-toggler .qty\').html(data.qty);
									jQuery(\'.topcart .cart-toggler .subtotal\').html(data.subtotal);
									jQuery(\'.topcart_content\').css(\'height\', \'auto\');
									if(data.qtycount > 0){
										jQuery(\'.topcart_content .total .amount\').html(data.subtotal);
									}else{
										jQuery(\'.topcart_content .cart_list\').html(\'<li class="empty">' .  esc_html__('No products in the cart.', 'outstock') .'</li>\');
										jQuery(\'.topcart_content .total\').remove();
										jQuery(\'.topcart_content .buttons\').remove();
									}
									item_li.remove();
								}
							});
							e.preventDefault();
							return false;
						});';
	wp_add_inline_script( 'outstock-theme-js', $remove_cartitem_js );
	
	
}
add_action( 'wp_enqueue_scripts', 'outstock_register_script' );
// bootstrap for back-end page
add_action( 'admin_enqueue_scripts', 'outstock_admin_custom' );
function outstock_admin_custom() {
	wp_enqueue_style( 'outstock-admin-custom', get_template_directory_uri() . '/css/admin.css');
}
//Outstock theme gennerate title
function outstock_wp_title( $title, $sep ) {
	global $paged, $page;
	if ( is_feed() ) return $title;
	
	$title .= get_bloginfo( 'name', 'display' );
	
	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";
	
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( esc_html__( 'Page %s', 'outstock' ), max( $paged, $page ) );
	
	return $title;
}

add_filter( 'wp_title', 'outstock_wp_title', 10, 2 );


add_action( 'wp_head', 'outstock_wp_custom_head', 100);
function outstock_wp_custom_head(){
	global $outstock_opt;
	if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) {
		if(!empty($outstock_opt['opt-favicon']['url'])) { 
			if(is_ssl()){
				$outstock_opt['opt-favicon'] = str_replace('http:', 'https:', $outstock_opt['opt-favicon']);
			}
		?>
			<link rel="icon" type="image/png" href="<?php echo esc_url($outstock_opt['opt-favicon']['url']);?>">
		<?php }
	}
}

// body class for wow scroll script
add_filter('body_class', 'outstock_effect_scroll');

function outstock_effect_scroll($classes){
	$classes[] = 'outstock-animate-scroll';
	if( !function_exists('compileLess') ){
		$classes[] = 'outstock-base-design';
	}
	return $classes;
}
?>