<?php
/**
* Theme specific widgets
*
* @package WordPress
* @subpackage Outstock_theme
* @since Outstock Themes 1.2
*/
 
/**
 * Register widgets
 *
 * @return void
 */
function outstock_widgets_init() {
	register_sidebar( array(
		'name' => esc_html__( 'Blog Sidebar', 'outstock' ),
		'id' => 'blog',
		'description' => esc_html__( 'Appears on blog page', 'outstock' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s first_last">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	
	register_sidebar( array(
		'name' => esc_html__( 'Shop Sidebar', 'outstock' ),
		'id' => 'shop',
		'description' => esc_html__( 'Sidebar on shop page', 'outstock' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s first_last">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );

	register_sidebar( array(
		'name' => esc_html__( 'Header Extension Area', 'outstock' ),
		'id' => 'top_header',
		'description' => esc_html__( 'This area on top bar of header to display language switcher, currency switcher ... For header 1 layout', 'outstock' ),
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	register_sidebar( array(
		'name' => esc_html__( 'Footer newsletter', 'outstock' ),
		'id' => 'footer_widget_newsletter',
		'description' => esc_html__( 'This area on footer to display information of newsletter', 'outstock' ),
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	register_sidebar( array(
		'name' => esc_html__( 'Footer 2 columns left', 'outstock' ),
		'id' => 'footer_2columns_left',
		'description' => esc_html__( 'This area on footer 2 columns to display short about us', 'outstock' ),
		'before_widget' => '<div class="widget widget_contact_us">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	register_sidebar( array(
		'name' => esc_html__( 'Footer 2 column right', 'outstock' ),
		'id' => 'footer_2columns_right',
		'description' => esc_html__( 'This area on footer 2 columns to display two menu columns', 'outstock' ),
		'before_widget' => '<div class="col-sm-3 col-md-3"><div class="widget_menu">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	register_sidebar( array(
		'name' => esc_html__( 'Footer 4 columns', 'outstock' ),
		'id' => 'footer_4columns',
		'description' => esc_html__( 'This area on footer 4 columns. Each widget is one column', 'outstock' ),
		'before_widget' => '<div class="col-sm-3 col-md-3 col-xs-6"><div class="widget_menu">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
}
add_action( 'widgets_init', 'outstock_widgets_init' ); 