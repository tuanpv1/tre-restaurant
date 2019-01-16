<?php

/**
 * Class WPGlobus WC
 * @package     WPGlobus-WC
 */
class WPGlobus_WC {

	/**
	 * To save the current locale to the order meta.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	const META_ORDER_LOCALE = 'order_locale';

	/**
	 * Rows-per-page. User can change it via the Screen tab.
	 */
	const ROWS_PER_PAGE_DEFAULT = 150;
	const ROWS_PER_PAGE_MIN = 1;
	const ROWS_PER_PAGE_MAX = 999;

	public static $PLUGIN_DIR_URL = '';
	public static $PLUGIN_DIR_PATH = '';


	/**
	 * Menu items position by default. It may have 2 values 'submenu' or 'mainmenu'
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $admin_menu_position = 'submenu';

	/**
	 * Parent slug for adding submenu
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $menu_parent_slug = 'edit.php?post_type=product';

	/**
	 * Name for WPGlobus WC translations page
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $page_menu = 'wpglobus-wc-translations';

	/**
	 * The resulting page's hook_suffix, or false if the user does not have the capability required.
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $admin_submenu = '';

	/**
	 * Enabled roles for access to translations page
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $enabled_roles = array();

	/**
	 * Array of table columns for Screen Options
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $table_columns = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->enabled_roles[] = 'administrator';
		/**
		 * Add standard WooCommerce role
		 * @see WC_Install::create_roles
		 */
		$this->enabled_roles[] = 'shop_manager';

		if ( is_admin() ) {

			add_action( 'admin_init', array( $this, 'setup_table_columns' ) );

			add_action( 'wp_ajax_' . __CLASS__ . '_process_ajax', array( $this, 'on_process_ajax' ) );

			add_action( 'admin_menu', array(
				$this,
				'on_admin_menu'
			) );

			add_filter( 'set-screen-option', array(
				$this,
				'set_rows_per_page'
			), 10, 3 );

			add_filter( "manage_product_page_{$this->page_menu}_columns", array(
				$this,
				'set_option_columns'
			), 10, 3 );

			if ( ! isset( $_GET['wpglobus'] ) || 'off' !== $_GET['wpglobus'] ) {

				add_action( 'admin_print_scripts', array(
					$this,
					'on_admin_scripts'
				) );

				if ( WPGlobus::Config()->builder->is_builder_page() ) {
					/**
					 * @since 3.5
					 */
					// @todo add doc.
				} else {
					
					add_action( 'edit_form_after_editor', array(
						$this,
						'on_add_excerpt_editors'
					), 11 );
				
					add_filter( 'wpglobus_save_post_data', array(
						$this,
						'on_tag_excerpt'
					), 10, 3 );
					
				}

			}

			// is_admin()
		}
		else {
			add_action( 'wp_enqueue_scripts', array(
				$this,
				'enqueue_frontend_scripts'
			) );

			add_action( 'wp_loaded', array(
				$this,
				'load_shop_custom_slug_class'
			) );


		}
	}

	/**
	 * Load the Shop Custom Slug class only if the WPGlobus Plus "Slug" module active.
	 * @since 1.7.0
	 */
	public function load_shop_custom_slug_class() {
		if ( class_exists( 'WPGlobusPlus_Slug', false ) ) {
			require_once dirname(__FILE__) . '/class-wpglobus-wc-shop-custom-slug.php';
			new WPGlobus_WC_Shop_Custom_Slug();
		}
	}

	/**
	 *
	 */
	public function setup_table_columns() {

		/**
		 * @see WPGlobus_WC_Translations_table::$table_fields
		 */
		$this->table_columns = array(
			'thumbnail' => esc_html__( 'Thumbnail', 'woocommerce' ),
			'taxonomy'  => esc_html__( 'Taxonomy', 'woocommerce-wpglobus' ),
			'slug'      => esc_html__( 'Term Slug', 'woocommerce-wpglobus' ),
			'term_id'   => esc_html__( 'Term ID', 'woocommerce-wpglobus' ),
			'source'    => esc_html__( 'Source', 'woocommerce-wpglobus' ),
			//				'singular' => 'Singular'
		);

		/**
		 * Filter the array of table columns.
		 * @since 1.0.0
		 *
		 * @param array $table_columns Array of table columns.
		 */
		$this->table_columns = apply_filters( 'wpglobus_wc_table_columns', $this->table_columns );

	}

	/**
	 * Enqueue front-end scripts.
	 *
	 * @since 1.6.0
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wpglobus-wc-frontend',
			WPGlobus_WC::$PLUGIN_DIR_URL .
			'assets/js/frontend/wpglobus-wc-frontend' .
			WPGlobus::SCRIPT_SUFFIX() . '.js',
			array( 'jquery' ),
			WOOCOMMERCE_WPGLOBUS_VERSION,
			true
		);
	}

	/**
	 * Ajax handler.
	 * Works, for example, on the Products-Translate page (`save_term` case).
	 * @since 1.0.0
	 * @return void
	 */
	public function on_process_ajax() {
		$order = $_POST['order'];

		$result = array();
		switch ( $order['order'] ) :
			case 'save_meta' :

				$meta = get_term_meta( $order['id'], 'wpglobus_wc_' . $order['meta'], true );

				$meta[ $order['language'] ] = htmlentities2( $order['value'] );

				update_term_meta( $order['id'], 'wpglobus_wc_' . $order['meta'], $meta );

				break;
			case 'save_term' :
				if ( 'product' === $order['wc_type'] || 'product_child' === $order['wc_type'] ) {

					remove_filter( 'get_term', array( 'WPGlobus_Filters', 'filter__get_term' ), 0 );

					$term = get_term_by( 'id', $order['id'], $order['taxonomy'] );

					$pieces = array();
					foreach ( WPGlobus::Config()->enabled_languages as $language ) {
						$return              =
							$language === WPGlobus::Config()->default_language ? WPGlobus::RETURN_IN_DEFAULT_LANGUAGE : WPGlobus::RETURN_EMPTY;
						$pieces[ $language ] = WPGlobus_Core::text_filter( $term->name, $language, $return );
					}

					$new_name = '';
					foreach ( $pieces as $l => $piece ) {
						if ( $l === $order['language'] ) {
							$piece = trim( $order['value'] );
						}
						if ( ! empty( $piece ) ) {
							$new_name .= WPGlobus::add_locale_marks( $piece, $l );
						}
					}

					$update = wp_update_term( $order['id'], $order['taxonomy'], array( 'name' => $new_name ) );

					if ( is_wp_error( $update ) ) {
						$result['error']   = true;
						$result['message'] = esc_html__( 'Error updating `product_cat`', 'woocommerce-wpglobus' );
					} else {
						$result['id']     = $order['id'];
						$result['source'] = $new_name;
						$result['parent'] = $order['wc_type'] . '-' . $order['id'];
					}

				} elseif ( 'product_attribute' === $order['wc_type'] && 'product_attribute' !== $order['taxonomy'] ) {

					remove_filter( 'get_term', array( 'WPGlobus_Filters', 'filter__get_term' ), 0 );

					$term = get_term_by( 'id', $order['id'], $order['taxonomy'] );

					$pieces = array();
					foreach ( WPGlobus::Config()->enabled_languages as $language ) {
						$return              =
							$language === WPGlobus::Config()->default_language ? WPGlobus::RETURN_IN_DEFAULT_LANGUAGE : WPGlobus::RETURN_EMPTY;
						$pieces[ $language ] = WPGlobus_Core::text_filter( $term->name, $language, $return );
					}

					$new_name = '';
					foreach ( $pieces as $l => $piece ) {
						if ( $l === $order['language'] ) {
							$piece = trim( $order['value'] );
						}
						if ( ! empty( $piece ) ) {
							$new_name .= WPGlobus::add_locale_marks( $piece, $l );
						}
					}

					$update = wp_update_term( $order['id'], $order['taxonomy'], array( 'name' => $new_name ) );

					if ( is_wp_error( $update ) ) {
						$result['error']   = true;
						$result['message'] = esc_html__( 'Attribute term update error', 'woocommerce-wpglobus' );
					} else {
						$result['id']     = $order['id'];
						$result['source'] = $new_name;
						$result['parent'] = $order['wc_type'] . '-' . $order['id'];
					}

				} elseif ( 'product_attribute' === $order['wc_type'] && 'product_attribute' === $order['taxonomy'] ) {

					global $wpdb;

					$label = WPGlobus_WC_Utils::attribute_label_by( 'id', $order['id'] );

					$pieces = array();
					foreach ( WPGlobus::Config()->enabled_languages as $language ) {
						$return              =
							$language === WPGlobus::Config()->default_language ? WPGlobus::RETURN_IN_DEFAULT_LANGUAGE : WPGlobus::RETURN_EMPTY;
						$pieces[ $language ] = WPGlobus_Core::text_filter( $label, $language, $return );
					}

					$new_label = '';
					foreach ( $pieces as $l => $piece ) {
						if ( $l === $order['language'] ) {
							$piece = trim( $order['value'] );
						}
						if ( ! empty( $piece ) ) {
							$new_label .= WPGlobus::add_locale_marks( $piece, $l );
						}
					}

					$query =
						$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}woocommerce_attribute_taxonomies SET attribute_label = '%s' WHERE attribute_id = '%d'", $new_label, $order['id'] ) );

					if ( $query ) {
						$result['source'] = $new_label;
						$result['id']     = $order['id'];
						$result['parent'] = $order['wc_type'] . '-' . $order['id'];
					} else {
						$result['error']   = true;
						$result['message'] = esc_html__( 'Attribute term update error', 'woocommerce-wpglobus' );
					}

				}
				break;
			case 'save_description' :

				if ( ! isset( $order['id'] ) ) {
					$result['result']  = 'error';
					$result['message'] = esc_html__( 'Internal Error:', 'woocommerce-wpglobus' )
					                     . 'Undefined $order[id]';
					break;
				}

				$taxonomy = '';
				if ( 'product_attribute' === $order['data-wc-type'] ) {
					$taxonomy = $order['taxonomy'];
				} elseif ( 'product' === $order['data-wc-type'] || 'product_child' === $order['data-wc-type'] ) {
					$taxonomy = 'product_cat';
				}

				if ( empty( $taxonomy ) ) {
					$result['result']  = 'error';
					$result['message'] = esc_html__( 'Undefined taxonomy', 'woocommerce-wpglobus' );
					break;
				}

				remove_filter( 'get_term', array( 'WPGlobus_Filters', 'filter__get_term' ), 0 );

				$term = get_term( $order['id'], $taxonomy );

				$pieces = array();
				foreach ( WPGlobus::Config()->enabled_languages as $language ) {

					$return      =
						$language === WPGlobus::Config()->default_language ? WPGlobus::RETURN_IN_DEFAULT_LANGUAGE : WPGlobus::RETURN_EMPTY;
					$description = WPGlobus_Core::text_filter( $term->description, $language, $return );
					$description = trim( $description );

					if ( ! empty( $description ) ) {
						$pieces[ $language ] = WPGlobus::add_locale_marks( $description, $language );
					}

				}

				$ids    = array();
				$values = array();
				/** @var array[] $order */
				foreach ( $order['field'] as $field ) {
					$value          = trim( $field['value'] );
					$ids[]          = 'description-' . $field['language'] . $order['id'];
					$key            = 'description-' . $field['language'] . $order['id'];
					$values[ $key ] = $value;
					if ( empty( $value ) ) {
						unset( $pieces[ $field['language'] ] );
					} else {
						$pieces[ $field['language'] ] = WPGlobus::add_locale_marks( $value, $field['language'] );
					}
				}

				$new_description = '';
				foreach ( $pieces as $v ) {
					$new_description .= $v;
				}

				$res = wp_update_term( $order['id'], $taxonomy, array(
					'description' => $new_description,
					'name'        => $term->name
				) );

				if ( is_wp_error( $res ) ) {
					$result['result']  = 'error';
					$result['message'] = $res;
				} else {
					$result['result'] = 'ok';
					$result['id']     = $order['id'];
					$result['ids']    = $ids;
					$result['value']  = $values;
				}
				break;
		endswitch;

		wp_send_json( $result );
	}

	/**
	 * Tag post excerpt
	 * @since 1.0.0
	 *
	 * @param array $data
	 * @param array $postarr
	 * @param bool  $devmode
	 *
	 * @return array
	 */
	public function on_tag_excerpt( $data, $postarr, $devmode ) {
		if ( $devmode ) {
			return $data;
		}

		if ( 'product' !== $data['post_type'] ) {
			return $data;
		}

		/**
		 * WooCommerce 3+ code changed so that we are arriving here twice at the post save.
		 * At the second time, we do not have the `wpglobus_excerpt_{language}` in `$postarr`.
		 * @since 3.0.1
		 */
		if ( ! isset( $postarr[ 'wpglobus_excerpt_' . WPGlobus::Config()->default_language ] ) ) {
			return $data;
		}

		$excerpt_default = '';
		$data['post_excerpt'] = '';

		foreach ( WPGlobus::Config()->enabled_languages as $language ) :
			/**
			 * Join post excerpt for enabled languages
			 */
			if ( $language === WPGlobus::Config()->default_language ) {
				$excerpt_default =
					empty( $postarr[ 'wpglobus_excerpt_' . $language ] ) ? '' : trim( $postarr[ 'wpglobus_excerpt_' . $language ] );

			} else {
				$excerpt =
					empty( $postarr[ 'wpglobus_excerpt_' . $language ] ) ? '' : trim( $postarr[ 'wpglobus_excerpt_' . $language ] );

				if ( ! empty( $excerpt ) ) {
					$data['post_excerpt'] .= WPGlobus::add_locale_marks( $excerpt, $language );
				}
			}
		endforeach;

		// If we have only the default language, we do not need the locale marks.
		if ( '' !== $excerpt_default ) {
			if ( '' === $data['post_excerpt'] ) {
				$data['post_excerpt'] = $excerpt_default;
			} else {
				$data['post_excerpt'] = WPGlobus::add_locale_marks( $excerpt_default, WPGlobus::Config()->default_language ) . $data['post_excerpt']  ;
			}
		}

		return $data;

	}

	/**
	 * @return string
	 * @since 1.2.0
	 */
	protected function _page_action() {
		/**
		 * Current `screen` gives most of the information about where we are now
		 */
		$screen = get_current_screen();

		/**
		 * `post_type` must be taken from the global `post` and not from the `screen`:
		 * `screen` sets post type as "product" for the attribute edit, too.
		 * We need it only if it's product edit.
		 */
		$post      = get_post();
		$post_type = ( ! empty( $post->post_type ) ? $post->post_type : '' );


		/**
		 * That's not in the `screen`, so we need to check the request
		 */
		$is_edit = (
			// &edit=1
			! empty( $_GET['edit'] ) or
			// &action=edit
			( ! empty( $_GET['action'] ) && $_GET['action'] === 'edit' )
		);

		if ( $post_type === 'product' && $is_edit ) {
			/**
			 * http://qa.wpglobus.com/wp/wp-admin/post.php?post=726&action=edit
			 */
			return 'post-edit';
		}

		if ( 'product' === $post_type && 'add' === $screen->action ) {
			/**
			 * since 1.3.0
			 * for case /wp-admin/post-new.php?post_type=product
			 */
			return 'post-edit';
		}

		if ( $screen->id === 'product_page_product_attributes' && $is_edit ) {
			/**
			 * since WooCommerce v.2.3
			 * http://qa.wpglobus.com/wp/wp-admin/edit.php?post_type=product&page=product_attributes&edit=1
			 */
			return 'product-attributes';
		}

		if ( $screen->id === 'product_page_wpglobus-wc-translations' ) {
			/**
			 * http://qa.wpglobus.com/wp/wp-admin/edit.php?post_type=product&page=wpglobus-wc-translations
			 * Alternative way to check:
			 * if ( $pagenow === 'edit.php' && $plugin_page === $this->page_menu )
			 */
			wp_enqueue_script( 'jquery-effects-highlight' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			return 'translations';

		}

		if ( $screen->id === 'woocommerce_page_wc-settings' ) {
			/**
			 * WooCommerce Settings pages
			 * http://qa.wpglobus.com/wp/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_gateway_cheque
			 * For fine tuning, can use:
			 * $_GET['tab'] = checkout
			 * $_GET['section'] = wc_gateway_cheque
			 */
			return 'wc-settings';
		}

		/**
		 * @todo Remove this after WC 2.4 released and tested
		 */
		$pagenow     = WPGlobus_WP::pagenow();
		$plugin_page = WPGlobus_WP::plugin_page();
		if ( $pagenow === 'admin.php' && $plugin_page === 'product_attributes' ) {
			/**
			 * WooCommerce 2.2
			 */
			return 'product-attributes';
		}

		/**
		 * Default: we did not recognize the place, so we will not load the scripts
		 */

		return '';

	}

	/**
	 * Enqueue WC-specific admin scripts.
	 * Note that edit product categories/tags is handled by @see WPGlobus::on_admin_scripts()
	 */
	public function on_admin_scripts() {

		$page_action = $this->_page_action();
		if ( ! $page_action ) {
			return;
		}

		/**
		 * Get User's preferences regarding hidden columns and prepare the list of columns to hide.
		 */
		$hidden_columns = array();
		foreach ( get_hidden_columns( get_current_screen() ) as $_column ) {
			if ( ! empty( $this->table_columns[ $_column ] ) ) {
				$hidden_columns[] = $_column;
			}
		}
		unset( $_column );

		$language_name = array();
		foreach ( WPGlobus::Config()->enabled_languages as $_language ) {
			$language_name[ $_language ] = WPGlobus::Config()->en_language_name[ $_language ];
		}
		unset( $_language );

		/**
		 * Get User's settings for closedpostboxes_product
		 * @since 1.2.0
		 */
		$user = wp_get_current_user();
		$opt = get_user_option( 'closedpostboxes_product', $user->ID );
		if ( is_array( $opt ) && in_array( 'wpglobus-wc-excerpt-tabs', $opt) ) {
			$box_short_desc = 'hidden';
		} else {
			$box_short_desc = '';
		}

		if ( WPGlobus::Config()->builder->is_builder_page() ) {
			/**
			 * @since 3.5
			 */
			wp_register_script(
				'wpglobus-wc',
				WPGlobus_WC::$PLUGIN_DIR_URL .
				'assets/builders/js/admin/wpglobus-wc-builders' .
				WPGlobus::SCRIPT_SUFFIX() . '.js',
				array( 'jquery', 'jquery-ui-tabs' ),
				WOOCOMMERCE_WPGLOBUS_VERSION,
				true
			);
			wp_enqueue_script( 'wpglobus-wc' );
			
			wp_localize_script(
				'wpglobus-wc',
				'WPGlobusWC',
				array(
					'version'             => WOOCOMMERCE_WPGLOBUS_VERSION,
					'woocommerce_version' => WC()->version,
					'page'                => $page_action,
					'default_language'    => WPGlobus::Config()->default_language,
					'language'            => WPGlobus::Config()->builder->get_language(),
					'enabled_languages'   => WPGlobus::Config()->enabled_languages,
					'en_language_name'    => $language_name,					
				)
			);
			
		} else {
		
			wp_enqueue_script(
				'wpglobus-wc',
				WPGlobus_WC::$PLUGIN_DIR_URL .
				'assets/js/admin/wpglobus-wc' .
				WPGlobus::SCRIPT_SUFFIX() . '.js',
				array( 'jquery', 'jquery-ui-tabs' ),
				WOOCOMMERCE_WPGLOBUS_VERSION,
				true
			);
	
			wp_localize_script(
				'wpglobus-wc',
				'WPGlobusWC',
				array(
					'version'             => WOOCOMMERCE_WPGLOBUS_VERSION,
					'woocommerce_version' => WC()->version,
					'page'                => $page_action,
					#'excerpt_template' => $this->get_template(),
					'locale_tag_start'    => WPGlobus::LOCALE_TAG_START,
					'locale_tag_end'      => WPGlobus::LOCALE_TAG_END,
					'default_language'    => WPGlobus::Config()->default_language,
					'language'            => WPGlobus::Config()->language,
					'enabled_languages'   => WPGlobus::Config()->enabled_languages,
					'en_language_name'    => $language_name,
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'parentClass'         => __CLASS__,
					'process_ajax'        => __CLASS__ . '_process_ajax',
					'hidden_columns'      => $hidden_columns,
					'box_short_desc' 	  => $box_short_desc
				)
			);
			
		}

	}


	/**
	 * Add mainmenu or submenu
	 * @since 1.0.0
	 * @return void
	 */
	public function on_admin_menu() {

		/**
		 * Filter the array of enabled roles.
		 * @since 1.0.0
		 *
		 * @param array $enabled_roles Array of enabled roles.
		 */
		$this->enabled_roles = apply_filters( 'wpglobus_wc_enabled_roles', $this->enabled_roles );

		if ( 'submenu' === $this->admin_menu_position ) {

			/**
			 * PHPStorm bug: complains about this if placed inline
			 * @var callable $function
			 */
			$function = array( $this, 'wc_translation_table' );

			$this->admin_submenu = add_submenu_page(
				$this->menu_parent_slug,
				/** Page title */
				__( 'Translate', 'woocommerce-wpglobus' ),
				/** Menu title (under Products) */
				'<span class="dashicons dashicons-translation" style="vertical-align:middle"></span>' .
				__( 'Translate', 'woocommerce-wpglobus' ),
				$this->get_enabled_role(),
				$this->page_menu,
				$function
			);

			add_action( "load-{$this->admin_submenu}", array( $this, 'screen_options' ) );

		}

	}

	/**
	 * Set screen options
	 * @see   set_screen_options
	 * @since 1.0.0
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 *
	 * @return bool|int
	 */
	public function set_rows_per_page( $status, $option, $value ) {

		if ( 'wpglobus_wc_per_page' === $option ) {
			$per_page = (int) $value;
			if ( $per_page >= self::ROWS_PER_PAGE_MIN && $per_page <= self::ROWS_PER_PAGE_MAX ) {
				$status = $per_page;
			}
		}

		return $status;

	}

	/**
	 * Add screen options
	 * @since 1.0.0
	 * @return void
	 */
	public function screen_options() {

		$args = array(
			'label'   => __( 'Records per page', 'woocommerce-wpglobus' ),
			'default' => self::ROWS_PER_PAGE_DEFAULT,
			'option'  => 'wpglobus_wc_per_page'
		);
		add_screen_option( 'per_page', $args );

	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_translation_table() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Translate WooCommerce Taxonomies', 'woocommerce-wpglobus' ); ?></h2>
			<?php
			new WPGlobus_WC_Translations_table();
			?>
		</div>
	<?php
	}

	/**
	 * Emulate excerpt metabox with wp_editors
	 * @see   action edit_form_after_editor in wp-admin\edit-form-advanced.php:542
	 * @since 1.0.0
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function on_add_excerpt_editors( $post ) {

		if ( 'product' !== $post->post_type ) {
			return;
		}

		$excerpt = htmlspecialchars_decode( $post->post_excerpt );

		$settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => array( 'buttons' => 'em,strong,link' ),
			'tinymce'       => array(
				'resize'                  => true,
				'wp_autoresize_on'        => true,
				'add_unload_trigger'      => false,
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_css'    => '<style>#wpglobus-wc-excerpt-tabs .wp-editor-area{height:175px; width:100%;}</style>'
		);

		?>

		<div id="wpglobus-wc-excerpt-tabs" class="postbox">
			<div class="handlediv" title="<?php
            esc_attr_e( 'Click to toggle' );
            ?>"><br></div>

			<h3 class="hndle wpglobus-hndle">
				<span><?php _e( 'Product Short Description', 'woocommerce-wpglobus' ); ?></span></h3>
			<ul class="wpglobus-tabs-list">    <?php
				foreach ( WPGlobus::Config()->enabled_languages as $language ) { ?>
					<li class="wpglobus-excerpt-tab" id="wpglobus-excerpt-tab-<?php echo $language; ?>"><a
							href="#excerpt-tab-<?php echo $language; ?>"><?php echo WPGlobus::Config()->en_language_name[ $language ]; ?></a>
					</li> <?php
				} ?>
			</ul>
			<div class="clear"></div>    <?php

			foreach ( WPGlobus::Config()->enabled_languages as $language ) :
				$settings['textarea_name'] = 'wpglobus_excerpt' . "_$language"; ?>
				<div id="excerpt-tab-<?php echo $language; ?>" class="wpglobus-excerpt-editor">    <?php
					wp_editor( WPGlobus_Core::text_filter( $excerpt, $language, WPGlobus::RETURN_EMPTY ), 'wpglobus_excerpt_' . $language, $settings ); ?>
				</div> <?php
			endforeach; ?>
		</div> <!-- #wpglobus-wc-excerpt-tabs --> <?php
	}

	/**
	 *
	 */
	public function get_menu_parent_slug() {
		return $this->menu_parent_slug;
	}

	/**
	 * @return string
	 */
	public function get_page_menu() {
		return $this->page_menu;
	}

	/**
	 * Get enabled role
	 * @since          1.0.0
	 *
	 * @param string $role
	 *
	 * @return string|bool
	 */
	public function get_enabled_role( $role = '' ) {

		global $current_user;

		if ( empty( $role ) ) {
			foreach ( $current_user->roles as $_role ) {
				if ( in_array( $_role, $this->enabled_roles ) ) {
					return $_role;
				}
			}

			return false;
		}

		if ( in_array( $role, $this->enabled_roles ) ) {
			return $role;
		}

		return false;

	}

	/**
	 * @todo unused function since 1.0.0
	 *
	 * Get template
	 * @since 1.0.0
	 * @return string
	 */
	public function get_template() {

		$config = WPGlobus::Config();

		$post = get_post();

		$settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => array( 'buttons' => 'em,strong,link' ),
			'tinymce'       => array(
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_css'    => '<style>#wpglobus-wc-excerpt-tabs .wp-editor-area{height:175px; width:100%;}</style>'
		);
		//$settings = apply_filters( 'woocommerce_product_short_description_editor_settings', $settings );

		$excerpt = htmlspecialchars_decode( $post->post_excerpt );

		ob_start(); ?>
		<div id="wpglobus-wc-excerpt-tabs">
			<ul>    <?php
				foreach ( $config->enabled_languages as $language ) { ?>
					<li id="wpglobus-excerpt-tab-<?php echo $language; ?>"><a
							href="#excerpt-tab-<?php echo $language; ?>"><?php echo $config->en_language_name[ $language ]; ?></a>
					</li> <?php
				} ?>
			</ul>
			<div class="clear"></div>
			<?php

			foreach ( $config->enabled_languages as $language ) {
				$settings['textarea_name'] = 'excerpt' . "-$language"; ?>
				<div id="excerpt-tab-<?php echo $language; ?>" class="">
					<?php
					wp_editor( WPGlobus_Core::text_filter( $excerpt, $language ), 'excerpt-' . $language, $settings );
					?>
				</div>
			<?php
			} ?>
		</div> <!--  #wpglobus-wc-excerpt-tabs"	 --> <?php

		return ob_get_clean();

	}

	/**
	 * Set option columns
	 * @since 1.0.0
	 * @return array
	 */
	public function set_option_columns() {
		return $this->table_columns;
	}

}

# --- EOF
