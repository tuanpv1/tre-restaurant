<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('Outstock_Theme_Config')) {

    class Outstock_Theme_Config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if (  true == Redux_Helpers::isTheme(__FILE__) ) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        public function initSettings() {

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }
            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css, $changed_values) {
            echo '<h1>The compiler hook has run!</h1>';
            echo "<pre>";
            print_r($changed_values); // Values that have changed since the last save
            echo "</pre>";
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => esc_html__('Section via hook', 'outstock'),
                'desc' => esc_html__('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'outstock'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        /**

          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            return $args;
        }

        /**

          Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {

            ob_start();

            $ct             = wp_get_theme();
            $this->theme    = $ct;
            $item_name      = $this->theme->get('Name');
            $tags           = $this->theme->Tags;
            $screenshot     = $this->theme->get_screenshot();
            $class          = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(esc_html__('Customize &#8220;%s&#8221;', 'outstock'), $this->theme->display('Name'));
            
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview', 'outstock'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview', 'outstock'); ?>" />
                <?php endif; ?>

                <h4><?php echo esc_html($this->theme->display('Name')); ?></h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(esc_html__('By %s', 'outstock'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(esc_html__('Version %s', 'outstock'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . esc_html__('Tags', 'outstock') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo esc_html($this->theme->display('Description')); ?></p>
            <?php
            if ($this->theme->parent()) {
                printf(' <p class="howto">' . esc_html__('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.', 'outstock') . '</p>', esc_html__('http://codex.wordpress.org/Child_Themes', 'outstock'), $this->theme->parent()->display('Name'));
            }
            ?>

                </div>
            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            $sampleHTML = '';
          
			
            // General
            $this->sections[] = array(
                'title'     => esc_html__('General', 'outstock'),
                'desc'      => esc_html__('General theme options', 'outstock'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(

                    array(
                        'id'        => 'logo_main',
                        'type'      => 'media',
                        'title'     => esc_html__('Logo', 'outstock'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload logo here.', 'outstock'),
                    ),
					array(
                        'id'        => 'opt-favicon',
                        'type'      => 'media',
                        'title'     => esc_html__('Favicon', 'outstock'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload favicon here.', 'outstock'),
                    ),
					array(
                        'id'        => 'background_opt',
                        'type'      => 'background',
                        'output'    => array('body'),
                        'title'     => esc_html__('Body background', 'outstock'),
                        'subtitle'  => esc_html__('Upload image or select color. Only work with box layout', 'outstock'),
						'default'   => array('background-color' => '#fff'),
                    ),
					array(
                        'id'        => 'back_to_top',
                        'type'      => 'switch',
                        'title'     => esc_html__('Back To Top', 'outstock'),
						'desc'      => esc_html__('Show back to top button on all pages', 'outstock'),
						'default'   => true,
                    ),
                ),
            );
			
			// Colors
            $this->sections[] = array(
                'title'     => esc_html__('Colors', 'outstock'),
                'desc'      => esc_html__('Color options', 'outstock'),
                'icon'      => 'el-icon-tint',
                'fields'    => array(
				          	array(
                        'id'        => 'primary_color',
                        'type'      => 'color',
                        'title'     => esc_html__('Primary Color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for primary color (default: #bd8348).', 'outstock'),
						            'transparent' => false,
                        'default'   => '#bd8348',
                        'validate'  => 'color',
                    ),
					
					        array(
                        'id'        => 'sale_color',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Sale Label BG Color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for bg sale label (default: #535353).', 'outstock'),
						            'transparent' => true,
                        'default'   => '#535353',
                        'validate'  => 'color',
                    ),
					
					        array(
                        'id'        => 'saletext_color',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Sale Label Text Color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for sale label text (default: #ffffff).', 'outstock'),
                        'transparent' => false,
                        'default'   => '#ffffff',
                        'validate'  => 'color',
                    ),
					
					        array(
                        'id'        => 'rate_color',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Rating Star Color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for star of rating (default: #181818).', 'outstock'),
						            'transparent' => false,
                        'default'   => '#181818',
                        'validate'  => 'color',
                    ),
                ),
            );
			
			//Header
			$this->sections[] = array(
            'title'     => esc_html__('Header', 'outstock'),
            'desc'      => esc_html__('Header options', 'outstock'),
            'icon'      => 'el-icon-tasks',
            'fields'    => array(
              array(
                    'id'        => 'header_layout',
                    'type'      => 'select',
                    'title'     => esc_html__('Header Layout', 'outstock'),

                    //Must provide key => value pairs for select options
                    'options'   => array(
                        'default' => 'Default',
                        'second' => 'Second',
                        'third' => 'Third',
                        'fourth' => 'Fourth',
                    ),
                    'default'   => 'default'
                ),
               array(
                    'id'        => 'sticky_menu',
                    'type'      => 'switch',
                    'title'     => esc_html__('Enable sticky menu', 'outstock'),
                    'default'   => true,
                ),
                array(
                  'id'        => 'header_bg',
                  'type'      => 'color',
                  //'output'    => array(),
                  'title'     => esc_html__('Header Background Color', 'outstock'),
                  'subtitle'  => esc_html__('Pick a color (default: #f5f5f5).', 'outstock'),
                  'transparent' => true,
                  'default'   => '#f5f5f5',
                  'validate'  => 'color',
              ),
          ),
      );
			
			   
		//Footer
		$this->sections[] = array(
			'title'     => esc_html__('Footer', 'outstock'),
			'desc'      => esc_html__('Footer options', 'outstock'),
			'icon'      => 'el-icon-cog',
			'fields'    => array(
				array(
					'id'        => 'footer_layout',
					'type'      => 'select',
					'title'     => esc_html__('Footer Layout', 'outstock'),
					'options'   => array(
						'default' => 'Default',
						'second' => 'Second',
						'third' => 'Third',
						'four' => 'Four',
                        'fifth' => 'Fifth',
                        'sixth' => 'Sixth',
					),
					'default'   => 'default'
				),
				array(
					'id'               => 'copyright',
					'type'             => 'editor',
					'title'    => esc_html__('Copyright information', 'outstock'),
					'subtitle'         => esc_html__('HTML tags allowed: a, br, em, strong', 'outstock'),
					'default'          => '',
					'args'   => array(
					  'teeny'            => true,
					  'textarea_rows'    => 5,
					  'media_buttons'	=> false,
					)
				  ),
				array(
					'id'        => 'footer_bg',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Footer Background Color', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #232323).', 'outstock'),
					'transparent' => true,
					'default'   => '#232323',
					'validate'  => 'color',
				),
				array(
					'id'        => 'footertext_color',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Footer Text Color', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #8e8e8e).', 'outstock'),
					'transparent' => true,
					'default'   => '#8e8e8e',
					'validate'  => 'color',
				),
				array(
					'id'        => 'copyright_bg',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Copyright Background Color', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #232323).', 'outstock'),
					'transparent' => true,
					'default'   => '#232323',
					'validate'  => 'color',
				),
				array(
					'id'        => 'copyrighttext_color',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Copyright Text Color', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #8e8e8e).', 'outstock'),
					'transparent' => true,
					'default'   => '#8e8e8e',
					'validate'  => 'color',
				),
				array(
					'id'        => 'footer_border_color',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Footer border Color', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #383838).', 'outstock'),
					'transparent' => true,
					'default'   => '#383838',
					'validate'  => 'color',
				),
				array(
					'id'        => 'footer_newsletter_bg',
					'type'      => 'color',
					//'output'    => array(),
					'title'     => esc_html__('Footer newsletter background', 'outstock'),
					'subtitle'  => esc_html__('Pick a color (default: #FFF).', 'outstock'),
					'transparent' => true,
					'default'   => '#FFF',
					'validate'  => 'color',
				),
			),
		);
			
		$this->sections[] = array(
			'icon'       => 'el-icon-website',
			'title'      => esc_html__( 'Social Icons', 'outstock' ),
			'subsection' => false,
			'fields'     => array(				 
			 array(
			  'id'       => 'social_icons',
			  'type'     => 'sortable',
			  'title'    => esc_html__('Social Icons', 'outstock'),
			  'subtitle' => esc_html__('Enter social links', 'outstock'),
			  'desc'     => esc_html__('Drag/drop to re-arrange', 'outstock'),
			  'mode'     => 'text',
			  'options'  => array(
				'twitter'     => '',
				'skype'     => '',
				'vine'     => '',
				'facebook'     => '',
				'instagram' => '',
				'tumblr'     => '',
				'pinterest'     => '',
				'google-plus'     => '',
				'linkedin'     => '',
				'behance'     => '',
				'dribbble'     => '',
				'youtube'     => '',				   
				'rss'     => '',
				'vk'     => '',
				'yahoo'     => '',
				'qq'     => '',				   
				'weibo'     => '',
				'snapchat'     => '',
			  ),
			  'default' => array(	
				'facebook'     => '#facebook',
				'twitter'     => '#twitter.com',
				'rss'     => '#rss',
				'google-plus'     => '#google',			   
				'linkedin'     => '#linkedin',
			  ),
			 ),
			)
		);
			
			//Newsletter Popup
			$this->sections[] = array(
				'icon'       => 'el-icon-website',
				'title'      => esc_html__( 'Newsletter Popup', 'outstock' ),
				'desc'      => esc_html__('Content show up on home page loaded', 'outstock'),
				'fields'     => array(
					array(
                        'id'        => 'enable_popup',
                        'type'      => 'switch',
                        'title'     => esc_html__('Enable', 'outstock'),
						'default'   => true,
                    ),
					array(
                        'id'        => 'background_popup',
                        'type'      => 'background',
                        //'output'    => array(''),
                        'title'     => esc_html__('Popup background', 'outstock'),
                        'subtitle'  => esc_html__('Upload image or select color.', 'outstock'),
						'default'   => array('background-color' => '#eee'),
                    ),
					array(
						'id'=>'popup_onload_content',
						'type' => 'editor',
						'title' => esc_html__('Content', 'outstock'), 
						'subtitle'         => esc_html__('HTML tags allowed: a, img, br, em, strong, p, ul, li', 'outstock'),
						'default' => '<h3>Get our email letter</h3>
									Subscribe to the Outstock mailing list to receive updates on new arrivals, special offers and other discount information.',
						'args'   => array(
							'teeny'            => true,
							'textarea_rows'    => 10,
							'media_buttons'	=> true,
						)
					),
					array(
                        'id'        => 'popup_onload_form',
                        'type'      => 'text',
                        'title'     => esc_html__('Mailchimp Form ID', 'outstock'),
						'default'   => '235',
                    ),
					array(
						'id'        => 'popup_onload_expires',
						'type'      => 'slider',
						'title'     => esc_html__('Time expires', 'outstock'),
						'desc'      => esc_html__('Time expires after tick not show again defaut: 7 days', 'outstock'),
						'default'   => 1,
						'min'       => 1,
						'step'      => 1,
						'max'       => 7,
						'display_value' => 'text'
					),
				)
			);
			
			//Fonts
			$this->sections[] = array(
                'title'     => esc_html__('Fonts', 'outstock'),
                'desc'      => esc_html__('Fonts options', 'outstock'),
                'icon'      => 'el-icon-font',
                'fields'    => array(

                    array(
                        'id'            => 'bodyfont',
                        'type'          => 'typography',
                        'title'         => esc_html__('Body font', 'outstock'),
                        //'compiler'      => true,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        //'font-style'    => false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => false, // Only appears if google is true and subsets not set to false
						            'text-align'   => false,
                        //'font-size'     => false,
                        //'line-height'   => false,
                        //'word-spacing'  => true,  // Defaults to false
                        //'letter-spacing'=> true,  // Defaults to false
                        //'color'         => false,
                        //'preview'       => false, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        => array('body'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      => array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Main body font.', 'outstock'),
                        'default'       => array(
                            'color'         => '#606060',
                            'font-weight'    => '400',
                            'font-family'   => 'Poppins',
                            'google'        => true,
                            'font-size'     => '14px',
                            'line-height'   => '24px'
						            ),
                    ),
					            array(
                        'id'            => 'headingfont',
                        'type'          => 'typography',
                        'title'         => esc_html__('Heading font', 'outstock'),
                        //'compiler'      => true,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        //'font-style'    => false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => false, // Only appears if google is true and subsets not set to false
                        'font-size'     => false,
                        'line-height'   => false,
						            'text-align'   => false,
                        //'word-spacing'  => true,  // Defaults to false
                        //'letter-spacing'=> true,  // Defaults to false
                        //'color'         => false,
                        //'preview'       => false, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        //'output'        => array('h1, h2, h3, h4, h5, h6'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      => array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Heading font.', 'outstock'),
                        'default'       => array(
							'color'         => '#201f1f',
                            'font-weight'    => '500',
                            'font-family'   => 'Poppins',
                            'google'        => true,
						            ),
                    ),
					          array(
                        'id'            => 'menufont',
                        'type'          => 'typography',
                        'title'         => esc_html__('Menu font', 'outstock'),
                        //'compiler'      => true,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        //'font-style'    => false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => false, // Only appears if google is true and subsets not set to false
                        'font-size'     => true,
                        'line-height'   => false,
                        'text-align'   => false,
                        'color'         => true,
                        //'word-spacing'  => true,  // Defaults to false
                        //'letter-spacing'=> true,  // Defaults to false
                        //'color'         => false,
                        //'preview'       => false, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        //'output'        => array('h1, h2, h3, h4, h5, h6'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      => array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         => 'px', // Defaults to px
                        'subtitle'      => esc_html__('Menu font.', 'outstock'),
                        'default'       => array(
                            'font-weight'    => '500',
                            'font-family'   => 'Poppins',
							              'font-size'     => '14px',
                            'google'        => true,
							              'color'			=> '#a3a3a3',
						            ),
                    ),
					          array(
                        'id'            => 'pricefont',
                        'type'          => 'typography',
                        'title'         => esc_html__('Price Font', 'outstock'),
                        //'compiler'      => true,  // Use if you want to hook in your own CSS compiler
                        'google'        => true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   => false,    // Select a backup non-google font in addition to a google font
                        'font-style'    => false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        'subsets'       => false, // Only appears if google is true and subsets not set to false
                        'font-size'     => false,
                        'line-height'   => false,
                        'text-align'   => false,
                        'font-weight'   => false,
                        'color'         => true,
                        //'word-spacing'  => true,  // Defaults to false
                        //'letter-spacing'=> true,  // Defaults to false
                        //'color'         => false,
                        //'preview'       => false, // Disable the previewer
                        'all_styles'    => true,    // Enable all Google Font style/weight variations to be added to the page
                        //'output'        => array('h1, h2, h3, h4, h5, h6'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      => array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         => 'px', // Defaults to px
                        'default'       => array(
                            'font-family'   => 'Rubik',
                            'color'			=> '#23232c',
                        ),
                    ),
					          array(
                        'id'        => 'sub_menu_bg',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Submenu background', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for sub menu bg (default: #7d7d7d).', 'outstock'),
						            'transparent' => false,
                        'default'   => '#fff',
                        'validate'  => 'color',
                    ),
					          array(
                        'id'        => 'sub_menu_color',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Submenu color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color for sub menu color (default: #7d7d7d).', 'outstock'),
						            'transparent' => false,
                        'default'   => '#7d7d7d',
                        'validate'  => 'color',
                    ),
                ),
            );
			
			// Layout
            $this->sections[] = array(
                'title'     => esc_html__('Layout', 'outstock'),
                'desc'      => esc_html__('Select page layout: Box or Full Width', 'outstock'),
                'icon'      => 'el-icon-align-justify',
                'fields'    => array(
					array(
						'id'       => 'page_layout',
						'type'     => 'select',
						'multi'    => false,
						'title'    => esc_html__('Page Layout', 'outstock'),
						'options'  => array(
							'full' => 'Full Width',
							'box' => 'Box'
						),
						'default'  => 'full'
					),
					array(
						'id'        => 'box_layout_width',
						'type'      => 'slider',
						'title'     => esc_html__('Box layout width', 'outstock'),
						'desc'      => esc_html__('Box layout width in pixels, default value: 1200', 'outstock'),
						"default"   => 1240,
						"min"       => 960,
						"step"      => 1,
						"max"       => 1920,
						'display_value' => 'text'
					),
                ),
            );
			
			//Brand logos
			$this->sections[] = array(
                'title'     => esc_html__('Brand Logos', 'outstock'),
                'desc'      => esc_html__('Upload brand logos and links', 'outstock'),
                'icon'      => 'el-icon-briefcase',
                'fields'    => array(
					array(
						'id'          => 'brand_logos_1',
						'type'        => 'slides',
						'title'       => esc_html__('List Logo', 'outstock'),
						'desc'        => esc_html__('Upload logo image and enter logo link.', 'outstock'),
						'placeholder' => array(
							'title'           => esc_html__('Title', 'outstock'),
							'description'     => esc_html__('Description', 'outstock'),
							'url'             => esc_html__('Link', 'outstock'),
						),
					),
                ),
            );
			
			
			// Portfolio
            $this->sections[] = array(
                'title'     => esc_html__('Portfolio', 'outstock'),
                'desc'      => esc_html__('Use this section to select options for portfolio', 'outstock'),
                'icon'      => 'el-icon-bookmark',
                'fields'    => array(
					array(
						'id'        => 'portfolio_columns',
						'type'      => 'slider',
						'title'     => esc_html__('Portfolio Columns', 'outstock'),
						"default"   => 3,
						"min"       => 2,
						"step"      => 1,
						"max"       => 4,
						'display_value' => 'text'
					),
					array(
						'id'        => 'portfolio_per_page',
						'type'      => 'slider',
						'title'     => esc_html__('Projects per page', 'outstock'),
						'desc'      => esc_html__('Amount of projects per page on portfolio page', 'outstock'),
						"default"   => 12,
						"min"       => 4,
						"step"      => 1,
						"max"       => 48,
						'display_value' => 'text'
					),
					array(
                        'id'        => 'related_project_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Related projects title', 'outstock'),
                        'default'   => 'Related Projects'
                    ),
                ),
            );
			

			// Products
            $this->sections[] = array(
                'title'     => esc_html__('Products', 'outstock'),
                'desc'      => esc_html__('Use this section to select options for product', 'outstock'),
                'icon'      => 'el-icon-tags',
                'fields'    => array(
				    array(
                        'id'       => 'sidebarshop_pos',
                        'type'     => 'radio',
                        'title'    => esc_html__('Shop Layout', 'outstock'),
                        'subtitle'      => esc_html__('Sidebar widgets should be add in Widgets manage -> Shop sidebar area.', 'outstock'),
                        'options'  => array(
                            '' => 'Full width',
                            'left' => 'Left sidebar',
                            'right' => 'Right sidebar'
                            ),
                        'default'  => 'left'
                    ),
					array(
						'id'       => 'main_search',
						'type'     => 'radio',
						'title'    => esc_html__('Search box layout type', 'outstock'),
						'options'  => array(
							'' => esc_html__('Basic', 'outstock'),
							'advanced' => esc_html__('Advance', 'outstock'),
							),
						'default'  => 'advanced'
					),
					array(
                        'id'        => 'categories_search',
                        'type'      => 'select',
                        'title'     => esc_html__('Categories in search box', 'outstock'),
                        'data' => 'terms',
						'args' => array(
							'taxonomies' => array( 'product_cat' ),
						),
						'multi' => true
                    ),
					array(
                        'id'        => 'default_view',
                        'type'      => 'select',
                        'title'     => esc_html__('Shop default view', 'outstock'),
                        'options'   => array(
							'grid-view' => 'Grid View',
                            'list-view' => 'List View',
                        ),
                        'default'   => 'grid-view'
                    ),
					array(
						'id'        => 'product_per_page',
						'type'      => 'slider',
						'title'     => esc_html__('Products per page', 'outstock'),
						'subtitle'      => esc_html__('Amount of products per page on category page', 'outstock'),
						'default'   => 12,
						'min'       => 4,
						'step'      => 1,
						'max'       => 48,
						'display_value' => 'text'
					),
					array(
						'id'        => 'product_per_row',
						'type'      => 'slider',
						'title'     => esc_html__('Product columns', 'outstock'),
						'subtitle'      => esc_html__('Amount of product columns on category page', 'outstock'),
						'default'   => 3,
						'min'       => 1,
						'step'      => 1,
						'max'       => 6,
						'display_value' => 'text'
					),
					array(
						'id'       => 'enable_loadmore',
						'type'     => 'radio',
						'title'    => esc_html__('Load more ajax', 'outstock'),
						'options'  => array(
							'' => esc_html__('Default pagination', 'outstock'),
							'scroll-more' => esc_html__('Scroll to load more', 'outstock'),
							'button-more' => esc_html__('Button load more', 'outstock')
							),
						'default'  => ''
					),
					array(
						'id'       => 'second_image',
						'type'     => 'switch',
						'title'    => esc_html__('Use secondary product image', 'outstock'),
						'desc'      => esc_html__('Show the secondary image when hover on product on list', 'outstock'),
						'default'  => true,
					),
					array(
                        'id'        => 'new_pro_from',
                        'type'      => 'text',
                        'title'     => esc_html__('New product from', 'outstock'),
						'desc'      => esc_html__('This set a day to mark product as new from that day to now. Blank for not set.', 'outstock'),
                        'default'   => ''
                    ),
					array(
                        'id'        => 'new_pro_label',
                        'type'      => 'text',
                        'title'     => esc_html__('New label', 'outstock'),
                        'default'   => 'New'
                    ),
					array(
                        'id'        => 'featured_pro_label',
                        'type'      => 'text',
                        'title'     => esc_html__('Featured label', 'outstock'),
                        'default'   => 'Hot'
                    ),
					array(
                        'id'        => 'upsells_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Up-Sells title', 'outstock'),
                        'default'   => 'Up-Sells'
                    ),
					array(
                        'id'        => 'crosssells_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Cross-Sells title', 'outstock'),
                        'default'   => 'Cross-Sells'
                    ),
                ),
            );
			$this->sections[] = array(
				'icon'       => 'el-icon-website',
				'title'      => esc_html__( 'Product page', 'outstock' ),
				'subsection' => true,
				'fields'     => array(
					array(
                        'id'        => 'gallery_thumbnail_size',
                        'type'      => 'dimensions',
                        'title'     => esc_html__('Gallery thumbnails size', 'outstock'),
                        'subtitle'  => esc_html__('Width x Height : Empty height value to disable crop image.', 'outstock'),
                        'units'     => false,
                        'default'  => array(
                            'width'   => '140', 
                            'height'  => '175'
                        ),
                    ),
          			array(
                        'id'        => 'pro_bg_color',
                        'type'      => 'color',
                        //'output'    => array(),
                        'title'     => esc_html__('Body background color', 'outstock'),
                        'subtitle'  => esc_html__('Pick a color default: #f5f5f5).', 'outstock'),
						'transparent' => true,
                        'default'   => '#f5f5f5',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'related_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Related products title', 'outstock'),
                        'default'   => 'Related Products'
                    ),
					array(
						'id'       => 'thumb_slider_direct',
						'type'     => 'radio',
						'title'    => esc_html__('Thumbnail slider direction', 'outstock'),
						'options'  => array(
							'' => esc_html__('Horizontal', 'outstock'),
							'vertical' => esc_html__('Vertical', 'outstock'),
							),
						'default'  => ''
					),
					array(
						'id'        => 'related_amount',
						'type'      => 'slider',
						'title'     => esc_html__('Number of related products', 'outstock'),
						"default"   => 4,
						"min"       => 1,
						"step"      => 1,
						"max"       => 16,
						'display_value' => 'text'
					),
					array(
                        'id'        => 'upsells_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Up-Sells title', 'outstock'),
                        'default'   => 'Up-Sells'
                    ),
					array(
						'id'       => 'pro_social_share',
						'type'     => 'checkbox',
						'title'    => esc_html__('Social share', 'outstock'), 
						'options'  => array(
							'facebook' => esc_html__('Facebook', 'outstock'),
							'twitter' => esc_html__('Twitter', 'outstock'),
							'pinterest' => esc_html__('Pinterest', 'outstock'),
							'gplus' => esc_html__('Gplus', 'outstock'),
							'linkedin' => esc_html__('LinkedIn', 'outstock')
						),
						'default' => array(
							'facebook' => '1', 
							'twitter' => '1', 
							'pinterest' => '1',
							'gplus' => '1',
							'linkedin' => '1',
						)
					)
				)
			);
			$this->sections[] = array(
				'icon'       => 'el-icon-website',
				'title'      => esc_html__( 'Quick View', 'outstock' ),
				'subsection' => true,
				'fields'     => array(
					array(
                        'id'        => 'detail_link_text',
                        'type'      => 'text',
                        'title'     => esc_html__('View details text', 'outstock'),
                        'default'   => 'View details'
                    ),
					array(
                        'id'        => 'quickview_link_text',
                        'type'      => 'text',
                        'title'     => esc_html__('View all features text', 'outstock'),
						'desc'      => esc_html__('This is the text on quick view box', 'outstock'),
                        'default'   => 'See all features'
                    ),
				)
			);
			// Blog options
            $this->sections[] = array(
                'title'     => esc_html__('Blog', 'outstock'),
                'desc'      => esc_html__('Use this section to select options for blog', 'outstock'),
                'icon'      => 'el-icon-file',
                'fields'    => array(
                    array(
                        'id'       => 'sidebarblog_pos',
                        'type'     => 'radio',
                        'title'    => esc_html__('Blog Layout', 'outstock'),
                        'subtitle'      => esc_html__('Sidebar widgets should be set in Widgets manage -> Blog Sidebar area', 'outstock'),
                        'options'  => array(
                            '' => 'Full width',
                            'left' => 'Left sidebar',
                            'right' => 'Right sidebar'
                            ),
                        'default'  => ''
                    ),
					array(
                        'id'        => 'blog_header_text',
                        'type'      => 'text',
                        'title'     => esc_html__('Blog header text', 'outstock'),
                        'default'   => 'Blog'
                    ),
					array(
                        'id'        => 'blog_header_subtext',
                        'type'      => 'text',
                        'title'     => esc_html__('Blog header sub-text', 'outstock'),
                        'default'   => 'Text of the printing and typesetting industry'
                    ),
					array(
                        'id'        => 'blog_column',
                        'type'      => 'select',
                        'title'     => esc_html__('Blog Content Column', 'outstock'),
                        'options'   => array(
							12 => 'One Column',
							6 => 'Two Column',
							4 => 'Three Column',
							3 => 'Four Column'
                        ),
                        'default'   => 6
                    ),
					array(
                        'id'        => 'readmore_text',
                        'type'      => 'text',
                        'title'     => esc_html__('Read more text', 'outstock'),
                        'default'   => 'read more'
                    ),
					array(
						'id'        => 'excerpt_length',
						'type'      => 'slider',
						'title'     => esc_html__('Excerpt length on blog page', 'outstock'),
						"default"   => 15,
						"min"       => 10,
						"step"      => 2,
						"max"       => 120,
						'display_value' => 'text'
					),
					array(
                        'id'        => 'blog_archive_month_format',
                        'type'      => 'select',
                        'title'     => esc_html__('Archive month format', 'outstock'),
                        'options'   => array(
							'F, Y' => 'January, 1999',
							'M, Y' => 'Jan, 1999',
							'm, Y' => '01, 1999',
							'n, Y' => '1, 1999',
                        ),
                        'default'   => 'F, Y'
                    ),
					array(
                        'id'        => 'blog_archive_year_format',
                        'type'      => 'select',
                        'title'     => esc_html__('Archive year format', 'outstock'),
                        'options'   => array(
							'Y' => '1999',
							'y' => '99',
                        ),
                        'default'   => 'Y'
                    ),
					array(
						'id'       => 'post_social_share',
						'type'     => 'checkbox',
						'title'    => esc_html__('Social share', 'outstock'), 
						'options'  => array(
							'facebook' => esc_html__('Facebook', 'outstock'),
							'twitter' => esc_html__('Twitter', 'outstock'),
							'pinterest' => esc_html__('Pinterest', 'outstock'),
							'gplus' => esc_html__('Gplus', 'outstock'),
							'linkedin' => esc_html__('LinkedIn', 'outstock')
						),
						'default' => array(
							'facebook' => '1', 
							'twitter' => '1', 
							'pinterest' => '1',
							'gplus' => '1',
							'linkedin' => '1',
						)
					)
                ),
            );
			
			// Error 404 page
            $this->sections[] = array(
                'title'     => esc_html__('Error 404 Page', 'outstock'),
                'desc'      => esc_html__('Error 404 page options', 'outstock'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(
					array(
                        'id'        => 'background_error',
                        'type'      => 'background',
                        'output'    => array('body.error404'),
                        'title'     => esc_html__('Error 404 background', 'outstock'),
                        'subtitle'  => esc_html__('Upload image or select color.', 'outstock'),
						'default'   => array('background-color' => ''),
                    ),
					array(
                        'id'        => '404-content',
                        'type'      => 'editor',
                        'title'     => esc_html__('404 Content', 'outstock'),
						'default' => '<h3>Component not found</h3>
									<h2>Oh my gosh! You found it!!!</h2>
									<p>The page are looking for has moved or does not exist anymore, If you like you can return our homepage.<br/>If the problem persists, please send us a email from <a href="/contact">Contact us</a></p>',
						'args'   => array(
							'teeny'            => true,
							'textarea_rows'    => 10,
							'media_buttons'	=> true,
						)
                    ),
                ),
            );
			
			// Less Compiler
            $this->sections[] = array(
                'title'     => esc_html__('Less Compiler', 'outstock'),
                'desc'      => esc_html__('Turn on this option to apply all theme options. Turn of when you have finished changing theme options and your site is ready.', 'outstock'),
                'icon'      => 'el-icon-wrench',
                'fields'    => array(
					array(
                        'id'        => 'enable_less',
                        'type'      => 'switch',
                        'title'     => esc_html__('Enable Less Compiler', 'outstock'),
						'default'   => true,
                    ),
                ),
            );
			
            $theme_info  = '<div class="redux-framework-section-desc">';
            $theme_info .= '<p class="redux-framework-theme-data description theme-uri">' . esc_html__('<strong>Theme URL:</strong> ', 'outstock') . '<a href="' . esc_url($this->theme->get('ThemeURI')) . '" target="_blank">' . $this->theme->get('ThemeURI') . '</a></p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-author">' . esc_html__('<strong>Author:</strong> ', 'outstock') . $this->theme->get('Author') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-version">' . esc_html__('<strong>Version:</strong> ', 'outstock') . $this->theme->get('Version') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-description">' . $this->theme->get('Description') . '</p>';
            $tabs = $this->theme->get('Tags');
            if (!empty($tabs)) {
                $theme_info .= '<p class="redux-framework-theme-data description theme-tags">' . esc_html__('<strong>Tags:</strong> ', 'outstock') . implode(', ', $tabs) . '</p>';
            }
            $theme_info .= '</div>';

            $this->sections[] = array(
                'title'     => esc_html__('Import / Export', 'outstock'),
                'desc'      => esc_html__('Import and Export your Redux Framework settings from file, text or URL.', 'outstock'),
                'icon'      => 'el-icon-refresh',
                'fields'    => array(
                    array(
                        'id'            => 'opt-import-export',
                        'type'          => 'import_export',
                        'title'         => 'Import Export',
                        'subtitle'      => 'Save and restore your Redux options',
                        'full_width'    => false,
                    ),
                ),
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-info-sign',
                'title'     => esc_html__('Theme Information', 'outstock'),
                'fields'    => array(
                    array(
                        'id'        => 'opt-raw-info',
                        'type'      => 'raw',
                        'content'   => $item_info,
                    )
                ),
            );
        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-1',
                'title'     => esc_html__('Theme Information 1', 'outstock'),
                'content'   => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'outstock')
            );

            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-2',
                'title'     => esc_html__('Theme Information 2', 'outstock'),
                'content'   => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'outstock')
            );

            // Set the help sidebar
            $this->args['help_sidebar'] = esc_html__('<p>This is the sidebar content, HTML is allowed.</p>', 'outstock');
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name'          => 'outstock_opt',            // This is where your data is stored in the database and also becomes your global variable name.
                'display_name'      => $theme->get('Name'),     // Name that appears at the top of your panel
                'display_version'   => $theme->get('Version'),  // Version that appears at the top of your panel
                'menu_type'         => 'menu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
                'menu_title'        => esc_html__('Theme Options', 'outstock'),
                'page_title'        => esc_html__('Theme Options', 'outstock'),
                
                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => '', // Must be defined to add google fonts to the typography module
                
                'async_typography'  => true,                    // Use a asynchronous font on the front end or font string
                //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
                'admin_bar'         => true,                    // Show the panel pages on the admin bar
                'global_variable'   => '',                      // Set a different name for your global variable other than the opt_name
                'dev_mode'          => false,                    // Show the time the page took to load, etc
                'customizer'        => true,                    // Enable basic customizer support
                //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
                //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

                // OPTIONAL -> Give you extra features
                'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent'       => 'themes.php',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
                'menu_icon'         => '',                      // Specify a custom URL to an icon
                'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
                'page_icon'         => 'icon-themes',           // Icon displayed in the admin panel next to your menu_title
                'page_slug'         => '_options',              // Page slug used to denote the panel
                'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
                'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
                'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => true,                   // Shows the Import/Export panel when not used as a field.
                
                // CAREFUL -> These options are for advanced use only
                'transient_time'    => 60 * MINUTE_IN_SECONDS,
                'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
                
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database'              => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info'           => false, // REMOVE

                // HINTS
                'hints' => array(
                    'icon'          => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color'    => 'lightgray',
                    'icon_size'     => 'normal',
                    'tip_style'     => array(
                        'color'         => 'light',
                        'shadow'        => true,
                        'rounded'       => false,
                        'style'         => '',
                    ),
                    'tip_position'  => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
                    ),
                    'tip_effect'    => array(
                        'show'          => array(
                            'effect'        => 'slide',
                            'duration'      => '500',
                            'event'         => 'mouseover',
                        ),
                        'hide'      => array(
                            'effect'    => 'slide',
                            'duration'  => '500',
                            'event'     => 'click mouseleave',
                        ),
                    ),
                )
            );

            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
            $this->args['share_icons'][] = array(
                'url'   => 'https://github.com/ReduxFramework/ReduxFramework',
                'title' => 'Visit us on GitHub',
                'icon'  => 'el-icon-github'
                //'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
            );
            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'http://twitter.com/reduxframework',
                'title' => 'Follow us on Twitter',
                'icon'  => 'el-icon-twitter'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'http://www.linkedin.com/company/redux-framework',
                'title' => 'Find us on LinkedIn',
                'icon'  => 'el-icon-linkedin'
            );

            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace('-', '_', $this->args['opt_name']);
                }
                //$this->args['intro_text'] = sprintf(esc_html__('<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'outstock'), $v);
            } else {
                //$this->args['intro_text'] = esc_html__('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'outstock');
            }

            // Add content after the form.
            //$this->args['footer_text'] = esc_html__('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'outstock');
        }

    }   
    global $reduxConfig;
    $reduxConfig = new Outstock_Theme_Config();
}

/**
  Custom function for the callback referenced above
 */
if (!function_exists('redux_my_custom_field')):
    function redux_my_custom_field($field, $value) {
        print_r($field);
        echo '<br/>';
        print_r($value);
    }
endif;

/**
  Custom function for the callback validation referenced above
 * */
if (!function_exists('redux_validate_callback_function')):
    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';

        /*
          do your validation

          if(something) {
            $value = $value;
          } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
