<?php
/**
 * File: class-wpglobus-wc-translations.php
 *
 * @package     WPGlobus\WC\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table', false ) ) {
	/* @noinspection PhpIncludeInspection */
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Admin interface to WooCommerce taxonomy translations: all in one place.
 */
class WPGlobus_WC_Translations_table extends WP_List_Table {

	/**
	 * Table data
	 *
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	public $data = array();

	/**
	 * Use for pagination
	 *
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $found_data = array();

	/**
	 * Contains table fields
	 *
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $table_fields = array();

	/**
	 * First table column is checkbox?
	 *
	 * @access private
	 * @since  1.0.0
	 * @var boolean
	 */
	private $first_column_is_checkbox = false;

	/**
	 * Get taxonomies
	 *
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $taxes = array();

	private $all_taxes = array();

	/**
	 *
	 */
	private $default_language = '';

	/**
	 * Contains language pair for translation table, default language + one national language
	 *
	 * @access private
	 * @since  1.0.0
	 * @var array
	 */
	private $languages = array();

	/**
	 * Default field sorting orderby
	 *
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $sort_orderby = 'slug';

	/**
	 * Default field sorting order
	 *
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $sort_order = 'asc';

	/**
	 * Taxonomy filter
	 *
	 * @access private
	 * @since  1.0.0
	 * @var string
	 */
	private $taxonomy_filter = '0';

	/**
	 *  Constructor.
	 *
	 * @param bool   $get_data_only
	 * @param string $language
	 */
	public function __construct( $get_data_only = false, $language = '' ) {

		if ( isset( $_POST['wpglobus_tax_filter'] ) ) {
			$this->taxonomy_filter = $_POST['wpglobus_tax_filter'];
			set_transient( 'wpglobus_wc_taxonomy_filter', $this->taxonomy_filter );
		} else {
			$tf = get_transient( 'wpglobus_wc_taxonomy_filter' );
			if ( ! empty( $tf ) ) {
				$this->taxonomy_filter = $tf;
			}
		}

		$this->default_language = WPGlobus::Config()->default_language;

		$this->all_taxes = $this->get_taxonomies( 'names', true );

		$this->taxes = $this->get_taxonomies();

		if ( $this->get_language_pair( $language ) ) {

			$this->set_orderby();
			$this->set_order();

			if ( ! defined( 'DOING_AJAX' ) ) {

				$this->get_data();

				if ( $get_data_only ) {
					return;
				}

				add_action( 'admin_footer', array(
					$this,
					'on_admin_footer'
				) );

				parent::__construct( array(
					'singular' => 'item',    // singular name of the listed records
					'plural'   => 'items',    // plural name of the listed records
					'ajax'     => true                    // does this table support ajax?
				) );

				$this->display_translations_table();
			}

		} else {

			$this->about_screen();

		}

	}

	/**
	 * Output the About WPGlobus WC Translations screen.
	 */
	public function about_screen() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'About WPGlobus WC Translations', 'woocommerce-wpglobus' ); ?></h2>
			<p><?php esc_html_e( 'Your need to enable two or more languages to use this tool.', 'woocommerce-wpglobus' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Set field sorting orderby
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_orderby() {
		if ( ! empty( $_GET['orderby'] ) ) {
			$this->sort_orderby = $_GET['orderby'];
		}
	}

	/**
	 * Set field sorting order
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_order() {
		if ( ! empty( $_GET['order'] ) ) {
			$this->sort_order = $_GET['order'];
		}
	}

	/**
	 * Get language pair for translation table, default language + one national language
	 * @since 1.0.0
	 *
	 * @param string $language
	 *
	 * @return bool
	 */
	public function get_language_pair( $language = '' ) {

		$this->languages   = array();
		$this->languages[] = $this->default_language;

		if ( ! empty( $language ) ) {
			$this->languages[] = $language;

			return ( 2 === count( $this->languages ) );
		}

		$second_language = get_transient( 'wpglobus_wc_second_language' );

		if ( ! empty( $_POST['wpglobus_wc_language'] ) ) {

			foreach ( WPGlobus::Config()->en_language_name as $_language => $_name ) {
				if ( $_name === $_POST['wpglobus_wc_language'] ) {
					$this->languages[] = $_language;
					break;
				}
			}

		} elseif ( ! empty( $_POST['wpglobus_wc_term_language'] ) ) {

			$this->languages[] = $_POST['wpglobus_wc_term_language'];

		} elseif ( ! empty( $second_language ) ) {

			$this->languages[] = $second_language;

		} else {

			if ( isset( WPGlobus::Config()->open_languages[1] ) ) {
				$this->languages[] = WPGlobus::Config()->open_languages[1];
			}

		}

		if ( 2 === count( $this->languages ) ) {
			set_transient( 'wpglobus_wc_second_language', $this->languages[1] );

			return true;
		}

		return false;

	}

	public function display_translations_table() {
		$this->prepare_items();

		/** Output float column header for  */ ?>
		<div id="wpglobus-table-wrapper" class="table-wrap wrap" data-default-language="<?php echo $this->languages[0]; ?>" data-second-language="<?php echo $this->languages[1]; ?>">
			<form method="post">
				<input type="hidden" name="translations-page" />
				<table class="wp-list-table widefat fixed float-header">
					<thead>
					<tr>
						<?php $this->print_column_headers( false ); ?>
					</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				<?php $this->search_box( esc_html__( 'Search', 'woocommerce-wpglobus' ), 'tax' );
				$this->display(); ?>
			</form>
		</div>    <!-- .wrap -->    <?php

	}

	/**
	 * Admin footer callback
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function on_admin_footer() {

		/**
		 * "Edit Description" dialog form
		 */
		?>
		<div id="wpglobus-wc-edit-description-form"
		     title="<?php esc_attr_e( 'Edit Description:', 'woocommerce-wpglobus' ); ?> ">
			<form id="edit-description" style="height:90%;">
				<fieldset style="height:inherit;">
					<label for="description">
						<?php esc_html_e( 'Default Language', 'woocommerce-wpglobus' ); ?>
					</label>
					<textarea style="width:100%;height:50%;" name="description" id="description"
					          class="wpglobus_form_description textarea ui-widget-content"></textarea>
					<label for="description_2">
						<?php esc_html_e( 'Translation', 'woocommerce-wpglobus' ); ?>
					</label>
					<textarea style="width:100%;height:50%;" name="description_2" id="description_2"
					          class="wpglobus_form_description textarea ui-widget-content"></textarea>
				</fieldset>
			</form>
		</div>
		<?php

	}

	public function process_bulk_action() {
	}


	public function process_row_action() {
	}

	/**
	 * Data table filling
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_data() {

		$this->table_fields = array(
			'thumbnail' => array(
				'caption'      => __( 'Thumbnail', 'woocommerce' ),
				'sortable'     => false,
				'translatable' => false
			),
			'taxonomy'  => array(
				'caption'      => esc_html__( 'Taxonomy', 'woocommerce-wpglobus' ),
				'sortable'     => false,
				'translatable' => false
			),
			'slug'      => array(
				'caption'      => esc_html__( 'Term Slug', 'woocommerce-wpglobus' ),
				'sortable'     => false,
				'translatable' => false
			),
			'term_id'   => array(
				'caption'      => esc_html__( 'Term ID', 'woocommerce-wpglobus' ),
				'sortable'     => false,
				'translatable' => false
			),
			'source'    => array(
				'caption'      => esc_html__( 'Source', 'woocommerce-wpglobus' ),
				'sortable'     => false,
				'translatable' => false
			)
		);


		/**
		 * Add national language column
		 */
		/** @var array $languages */
		foreach ( $this->languages as $language ) {
			$this->table_fields[ $language ] = array(
				'caption'      => WPGlobus::Config()->en_language_name[ $language ],
				'sortable'     => false,
				'translatable' => true
			);
		}

		/**
		 * Filter the argument used to add custom table fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $table_fields An array of table fields.
		 */
		$this->table_fields = apply_filters( 'wpglobus_wc_table_fields', $this->table_fields );

		$search = '';
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = mb_strtolower( $_REQUEST['s'] );
		}

		if ( '0' !== $this->taxonomy_filter ) {
			$this->taxes = array( $this->taxonomy_filter );
		}

		remove_filter( 'get_terms', array( 'WPGlobus_Filters', 'filter__get_terms' ), 11 );

		foreach ( $this->taxes as $tax ) {

			if ( 'slug' === $this->sort_orderby ) {

				$tt         = WPGlobus_WC_Utils::taxonomy_type( $tax );
				$show_title = false;

				if ( 'product_attribute' === $tt['type'] || 'product_child' === $tt['type'] ) {
					$show_title = true;
				}

				if ( 'product_child' === $tt['type'] ) {

					$term = get_term_by( 'slug', $tax, 'product_cat' );

					$parent_terms = get_terms( 'product_cat', array(
						'parent'     => 0,
						'hide_empty' => false,
						'slug'       => $term->slug,
						'orderby'    => $this->sort_orderby,
						'order'      => $this->sort_order
					) );

				} else {

					$parent_terms = get_terms( $tax, array(
						'parent'     => 0,
						'hide_empty' => false,
						'orderby'    => $this->sort_orderby,
						'order'      => $this->sort_order
					) );

				}

				if ( ! is_wp_error( $parent_terms ) ) {

					/** @var array $parent_terms */

					foreach ( $parent_terms as $parent_term ) {

						/**
						 * Get parent terms
						 * @todo Check what happens with $singular
						 */

//						$singular = (array) get_term_meta( $parent_term->term_id, 'wpglobus_wc_singular', true );

						if ( 'product' === $tt['type'] ) {

							/** @noinspection MissingOrEmptyGroupStatementInspection */
							if ( ! empty( $search ) && ! $this->search( $search, $parent_term ) ) {
								//continue;
							} else {

								/**
								 * Add row with product category title
								 */
								$row          = array();
								$row['type']  = 'header';
								$row['text']  = '<h4 style="margin:0;">' . $tt['title'] . ': ' . WPGlobus_Core::text_filter( $parent_term->name, $this->default_language ) . '</h4>';
								$this->data[] = $row;

								/**
								 * Add product row which is parent immediately after title
								 */
								$row                     = array();
								$row['type']             = 'standard';
								$row['counting']         = true;
								$row['wc_type']          = 'product';
								$row['ID']               = $parent_term->term_id;
								$row['taxonomy']         = $tax;
								$row['slug']             = $parent_term->slug;
								$row['term_id']          = $parent_term->term_id;
								$row['source']           = $parent_term->name;
								$row['description']      = $parent_term->description;
								$row['default_language'] = $this->languages[0];
								$row['language']         = $this->languages[1];

								foreach ( $this->languages as $language ) {
									$row[ $language ]                  = WPGlobus_Core::text_filter( $parent_term->name, $language, WPGlobus::RETURN_EMPTY );
									$row[ 'description-' . $language ] = WPGlobus_Core::text_filter( $parent_term->description, $language, WPGlobus::RETURN_EMPTY );
								}

								$this->data[] = $row;
							}

						} elseif ( $show_title && 'product_attribute' === $tt['type'] ) {

							/** @noinspection MissingOrEmptyGroupStatementInspection */
							if ( ! empty( $search ) && ! $this->search( $search, $tt ) ) {
								//continue;
							} else {

//								$singular = (array) get_term_meta( $tt['attribute_id'], 'wpglobus_wc_singular', true );

								/**
								 * Add row with product_attribute title
								 */
								$row          = array();
								$row['type']  = 'header';
								$row['text']  = '<h4 style="margin:0;">' . $tt['title'] . ': ' . $tt['name'] . '</h4>';
								$this->data[] = $row;
								$show_title   = false;

								/**
								 * Add row with product_attribute ( one for all product_attribute terms )
								 */
								$row                     = array();
								$row['type']             = 'standard';
								$row['counting']         = true;
								$row['wc_type']          = 'product_attribute';
								$row['ID']               = $tt['attribute_id'];
								$row['taxonomy']         = $tt['type'];
								$row['slug']             = $tt['name'];
								$row['term_id']          = $tt['attribute_id'];
								$row['source']           = WPGlobus_WC_Utils::attribute_label_by( 'id', $tt['attribute_id'] );
								$row['description']      = false; // don't exists description for woocommerce attribute
								$row['default_language'] = $this->languages[0];
								$row['language']         = $this->languages[1];

								foreach ( $this->languages as $language ) {
									/**
									 * use $row['source'] instead of wc_attribute_label($tax) to get string with language marks
									 */
									$row[ $language ]                  = WPGlobus_Core::text_filter( $row['source'], $language, WPGlobus::RETURN_EMPTY );
									$row[ 'description-' . $language ] = false; // don't exists description for woocommerce attribute
								}
								$this->data[] = $row;

							}

						} elseif ( $show_title && 'product_child' === $tt['type'] ) {

							/** @noinspection MissingOrEmptyGroupStatementInspection */
							if ( ! empty( $search ) && ! $this->search( $search, $parent_term ) ) {
								//continue;
							} else {

								/**
								 * product_child is product_cat
								 */
								$tax = 'product_cat';

								/**
								 * Add row with product_child title
								 */
								$row          = array();
								$row['type']  = 'header';
								$row['text']  = '<h4 style="margin:0;">' . $tt['title'] . ': ' . $tt['name'] . '</h4>';
								$this->data[] = $row;
								$show_title   = false;

								/**
								 * Add row with product_child ( one for all other product_child )
								 */
								$row                     = array();
								$row['type']             = 'standard';
								$row['counting']         = true;
								$row['wc_type']          = 'product_child';
								$row['ID']               = $parent_term->term_id;
								$row['taxonomy']         = $tax;
								$row['slug']             = $parent_term->slug;
								$row['term_id']          = $parent_term->term_id;
								$row['source']           = $parent_term->name;
								$row['description']      = ''; // empty description for attribute
								$row['default_language'] = $this->languages[0];
								$row['language']         = $this->languages[1];

								foreach ( $this->languages as $language ) {
									$row[ $language ]                  = WPGlobus_Core::text_filter( $parent_term->name, $language, WPGlobus::RETURN_EMPTY );
									$row[ 'description-' . $language ] = WPGlobus_Core::text_filter( $parent_term->description, $language, WPGlobus::RETURN_EMPTY );
								}
								$this->data[] = $row;

							}

						}

						if ( 'product_attribute' === $tt['type'] ) {

							/* @noinspection IfConditionalsWithoutCurvyBracketsInspection */
							if ( ! empty( $search ) && ! $this->search( $search, $parent_term ) ) {
								//continue;
							} else {

//								$singular = (array) get_term_meta( $parent_term->term_id, 'wpglobus_wc_singular', true );

								$row                     = array();
								$row['type']             = 'standard';
								$row['counting']         = true;
								$row['wc_type']          = 'product_attribute';
								$row['ID']               = $parent_term->term_id;
								$row['taxonomy']         = $tax;
								$row['slug']             = $parent_term->slug;
								$row['term_id']          = $parent_term->term_id;
								$row['source']           = $parent_term->name;
								$row['description']      = '';
								$row['default_language'] = $this->languages[0];
								$row['language']         = $this->languages[1];

								foreach ( $this->languages as $language ) {
									$row[ $language ]                  = WPGlobus_Core::text_filter( $parent_term->name, $language, WPGlobus::RETURN_EMPTY );
									$row[ 'description-' . $language ] = WPGlobus_Core::text_filter( $parent_term->description, $language, WPGlobus::RETURN_EMPTY );
								}
								$this->data[] = $row;
							}

						}

						/**
						 * Get child terms
						 */
						$terms = get_terms( $tax, array(
							'child_of'   => $parent_term->term_id,
							'hide_empty' => false,
							'orderby'    => $this->sort_orderby,
							'order'      => $this->sort_order
						) );

						if ( ! is_wp_error( $terms ) ) {

							/** @var array $terms */

							foreach ( $terms as $term ) {

								if ( ! empty( $search ) && ! $this->search( $search, $term ) ) {
									continue;
								}

								$chain = WPGlobus_WC_Utils::get_family_chain( $parent_term, $term, $terms );

//								$singular = (array) get_term_meta( $term->term_id, 'wpglobus_wc_singular', true );

								$row             = array();
								$row['type']     = 'standard';
								$row['counting'] = true;
								$row['wc_type']  = 'product_child';
								$row['ID']       = $term->term_id;
								$row['taxonomy'] = $tax;
								#$row['slug']		 = $parent_term->slug . ' >> ' . $term->slug;
								$row['slug']             = $chain;
								$row['term_id']          = $term->term_id;
								$row['source']           = $term->name;
								$row['description']      = $term->description;
								$row['default_language'] = $this->languages[0];
								$row['language']         = $this->languages[1];

								foreach ( $this->languages as $language ) {
									$row[ $language ]                  = WPGlobus_Core::text_filter( $term->name, $language, WPGlobus::RETURN_EMPTY );
									$row[ 'description-' . $language ] = WPGlobus_Core::text_filter( $term->description, $language, WPGlobus::RETURN_EMPTY );
								}

								$this->data[] = $row;

							}
						}

					}    // end foreach $parent_term
				}

			} else {

				$terms = get_terms( $tax, array( 'hide_empty' => false ) );

				foreach ( $terms as $term ) {

					if ( ! empty( $search ) && ! $this->search( $search, $term ) ) {
						continue;
					}

//					$singular = (array) get_term_meta( $term->term_id, 'wpglobus_wc_singular', true );

					$row             = array();
					$row['type']     = 'standard';
					$row['counting'] = true;
					$row['wc_type']  = '';
					$row['ID']       = $term->term_id;
					$row['taxonomy'] = $tax;
					// $row['slug']		 = $parent_term->slug . ' >> ' . $term->slug;
					$row['slug']             = $term->slug;
					$row['term_id']          = $term->term_id;
					$row['source']           = WPGlobus_WC_Utils::attribute_label_by( $term->term_id );
					$row['default_language'] = $this->languages[0];
					$row['language']         = $this->languages[1];

					foreach ( $this->languages as $language ) {
						$row[ $language ] = WPGlobus_Core::text_filter( $term->name, $language, WPGlobus::RETURN_EMPTY );
					}

					$this->data[] = $row;

				}    // end foreach $terms

			} // end if $this->sort_orderby

		} // end foreach $this->taxes

		add_filter( 'get_terms', array( 'WPGlobus_Filters', 'filter__get_terms' ), 11 );

	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function no_items() {
		esc_html_e( 'No items found.', 'woocommerce-wpglobus' );
	}

	/**
	 * Define function for add item actions by name 'column_taxonomy'
	 *
	 * @since 1.0.0
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_taxonomy( $item ) {
		return $item['taxonomy'];
	}

	/**
	 * Define function for add item actions by name 'column_singular'
	 *
	 * @since 1.0.0
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	/**
	 * function column_singular( $item ) {
	 *
	 * if ( $this->table_fields['singular']['translatable'] ) {
	 *
	 * $id    = 'singular-' . $this->languages[1] . '-' . $item['ID'];
	 * $str   = '<input type="text" class="ajaxify wpglobus-meta wpglobus-singular ' . $id . '"
	 * data-meta="singular"
	 * data-language="' . $this->languages[1] . '"
	 * data-wc-type="' . $item['wc_type'] . '"
	 * data-taxonomy="' . $item['taxonomy'] . '"
	 * data-column="singular"
	 * data-id="' . $item['ID'] . '"
	 * id="' . $id . '"
	 * value="' . $item['singular'] . '"
	 * size="20" />';
	 * return $str;
	 *
	 * } else {
	 *
	 * return $item['singular'];
	 *
	 * }
	 *
	 * } // */

	/**
	 * Add "View" action to the list-table row.
	 * Do nothing if $actions['view'] already set.
	 *
	 * @param string[] $actions Existing actions (i.e. 'Edit')
	 * @param mixed[]  $item    Item - see column_slug()
	 *
	 * @see column_slug()
	 */
	protected function _add_view_row_action( &$actions, $item ) {
		if ( empty( $actions['view'] ) ) {
			$url_view = get_term_link( (int) $item['term_id'], $item['taxonomy'] );
			if ( ! is_wp_error( $url_view ) ) {
				$actions['view'] = '<a  target="_blank" href="' . $url_view . '">' .
                    esc_html__( 'View', 'woocommerce' ) .
				                   '</a>';
			}
		}
	}

	/**
	 * Define function for add item actions by name 'column_slug'
	 *
	 * @since 1.0.0
	 *
	 * @param  $item array
	 *
	 * @return string
	 */
	public function column_slug( $item ) {

		if ( empty( $item['wc_type'] ) ) {
			return $item['slug'];
		}

		global $wp_version;

		$page = 'term.php?';
		if ( version_compare( $wp_version, '4.5-RC1', '<' ) ) {
			$page = 'edit-tags.php?action=edit&amp;';
		}

        $msg_edit = esc_html__( 'Edit', 'woocommerce' );

		if ( 'product' === $item['wc_type'] ) {

			/** add actions for parent product category product_cat */
			//$actions['edit'] = sprintf( '<a target="_blank" href="%1s">%2s</a>', 'edit-tags.php?action=edit&amp;taxonomy=product_cat&amp;tag_ID=' . $item['term_id'] . '&amp;post_type=product',
			$actions['edit'] = sprintf( '<a target="_blank" href="%1s">%2s</a>', $page . 'taxonomy=product_cat&amp;tag_ID=' . $item['term_id'] . '&amp;post_type=product', $msg_edit );

			$this->_add_view_row_action( $actions, $item );

			return sprintf( '%1s %2s', $item['slug'], $this->row_actions( $actions ) );

		} elseif ( 'product_child' === $item['wc_type'] ) {

			/** add actions for any child category product_cat */
			$actions['edit'] = sprintf( '<a target="_blank" href="%1s">%2s</a>', $page . 'taxonomy=' . $item['taxonomy'] . '&amp;tag_ID=' . $item['term_id'] . '&amp;post_type=product', $msg_edit );

			$this->_add_view_row_action( $actions, $item );

			return sprintf( '%1s %2s', $item['slug'], $this->row_actions( $actions ) );

		} elseif ( 'product_attribute' === $item['wc_type'] ) {

			if ( 'product_attribute' === $item['taxonomy'] ) {
				/** add actions for product_attribute */
				$actions['edit'] = sprintf( '<a target="_blank" href="%1s">%2s</a>', 'admin.php?page=product_attributes&amp;edit=' . $item['term_id'], $msg_edit );

				return sprintf( '%1s %2s', $item['slug'], $this->row_actions( $actions ) );
			} else {
				/** add actions for product_attribute with taxonomy == 'pa_*'    */
				$actions['edit'] = sprintf( '<a target="_blank" href="%1s">%2s</a>', $page . 'taxonomy=' . $item['taxonomy'] . '&amp;tag_ID=' . $item['term_id'] . '&amp;post_type=product', $msg_edit );

				$this->_add_view_row_action( $actions, $item );

				return sprintf( '%1s %2s', $item['slug'], $this->row_actions( $actions ) );
			}

		}

		return $item['slug'];

	}

	/**
	 * Generate content for the "thumbnail" column.
	 *
	 * @since 1.6.5
	 *
	 * @param  $item array The current row item.
	 *
	 * @return string
	 */
	public function column_thumbnail( $item ) {
		$content = '';

		if ( 'product_attribute' === $item['wc_type'] && 'product_attribute' !== $item['taxonomy'] ) {

			if ( class_exists( 'WC_SwatchesPlugin' ) ) {
				/**
				 * WooCommerce Variation Swatches and Photos plugin
				 * @todo Check if we need $swatch_term->height and $swatch_term->width.
				 */
				/* @noinspection PhpUndefinedClassInspection */
				$swatch_term = new WC_Swatch_Term( 'swatches_id', $item['term_id'], $item['taxonomy'] );

				if ( 'photo' === $swatch_term->type ) {
					$content = '<img src="' . esc_url( $swatch_term->thumbnail_src ) . '" alt="" />';
				} elseif ( 'color' === $swatch_term->type ) {
					$content = '<div style="background-color:' . esc_attr( $swatch_term->color ) . ';height:48px;width:48px;"></div>';
				}
			}


		} elseif ( 'product_cat' === $item['taxonomy'] ) {

			/**
			 * @see WC_Admin_Taxonomies::product_cat_column()
			 */
			$thumbnail_id = get_term_meta( $item['term_id'], 'thumbnail_id', true );

			if ( $thumbnail_id ) {
				// We have a real product image.
				$image   = wp_get_attachment_thumb_url( $thumbnail_id );
				$content = '<img src="' . esc_url( $image ) . '" alt="" class="wp-post-image" height="48" width="48" />';
			}

		}

		return $content;

	}


	/**
	 * Define function for add item actions by default
	 *
	 * @since 1.0.0
	 *
	 * @param  $item        array
	 * @param  $column_name string
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( isset( $this->table_fields[ $column_name ] ) ) {

			if ( $this->table_fields[ $column_name ]['translatable'] ) {
				$id   = $column_name . '-' . $item['ID'];
				$text = '<input type="text" class="ajaxify wpglobus-translate wpglobus-tax-name ' . $id . '" data-taxonomy="' . $item['taxonomy'] .
				        '" data-wc-type="' . $item['wc_type'] .
				        '" data-language="' . $column_name .
				        '" data-id="' . $item['ID'] .
				        '" id="' . $id .
				        '" value="' . $item[ $column_name ] .
				        '" size="20" />';

				/**
				 * Add term description
				 */
				if ( false !== $item['description'] ) {

					$classes = empty( $item[ 'description-' . $column_name ] ) ? 'dashicons-plus-alt wp-ui-text-notification' : 'dashicons-edit wp-ui-text-highlight';
					$title   = empty( $item[ 'description-' . $column_name ] ) ?
						esc_html__( 'Click to add description', 'woocommerce-wpglobus' ) :
						$item[ 'description-' . $column_name ];

					$text .= '<div id="description-' . $column_name . $item['ID'] . '" class="description"
								data-language="' . $column_name . '"
								data-id="' . $item['ID'] . '"
								data-wc-type="' . $item['wc_type'] . '"
								data-taxonomy="' . $item['taxonomy'] . '"
								data-term-name="' . $item[ WPGlobus::Config()->default_language ] . '">
								<span title="' . esc_attr( $title ) . '" class="dashicons ' . $classes . '"></span>' .
					         '<span class="text">' . $item[ 'description-' . $column_name ] . '</span>' .
					         '</div>';
					//}

				}

				return $text;

			} else {

				if ( isset( $item[ $column_name ] ) ) {
					return $item[ $column_name ];
				} else {

					/**
					 * Fires in each custom column in the Translation list table.
					 *
					 * This hook only fires if the current post_type=product&page=wpglobus-wc-translations
					 *
					 * @since 1.0.0
					 *
					 * @param string $column_name The name of the column to display.
					 * @param array  $item
					 */
					do_action( 'wpglobus_wc_table_manage_column', $column_name, $item );
				}

			}

		} else {
			return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}

		return '';
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		foreach ( (array) $this->table_fields as $field => $attrs ) {
			if ( $attrs['sortable'] ) {
				$sortable_columns[ $field ] = array( $field, false );
			}
		}

		return $sortable_columns;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since  3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array();

		if ( $this->first_column_is_checkbox ) {
			$columns['cb'] = '<input type="checkbox" />';
		}

		foreach ( (array) $this->table_fields as $field => $attrs ) {
			$columns[ $field ] = $attrs['caption'];
		}

		return $columns;
	}

	/**
	 * User's defined function
	 * @since    0.1
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function usort_reorder( $a, $b ) {
		// If no sort, get the default
		$i             = 0;
		$default_field = 'source';

		foreach ( (array) $this->table_fields as $field => $attrs ) {
			$default_field = ( 0 === $i ) ? $field : $default_field;
			if ( isset( $attrs['order'] ) ) {
				break;
			}
			$i ++;
		}
		/** @var $field string */
		$field   = isset( $attrs['order'] ) ? $field : $default_field;
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : $field;

		// If no order, default to asc
		if ( ! empty( $_GET['order'] ) ) {
			$order = $_GET['order'];
		} else {
			$order = isset( $attrs['order'] ) ? $attrs['order'] : 'asc';
		}

		// Determine sort order
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : - $result;
	}

	/**
	 * Define function for first checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="item[]" value="%s" />', $item['ID']
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/**
		 * You can handle your row actions
		 */
		$this->process_row_action();

		if ( 'slug' !== $this->sort_orderby ) {
			usort( $this->data, array( &$this, 'usort_reorder' ) );
		}

		$screen          = get_current_screen();
		$per_page_option = $screen->get_option( 'per_page', 'option' );

		if ( null === $per_page_option ) {
			$per_page = WPGlobus_WC::ROWS_PER_PAGE_DEFAULT;
		} else {
			$user     = get_current_user_id();
			$per_page = get_user_meta( $user, $per_page_option, true );
		}

		if ( empty( $per_page ) ) {
			// Happens when the user does not have the meta yet.
			$per_page = WPGlobus_WC::ROWS_PER_PAGE_DEFAULT;
		}

		$current_page = $this->get_pagenum();
		$total_items  = count( $this->data );

		// only necessary because we have sample data
		$this->found_data = array_slice( $this->data, ( $current_page - 1 ) * $per_page, $per_page );

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items'          => $total_items,
			//WE have to calculate the total number of items
			'per_page'             => $per_page,
			//WE have to determine how many items to show on a page
			'total_pages'          => ceil( $total_items / $per_page ),
			//WE have to calculate the total number of pages
			'total_counting_items' => $this->items_count()
			//WE have to calculate the total number of items with 'counting' flag
		) );

		$this->items = $this->found_data;

	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $item The current item
	 */
	public function single_row( $item ) {
		static $row_class = '';
		$row_class_ext = '';

		if ( 'standard' === $item['type'] ) {
			$row_class = ( '' === $row_class ? ' class="alternate"' : '' );

			if ( empty( $row_class ) ) {
				$row_class_ext = ' class="row-' . $item['ID'] . '"';
			} else {
				$row_class_ext = ' class="alternate ' . 'row-' . $item['ID'] . '"';
			}
		}
		$id = '';
		if ( ! empty( $item['wc_type'] ) ) {
			$id = ' id="' . $item['wc_type'] . '-' . $item['ID'] . '" ';
		}
		echo '<tr ' . $id . $row_class_ext . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param array $item The current item
	 */
	protected function single_row_columns( $item ) {

		switch ( $item['type'] ) :
			case 'standard':

				/** @var string[] $columns */
				list( $columns, $hidden ) = $this->get_column_info();

				foreach ( $columns as $column_name => $column_display_name ) {
					$class = "class='$column_name column-$column_name'";

					$style = '';
					if ( in_array( $column_name, $hidden, true ) ) {
						$style = ' style="display:none;"';
					}

					$attributes = "$class$style";

					if ( 'cb' === $column_name ) {
						echo '<th scope="row" class="check-column">';
						echo $this->column_cb( $item );
						echo '</th>';
					} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
						echo "<td $attributes>";
						echo $this->{'column_' . $column_name}( $item );
						echo '</td>';
					} else {
						echo "<td $attributes>";
						echo $this->column_default( $item, $column_name );
						echo '</td>';
					}
				}
				break;
			case 'header':
				echo "<td colspan=\"99\" scope=\"row\" style=\"text-align:center;background-color:#efefef;\" class=\"table-header\">";  //  @todo add $attributes as line 674
				echo $item['text'];
				echo '</td>';
				break;
		endswitch;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( 'bottom' === $which ) {
			return;
		}

		if ( isset( $_GET['language'] ) ) {
			$current_language = $_GET['language'];
		} else {
			$current_language = $this->languages[1];
		}

		$product_attributes = wc_get_attribute_taxonomy_names();
		$product_cats       = array_diff( $this->all_taxes, $product_attributes );
		$product_cats       = array_diff( $product_cats, array( 'product_cat' ) );

		?>

		<div style="float:left;"> <?php
			foreach ( WPGlobus::Config()->open_languages as $language ) {
				if ( $language === WPGlobus::Config()->default_language ) {
					continue;
				}
				$class = 'wpglobus-language-tab';
				if ( $language === $current_language ) {
					$class .= ' wpglobus-current-language-tab';
				}
				?>
				<div style="float:left;" class="<?php echo $class; ?>">
					<!--
					<a class="" href="<?php //echo $WPGlobus_WC->get_menu_parent_slug(); ?>&page=<?php // echo $WPGlobus_WC->get_page_menu(); ?>&language=<?php //echo $language; ?>"><?php //echo WPGlobus::Config()->en_language_name[$language]; ?></a>
					-->
					<input type="submit" name="wpglobus_wc_language" value="<?php echo WPGlobus::Config()->en_language_name[ $language ]; ?>" />

				</div>    <?php
			} ?>
			<input type="hidden" name="wpglobus_wc_term_language" value="<?php echo $current_language; ?>" />
		</div>

		<div style="float:left;" id="wpglobus_tax_select" class="wpglobus_tax_select">
			<label for="wpglobus_tax_filter"><?php esc_html_e( 'Filter by taxonomy', 'woocommerce-wpglobus' ); ?></label>
			<select name="wpglobus_tax_filter" id="wpglobus_tax_filter" data-placeholder="<?php esc_attr_e( 'Select taxonomies...', 'woocommerce-wpglobus' ); ?>">
				<optgroup label="<?php esc_attr_e( 'Select taxonomies...', 'woocommerce-wpglobus' ); ?>">
					<option value="0"<?php selected( '0' === $this->taxonomy_filter ); ?>>
						<?php esc_html_e( 'All', 'woocommerce-wpglobus' ); ?>
					</option>
				</optgroup>
				<optgroup label="<?php esc_attr_e( 'All Product categories', 'woocommerce-wpglobus' ); ?>">
					<option value="product_cat"<?php selected( 'product_cat' === $this->taxonomy_filter ); ?>>
						product_cat
					</option>
				</optgroup>
				<optgroup label="<?php esc_attr_e( 'Product category', 'woocommerce-wpglobus' ); ?>"> <?php
					foreach ( $product_cats as $cat ) { ?>
						<option value="<?php echo esc_attr( $cat ); ?>"<?php selected( $cat === $this->taxonomy_filter ); ?>>
							<?php echo esc_html( $cat ); ?>
						</option> <?php
					} ?>
				</optgroup>
				<optgroup label="<?php esc_attr_e( 'Product attribute', 'woocommerce-wpglobus' ); ?>"> <?php
					foreach ( $product_attributes as $pa ) { ?>
						<option value="<?php echo esc_attr( $pa ); ?>"<?php selected( $pa === $this->taxonomy_filter ); ?>>
							<?php echo esc_html( $pa ); ?>
						</option> <?php
					} ?>
				</optgroup>
			</select>
			<input type="submit" name="submit" value="<?php esc_attr_e( 'Filter', 'woocommerce-wpglobus' ); ?>" />
		</div>

		<div style="float:left;margin-left:20px;">
			<input type="submit"
			       name="wpglobus_wc_translations_export"
			       id="wpglobus_wc_translations_export"
			       value="<?php esc_attr_e( 'Export to Excel', 'woocommerce-wpglobus' ); ?>" />
		</div>
		<?php
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since  1.0.0
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		/** @var array $columns */
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = $this->sort_orderby;
		}

		if ( isset( $_GET['order'] ) ) {
			$current_order = ( 'desc' === $_GET['order'] ? 'desc' : 'asc' );
		} else {
			$current_order = $this->sort_order;
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . esc_attr( $cb_counter ) . '">' .
                esc_html__( 'Select All', 'woocommerce' ) . '</label>'
			                 . '<input id="cb-select-all-' . esc_attr( $cb_counter ) . '" type="checkbox" />';
			$cb_counter ++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {

			$class = array( 'manage-column', 'column-' . esc_attr( $column_key ) );

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
				$class[] = 'num';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order   = ( 'asc' === $current_order ? 'desc' : 'asc' );
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order   = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . esc_html( $column_display_name ) . '</span><span class="sorting-indicator"></span></a>';
			}

			echo '<th scope="col"';
			if ( $with_id ) {
				echo ' id="' . esc_attr( $column_key ) . '"';
			}
			if ( count( $class ) ) {
				echo ' class="' . esc_attr( implode( ' ', $class ) ) . '"';
			}
			if ( in_array( $column_key, $hidden, true ) ) {
				echo ' style="display:none"';
			}
			echo '>';
			echo $column_display_name; // WPCS: XSS ok.
			echo '</th>';
		}
	}

	/**
	 * Get taxonomies for translate.
	 * @since    1.0.0
	 *
	 * @param string $output
	 * @param bool   $include_all
	 *
	 * @return array
	 */
	private function get_taxonomies( $output = 'names', $include_all = false ) {

		$tax_for_translate = array();

		if ( 'names' === $output ) {

			$tax_for_translate[] = 'product_cat';

			if ( $include_all ) {
				$product_cats = get_terms( 'product_cat', array( 'hide_empty' => false, 'parent' => 0 ) );

				if ( ! is_wp_error( $product_cats ) ) {
					/** @var stdClass[] $product_cats */
					foreach ( $product_cats as $cat ) {
						$tax_for_translate[] = $cat->slug;
					}
				}
			}

			$product_attributes = wc_get_attribute_taxonomy_names();
			if ( ! empty( $product_attributes ) ) {
				$tax_for_translate = array_merge( $tax_for_translate, $product_attributes );
			}

		} elseif ( 'object' === $output ) {

			$args              = array( 'public' => true, '_builtin' => false );
			$tax_for_translate = get_taxonomies( $args, $output );

		}

		return $tax_for_translate;
	}

	/**
	 * Count items with 'counting' flag
	 * @since  1.0.0
	 * @access private
	 *
	 * @return int
	 */
	private function items_count() {
		$i = 0;
		foreach ( $this->data as $item ) {
			if ( isset( $item['counting'] ) && $item['counting'] ) {
				$i ++;
			}
		}

		return $i;
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'wpglobus-wc-translations', 'widefat', 'fixed', $this->_args['plural'] );
	}

	/**
	 * Display the pagination.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

//		$total_items = $this->_pagination_args['total_items'];
		$total_pages          = (int) $this->_pagination_args['total_pages'];
		$total_counting_items = $this->_pagination_args['total_counting_items'];

		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		$output = '<span class="displaying-num">' . sprintf(
		        _n( '1 item', '%s items', $total_counting_items ),
                number_format_i18n( $total_counting_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( 1 === $current ) {
			$disable_first = ' disabled';
		}
		if ( $total_pages === $current ) {
			$disable_last = ' disabled';
		}
		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
            esc_attr__( 'Go to the first page', 'woocommerce' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
            esc_attr__( 'Go to the previous page', 'woocommerce' ),
			esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' === $which ) {
			$html_current_page = $current;
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
				'<label for="current-page-selector" class="screen-reader-text">' .
                __( 'Select Page' ) .
                '</label>',
                esc_attr__( 'Current page', 'woocommerce' ),
				esc_attr( $current ),
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = '<span class="paging-input">' . sprintf(
		        _x( '%1$s of %2$s', 'paging' ),
                $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
            esc_attr__( 'Go to the next page', 'woocommerce' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
            esc_attr__( 'Go to the last page', 'woocommerce' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

		$page_class = ' no-pages';
		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		}

		echo '<div class="tablenav-pages' . esc_attr( $page_class ) . '">' .
		     $output .
		     '</div>';
	}

	/**
	 * Search by slug, source
	 * @since  1.0.0
	 * @access private
	 *
	 * @param $needle
	 * @param $haystack
	 *
	 * @return bool
	 */
	private function search( $needle, $haystack ) {

		if ( is_object( $haystack ) ) {

			if ( false !== strpos( mb_strtolower( $haystack->name ), $needle ) ) {
				return true;
			}

			if ( false !== strpos( $haystack->slug, $needle ) ) {
				return true;
			}

			//* @todo maybe need it */
			/*
			if ( false !== strpos( mb_strtolower($haystack->description), $needle) ) {
				return true;
			} // */

		} elseif ( is_array( $haystack ) ) {

			if ( false !== strpos( mb_strtolower( $haystack['name'] ), $needle ) ) {
				return true;
			}

			if ( false !== strpos( mb_strtolower( $haystack['source'] ), $needle ) ) {
				return true;
			}

		}

		return false;

	}

} // class


