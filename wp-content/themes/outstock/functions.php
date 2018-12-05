<?php
/**
 * Outstock Themes functions and definitions
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
//Plugin-Activation
require_once( get_template_directory().'/class-tgm-plugin-activation.php' );

 //Init the Redux Framework
if ( class_exists( 'ReduxFramework' ) && !isset( $redux_demo )){
	if(file_exists( trailingslashit(get_stylesheet_directory()) . 'theme-config.php')){
		require_once( trailingslashit(get_stylesheet_directory()) . 'theme-config.php' );
	}else{
		require_once( trailingslashit(get_template_directory()) . 'theme-config.php' );
	}
}

// require system parts
if ( file_exists( get_template_directory().'/includes/theme-helper.php' ) ) {
	require_once( get_template_directory().'/includes/theme-helper.php' );
}
if ( file_exists( get_template_directory().'/includes/widget-areas.php' ) ) {
	require_once( get_template_directory().'/includes/widget-areas.php' );
}
if ( file_exists( get_template_directory().'/includes/head-media.php' ) ) {
	require_once( get_template_directory().'/includes/head-media.php' );
}
if ( file_exists( get_template_directory().'/includes/bootstrap-extras.php' ) ) {
	require_once( get_template_directory().'/includes/bootstrap-extras.php' );
}
if ( file_exists( get_template_directory().'/includes/bootstrap-tags.php' ) ) {
	require_once( get_template_directory().'/includes/bootstrap-tags.php' );
}
if ( file_exists( get_template_directory().'/includes/woo-hook.php' ) ) {
	require_once( get_template_directory().'/includes/woo-hook.php' );
}

// theme setup
function outstock_setup(){
	// Load languages
	load_theme_textdomain( 'outstock', get_template_directory() . '/languages' );
	
	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'image', 'gallery', 'video', 'audio' ) );
	
	if ( ! isset( $content_width ) ) $content_width = 625;
	
	add_theme_support( 'title-tag' );
	
	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );

	set_post_thumbnail_size( 1170, 9999 ); // Unlimited height, soft crop
	add_image_size( 'outstock-category-thumb', 1170, 597, true ); // (cropped)
	add_image_size( 'outstock-category-full', 1170, 597, true ); // (cropped)
	add_image_size( 'outstock-post-thumb', 1170, 597, true ); // (cropped)
	add_image_size( 'outstock-post-thumbwide', 539, 358, true ); // (cropped)
	
	add_theme_support( 'woocommerce' );
	
	add_theme_support( 'custom-background', array() );
	add_theme_support( 'custom-header', array() );
	
	register_nav_menu( 'primary', esc_html__( 'Primary Menu', 'outstock' ) );
	register_nav_menu( 'mobilemenu', esc_html__( 'Mobile Menu', 'outstock' ) );
	
	if(class_exists('WooCommerce')){
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}
	
	add_editor_style( array( 'css/editor-style.css' ) );
}
add_action( 'after_setup_theme', 'outstock_setup');

/**
* TGM-Plugin-Activation
*/
add_action( 'tgmpa_register', 'outstock_register_required_plugins'); 
function outstock_register_required_plugins(){
	$plugins = array(
				array(
					'name'               => esc_html__('LionThemes Helper', 'outstock'),
					'slug'               => 'lionthemes-helper',
					'source'             => get_template_directory() . '/plugins/lionthemes-helper.zip',
					'required'           => true,
					'external_url'       => '',
					'force_activation' => false,
					'force_deactivation' => false,
				),
				array(
					'name'               => esc_html__('Mega Main Menu', 'outstock'),
					'slug'               => 'mega_main_menu',
					'source'             => get_template_directory() . '/plugins/mega_main_menu.zip',
					'required'           => true,
					'external_url'       => '',
				),
				array(
					'name'               => esc_html__('Revolution Slider', 'outstock'),
					'slug'               => 'revslider',
					'source'             => get_template_directory() . '/plugins/revslider.zip',
					'required'           => true,
					'external_url'       => '',
				),
				array(
					'name'               => esc_html__('Visual Composer', 'outstock'),
					'slug'               => 'js_composer',
					'source'             => get_template_directory() . '/plugins/js_composer.zip',
					'required'           => true,
					'external_url'       => '',
				),
				// Plugins from the Online WordPress Plugin
				array(
					'name'               => esc_html__('Redux Framework', 'outstock'),
					'slug'               => 'redux-framework',
					'required'           => true,
					'force_activation'   => false,
					'force_deactivation' => false,
				),
				array(
					'name'      => esc_html__('Contact Form 7', 'outstock'),
					'slug'      => 'contact-form-7',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('MailChimp for WP', 'outstock'),
					'slug'      => 'mailchimp-for-wp',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('Projects', 'outstock'),
					'slug'      => 'projects-by-woothemes',
					'required'  => false,
				),
				array(
					'name'      => esc_html__('Shortcodes Ultimate', 'outstock'),
					'slug'      => 'shortcodes-ultimate',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('TinyMCE Widget', 'outstock'),
					'slug'      => 'black-studio-tinymce-widget',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('Testimonials', 'outstock'),
					'slug'      => 'testimonials-by-woothemes',
					'required'  => false,
				),
				array(
					'name'      => esc_html__('TinyMCE Advanced', 'outstock'),
					'slug'      => 'tinymce-advanced',
					'required'  => false,
				),
				array(
					'name'      => esc_html__('Widget Importer & Exporter', 'outstock'),
					'slug'      => 'widget-importer-exporter',
					'required'  => false,
				),
				array(
					'name'      => esc_html__('WooCommerce', 'outstock'),
					'slug'      => 'woocommerce',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('YITH WooCommerce Compare', 'outstock'),
					'slug'      => 'yith-woocommerce-compare',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('YITH WooCommerce Wishlist', 'outstock'),
					'slug'      => 'yith-woocommerce-wishlist',
					'required'  => true,
				),
				array(
					'name'      => esc_html__('YITH WooCommerce Zoom Magnifier', 'outstock'),
					'slug'      => 'yith-woocommerce-zoom-magnifier',
					'required'  => true,
				),
			);
			
	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'default_path' => '',                      // Default absolute path to pre-packaged plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => array(
			'page_title'                      => esc_html__( 'Install Required Plugins', 'outstock' ),
			'menu_title'                      => esc_html__( 'Install Plugins', 'outstock' ),
			'installing'                      => esc_html__( 'Installing Plugin: %s', 'outstock' ), // %s = plugin name.
			'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'outstock' ),
			'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'outstock' ),
			'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'outstock' ), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'outstock' ), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'outstock' ), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'outstock' ), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'outstock' ), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'outstock' ), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'outstock' ), // %1$s = plugin name(s).
			'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'outstock' ),
			'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'outstock' ),
			'return'                          => esc_html__( 'Return to Required Plugins Installer', 'outstock' ),
			'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'outstock' ),
			'complete'                        => esc_html__( 'All plugins installed and activated successfully. %s', 'outstock' ), // %s = dashboard link.
			'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
	);
	tgmpa( $plugins, $config );
}