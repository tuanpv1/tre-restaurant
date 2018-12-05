<?php
// All Wow show on effect
function lionthemes_get_effect_list(){
	return array(
		esc_html__( 'None', 'lionthemes' ) 	=> '',
		esc_html__( 'Bounce In', 'lionthemes' ) 	=> 'bounceIn',
		esc_html__( 'Bounce In Down', 'lionthemes' ) 	=> 'bounceInDown',
		esc_html__( 'Bounce In Left', 'lionthemes' ) 	=> 'bounceInLeft',
		esc_html__( 'Bounce In Right', 'lionthemes' ) 	=> 'bounceInRight',
		esc_html__( 'Bounce In Up', 'lionthemes' ) 	=> 'bounceInUp',
		esc_html__( 'Fade In', 'lionthemes' ) 	=> 'fadeIn',
		esc_html__( 'Fade In Down', 'lionthemes' ) 	=> 'fadeInDown',
		esc_html__( 'Fade In Left', 'lionthemes' ) 	=> 'fadeInLeft',
		esc_html__( 'Fade In Right', 'lionthemes' ) 	=> 'fadeInRight',
		esc_html__( 'Fade In Up', 'lionthemes' ) 	=> 'fadeInUp',
		esc_html__( 'Flip In X', 'lionthemes' ) 	=> 'flipInX',
		esc_html__( 'Flip In Y', 'lionthemes' ) 	=> 'flipInY',
		esc_html__( 'Light Speed In', 'lionthemes' ) 	=> 'lightSpeedIn',
		esc_html__( 'Rotate In', 'lionthemes' ) 	=> 'rotateIn',
		esc_html__( 'Rotate In Down Left', 'lionthemes' ) 	=> 'rotateInDownLeft',
		esc_html__( 'Rotate In Down Right', 'lionthemes' ) 	=> 'rotateInDownRight',
		esc_html__( 'Rotate In Up Left', 'lionthemes' ) 	=> 'rotateInUpLeft',
		esc_html__( 'Rotate In Up Right', 'lionthemes' ) 	=> 'rotateInUpRight',
		esc_html__( 'Slide In Down', 'lionthemes' ) 	=> 'slideInDown',
		esc_html__( 'Slide In Left', 'lionthemes' ) 	=> 'slideInLeft',
		esc_html__( 'Slide In Right', 'lionthemes' ) 	=> 'slideInRight',
		esc_html__( 'Roll In', 'lionthemes' ) 	=> 'rollIn',
	);
}

add_action( 'vc_before_init', 'lionthemes_vc_shortcodes' );

//get taxonomy list by parent children
function lionthemes_get_all_taxonomy_terms($taxonomy = 'product_cat', $all = false){
	global $wpdb;
	$categories = $wpdb->get_results($wpdb->prepare("SELECT t.name,t.slug,t.term_group,x.term_taxonomy_id,x.term_id,x.taxonomy,x.description,x.parent,x.count FROM {$wpdb->prefix}term_taxonomy x LEFT JOIN {$wpdb->prefix}terms t ON (t.term_id = x.term_id) WHERE x.taxonomy=%s ORDER BY x.parent ASC, t.name ASC;", $taxonomy));
	$output = array();
	if($all) $output = array( array('label' => esc_html__('All categories', 'lionthemes'), 'value' => '') );
	if(!is_array($categories)) return $output;
	lionthemes_get_repare_terms_children( 0, 0, $categories, 0, $output );
	return $output;
}
function lionthemes_get_repare_terms_children( $parent_id, $pos, $categories, $level, &$output ) {
	for ( $i = $pos; $i < count( $categories ); $i ++ ) {
		if ( isset($categories[ $i ]->parent) && $categories[ $i ]->parent == $parent_id ) {
			$name = str_repeat( " - ", $level ) . ucfirst($categories[ $i ]->name);
			$value = $categories[ $i ]->slug;
			$output[] = array( 'label' => $name, 'value' => $value );
			lionthemes_get_repare_terms_children( (int)$categories[ $i ]->term_id, $i, $categories, $level + 1, $output );
		}
	}
}

function lionthemes_vc_shortcodes() {
	vc_add_param( 'vc_row', array(
		 'type' => 'checkbox',
		 'heading' => esc_html__('Full Width', 'lionthemes'),
		 'param_name' => 'fullwidth',
		 'value' => array(
						'Yes, please' => true
					)
	));
	
	vc_add_params( 'vc_custom_heading', array(
		array(
			'type' => 'textarea',
			'heading' => esc_html__('Sub heading text', 'lionthemes'),
			'param_name' => 'sub_heading',
			'value' => '',
			'group' => esc_html__( 'Outstock options', 'lionthemes' ),
		 ),
		array(
			'type' => 'dropdown',
			'heading' => esc_html__('Sub heading tag', 'lionthemes'),
			'param_name' => 'sub_heading_tag',
			'value' => array(
				'h1'=>'h1',
				'h2'=>'h2',
				'h3'=>'h3',
				'h4'=>'h4',
				'h5'=>'h5',
				'h6'=>'h6',
				'div'=>'div',
				'p'=>'p',
				'span'=>'span',
			),
			'group' => esc_html__( 'Outstock options', 'lionthemes' ),
		)
	));

	vc_add_params( 'vc_video', array(
		array(
			'type' => 'checkbox',
			 'heading' => esc_html__('Enable lightbox', 'lionthemes'),
			 'param_name' => 'lightbox',
			 'value' => array(
							'Yes, please' => true
						),
			'group' => esc_html__( 'Outstock options', 'lionthemes' ),
		 ),
		array(
			'type' => 'textarea_html',
			'heading' => esc_html__('Short description', 'lionthemes'),
			'param_name' => 'content',
			'group' => esc_html__( 'Outstock options', 'lionthemes' ),
		)
	));

	//Brand logos
	vc_map( array(
		'name' => esc_html__( 'Brand Logos', 'lionthemes' ),
		'base' => 'ourbrands',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Brand list', 'lionthemes' ),
				'param_name' => 'brand_logos',
				'value' => array(
						esc_html__('List 1', 'lionthemes')	=> 'brand_logos_1',
						esc_html__('List 2', 'lionthemes')	=> 'brand_logos_2',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Widget style', 'lionthemes' ),
				'param_name' => 'widget_style',
				'value' => array(
						esc_html__('Default', 'lionthemes')	=> '',
						esc_html__('Line style', 'lionthemes')	=> 'line-style',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of columns', 'lionthemes' ),
				'param_name' => 'colsnumber',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
						'6'	=> '6',
					),
				'save_always' => true,
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of rows', 'lionthemes' ),
				'param_name' => 'rows',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
					),
				'save_always' => true
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Grid', 'lionthemes' )	 	=> 'grid',
						esc_html__( 'List', 'lionthemes' )	 	=> 'list',
						esc_html__( 'Carousel', 'lionthemes' ) 	=> 'carousel',
					),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show direction control', 'lionthemes'),
				'param_name' => 'nav',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show pagination control', 'lionthemes'),
				'param_name' => 'dot',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Autoplay', 'lionthemes'),
				'param_name' => 'autoplay',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Autoplay timeout', 'lionthemes' ),
				'param_name' => 'autoplay_timeout',
				'value' => '5000',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Smart speed', 'lionthemes' ),
				'param_name' => 'smart_speed',
				'value' => '250',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count desktop small', 'lionthemes' ),
				'param_name' => 'desksmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet', 'lionthemes' ),
				'param_name' => 'tablet_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet small', 'lionthemes' ),
				'param_name' => 'tabletsmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count mobile', 'lionthemes' ),
				'param_name' => 'mobile_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Margin', 'lionthemes' ),
				'param_name' => 'margin',
				'value' => '30',
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
		)
	) );
	
	
	//Feature content widget
	vc_map( array(
		'name' => esc_html__( 'Feature content', 'lionthemes' ),
		'base' => 'featuredcontent',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'iconpicker',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Icon', 'lionthemes' ),
				'param_name' => 'icon',
				'value' => '',
			),
			array(
				'type' => 'textarea_raw_html',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Feature text', 'lionthemes' ),
				'param_name' => 'feature_text',
				'value' => '',
			),
			array(
				'type' => 'textarea_raw_html',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Short description', 'lionthemes' ),
				'param_name' => 'short_desc',
				'value' => '',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Layout style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__('Style 1', 'lionthemes')	=> '',
						esc_html__('Style 2', 'lionthemes')	=> 'style_2',
						esc_html__('Style 3', 'lionthemes')	=> 'style_3',
					),
				'save_always' => true,
				'description' => esc_html__( 'This option for list style defined help for theme design.', 'lionthemes' )
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			)
		)
	) );
	
	//Specify Products
	vc_map( array(
		'name' => esc_html__( 'Specify Products', 'lionthemes' ),
		'base' => 'specifyproducts',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'textarea',
				'heading' => esc_html__('Short Description', 'lionthemes'),
				'param_name' => 'short_desc',
				'holder' => 'div',
				'class' => '',
				'value' => '',
				'save_always' => true,
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Widget style', 'lionthemes' ),
				'param_name' => 'widget_style',
				'value' => array(
						esc_html__('Default', 'lionthemes')	=> '',
						esc_html__('Line style', 'lionthemes')	=> 'line-style',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Type', 'lionthemes' ),
				'param_name' => 'type',
				'value' => array(
						esc_html__( 'Best Selling', 'lionthemes' )		=> 'best_selling',
						esc_html__( 'Featured Products', 'lionthemes' ) => 'featured_product',
						esc_html__( 'Top Rate', 'lionthemes' ) 			=> 'top_rate',
						esc_html__( 'Recent Products', 'lionthemes' ) 	=> 'recent_product',
						esc_html__( 'On Sale', 'lionthemes' ) 			=> 'on_sale',
						esc_html__( 'Recent Review', 'lionthemes' ) 	=> 'recent_review',
						esc_html__( 'Product Deals', 'lionthemes' )		 => 'deals'
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'In Specify Category', 'lionthemes' ),
				'param_name' => 'in_category',
				'value' => lionthemes_get_all_taxonomy_terms('product_cat', true),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of products to show', 'lionthemes' ),
				'param_name' => 'number',
				'value' => '10',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Grid', 'lionthemes' )	 	=> 'grid',
						esc_html__( 'List', 'lionthemes' ) 		=> 'list',
						esc_html__( 'Carousel', 'lionthemes' ) 	=> 'carousel',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Item layout', 'lionthemes' ),
				'param_name' => 'item_layout',
				'value' => array(
						esc_html__( 'Box', 'lionthemes' ) 		=> 'box',
						esc_html__( 'List', 'lionthemes' ) 	=> 'list',
					),
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Autoplay', 'lionthemes'),
				'param_name' => 'autoplay',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Autoplay timeout', 'lionthemes' ),
				'param_name' => 'autoplay_timeout',
				'value' => '5000',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Smart speed', 'lionthemes' ),
				'param_name' => 'smart_speed',
				'value' => '250',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show Navigation', 'lionthemes'),
				'param_name' => 'shownav',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of rows', 'lionthemes' ),
				'param_name' => 'rows',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns', 'lionthemes' ),
				'param_name' => 'columns',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
						'6'	=> '6',
					),
				'save_always' => true,
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count desktop small', 'lionthemes' ),
				'param_name' => 'desksmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet', 'lionthemes' ),
				'param_name' => 'tablet_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet small', 'lionthemes' ),
				'param_name' => 'tabletsmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count mobile', 'lionthemes' ),
				'param_name' => 'mobile_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4', 
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Margin', 'lionthemes' ),
				'param_name' => 'margin',
				'value' => '30',
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
		)
	) );
	//Products Category
	vc_map( array(
		'name' => esc_html__( 'Products Category', 'lionthemes' ),
		'base' => 'productscategory',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'textarea',
				'heading' => esc_html__('Short Description', 'lionthemes'),
				'param_name' => 'short_desc',
				'holder' => 'div',
				'class' => '',
				'value' => '',
				'save_always' => true,
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Widget style', 'lionthemes' ),
				'param_name' => 'widget_style',
				'value' => array(
						esc_html__('Default', 'lionthemes')	=> '',
						esc_html__('Line style', 'lionthemes')	=> 'line-style',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Category', 'lionthemes' ),
				'param_name' => 'category',
				'value' => lionthemes_get_all_taxonomy_terms(),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of products to show', 'lionthemes' ),
				'param_name' => 'number',
				'value' => '10',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Grid', 'lionthemes' )	 	=> 'grid',
						esc_html__( 'List', 'lionthemes' ) 		=> 'list',
						esc_html__( 'Carousel', 'lionthemes' ) 	=> 'carousel',
					),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Autoplay', 'lionthemes'),
				'param_name' => 'autoplay',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Autoplay timeout', 'lionthemes' ),
				'param_name' => 'autoplay_timeout',
				'value' => '5000',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Smart speed', 'lionthemes' ),
				'param_name' => 'smart_speed',
				'value' => '250',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Item layout', 'lionthemes' ),
				'param_name' => 'item_layout',
				'value' => array(
						esc_html__( 'Box', 'lionthemes' ) 		=> 'box',
						esc_html__( 'List', 'lionthemes' ) 	=> 'list',
					),
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show Navigation', 'lionthemes'),
				'param_name' => 'shownav',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of rows', 'lionthemes' ),
				'param_name' => 'rows',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns', 'lionthemes' ),
				'param_name' => 'columns',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
						'6'	=> '6',
					),
				'save_always' => true,
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count desktop small', 'lionthemes' ),
				'param_name' => 'desksmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet', 'lionthemes' ),
				'param_name' => 'tablet_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet small', 'lionthemes' ),
				'param_name' => 'tabletsmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count mobile', 'lionthemes' ),
				'param_name' => 'mobile_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Margin', 'lionthemes' ),
				'param_name' => 'margin',
				'value' => '30',
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
		)
	) );
	
	//Testimonials
	vc_map( array(
		'name' => esc_html__( 'Outstock Testimonials', 'lionthemes' ),
		'base' => 'testimonials',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Widget style', 'lionthemes' ),
				'param_name' => 'widget_style',
				'value' => array(
						esc_html__('Default', 'lionthemes')	=> '',
						esc_html__('Line style', 'lionthemes')	=> 'line-style',
					),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of products to show', 'lionthemes' ),
				'param_name' => 'number',
				'value' => '10',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Order', 'lionthemes' ),
				'param_name' => 'order',
				'value' => array(
					esc_html__('Latest first', 'lionthemes') => '',
					esc_html__('Random', 'lionthemes') => 'rand',
				),
				'save_always' => true,
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Carousel', 'lionthemes' ) 	=> 'carousel',
						esc_html__( 'List', 'lionthemes' ) 		=> 'list',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns', 'lionthemes' ),
				'param_name' => 'columns',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show direction control', 'lionthemes'),
				'param_name' => 'nav',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Show pagination control', 'lionthemes'),
				'param_name' => 'dot',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Autoplay', 'lionthemes'),
				'param_name' => 'autoplay',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Autoplay timeout', 'lionthemes' ),
				'param_name' => 'autoplay_timeout',
				'value' => '5000',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Smart speed', 'lionthemes' ),
				'param_name' => 'smart_speed',
				'value' => '250',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count desktop small', 'lionthemes' ),
				'param_name' => 'desksmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet', 'lionthemes' ),
				'param_name' => 'tablet_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet small', 'lionthemes' ),
				'param_name' => 'tabletsmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count mobile', 'lionthemes' ),
				'param_name' => 'mobile_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Margin', 'lionthemes' ),
				'param_name' => 'margin',
				'value' => '30',
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
		)
	) );
	
	//MailPoet Newsletter Form
	vc_map( array(
		'name' => esc_html__( 'Newsletter Form (MailPoet)', 'lionthemes' ),
		'base' => 'wysija_form',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Form ID', 'lionthemes' ),
				'param_name' => 'id',
				'value' => esc_html__( '', 'lionthemes' ),
				'description' => esc_html__( 'Enter form ID here', 'lionthemes' ),
			)
		)
	) );
	
	//MailChimp Newsletter Form
	vc_map( array(
		'name' => esc_html__( 'Newsletter Form (MailChimp)', 'lionthemes' ),
		'base' => 'lionthemes_mailchimpform',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Form ID', 'lionthemes' ),
				'param_name' => 'id',
				'value' => esc_html__( '', 'lionthemes' ),
				'description' => esc_html__( 'Enter form ID here', 'lionthemes' ),
			),
			array(
				'type' => 'textarea_raw_html',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Short text', 'lionthemes' ),
				'param_name' => 'short_text',
				'value' => '',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Custom style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Home 1', 'lionthemes' ) 	=> '',
						esc_html__( 'Home 2', 'lionthemes' ) 		=> 'style_2',
					),
			),
		)
	) );
	
	//Latest posts
	vc_map( array(
		'name' => esc_html__( 'Blog posts', 'lionthemes' ),
		'base' => 'blogposts',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Widget style', 'lionthemes' ),
				'param_name' => 'widget_style',
				'value' => array(
						esc_html__('Default', 'lionthemes')	=> '',
						esc_html__('Line style', 'lionthemes')	=> 'line-style',
					),
			),
			array(
				'type' => 'textarea',
				'heading' => esc_html__('Short Description', 'lionthemes'),
				'param_name' => 'short_desc',
				'holder' => 'div',
				'class' => '',
				'value' => '',
				'save_always' => true,
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of post to show', 'lionthemes' ),
				'param_name' => 'number',
				'value' => '5',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Style', 'lionthemes' ),
				'param_name' => 'style',
				'value' => array(
						esc_html__( 'Carousel', 'lionthemes' ) 	=> 'carousel',
						esc_html__( 'List', 'lionthemes' ) 		=> 'list',
						esc_html__( 'Grid', 'lionthemes' )	 	=> 'grid',
					),
			),
			array(
				'type' => 'checkbox',
				'heading' => esc_html__('Autoplay', 'lionthemes'),
				'param_name' => 'autoplay',
				'value' => array(
					'Yes' => 1
				),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Autoplay timeout', 'lionthemes' ),
				'param_name' => 'autoplay_timeout',
				'value' => '5000',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Smart speed', 'lionthemes' ),
				'param_name' => 'smart_speed',
				'value' => '250',
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Number of rows', 'lionthemes' ),
				'param_name' => 'rows',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns', 'lionthemes' ),
				'param_name' => 'columns',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Image scale', 'lionthemes' ),
				'param_name' => 'image',
				'value' => array(
						esc_html__( 'Wide', 'lionthemes' )	=> 'wide',
						esc_html__( 'Square', 'lionthemes' ) => 'square',
					),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Excerpt length', 'lionthemes' ),
				'param_name' => 'length',
				'value' => '20',
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Order by', 'lionthemes' ),
				'param_name' => 'orderby',
				'value' => array(
						esc_html__( 'Posted Date', 'lionthemes' )	=> 'date',
						esc_html__( 'Ordering', 'lionthemes' ) => 'menu_order',
						esc_html__( 'Random', 'lionthemes' ) => 'rand',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Order Direction', 'lionthemes' ),
				'param_name' => 'order',
				'value' => array(
						esc_html__( 'Descending', 'lionthemes' )	=> 'DESC',
						esc_html__( 'Ascending', 'lionthemes' ) => 'ASC',
					),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count desktop small', 'lionthemes' ),
				'param_name' => 'desksmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet', 'lionthemes' ),
				'param_name' => 'tablet_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count tablet small', 'lionthemes' ),
				'param_name' => 'tabletsmall',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'dropdown',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Columns count mobile', 'lionthemes' ),
				'param_name' => 'mobile_count',
				'value' => array(
						'1'	=> '1',
						'2'	=> '2',
						'3'	=> '3',
						'4'	=> '4',
						'5'	=> '5',
					),
				'save_always' => true,
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Margin', 'lionthemes' ),
				'param_name' => 'margin',
				'value' => '30',
				'group' => esc_html__( 'Carousel options', 'lionthemes' ),
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
		)
	) );
	
	//Custom countdown
	vc_map( array(
		'name' => esc_html__( 'Outstock Countdown timer', 'lionthemes' ),
		'base' => 'lionthemes_countdown',
		'class' => '',
		'category' => esc_html__( 'Outstock Theme', 'lionthemes'),
		'params' => array(
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Title', 'lionthemes' ),
				'param_name' => 'title',
				'value' => '',
			),
			array(
				'type' => 'textarea',
				'heading' => esc_html__('Short Description', 'lionthemes'),
				'param_name' => 'short_desc',
				'holder' => 'div',
				'class' => '',
				'value' => '',
			),
			array(
				'type' => 'textfield',
				'heading' => esc_html__('Date time expires', 'lionthemes'),
				'param_name' => 'datetime',
				'holder' => 'div',
				'class' => '',
				'value' => '',
				'description' => esc_html__( 'Format must be yyyy-mm-dd HH:mm:ss', 'lionthemes' )
			),
			array(
				'type' => 'textfield',
				'holder' => 'div',
				'class' => '',
				'heading' => esc_html__( 'Extra class name', 'lionthemes' ),
				'param_name' => 'el_class',
				'value' => '',
				'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lionthemes' )
			),
		)
	) );
}
// Filter to replace default css class names for vc_row shortcode and vc_column
add_filter( 'vc_shortcodes_css_class', 'lionthemes_custom_css_classes_for_vc_row_and_vc_column', 10, 2 );
function lionthemes_custom_css_classes_for_vc_row_and_vc_column( $class_string, $tag ) {
  $class_string = preg_replace( '/vc_col-sm-(\d{1,2})/', 'col-sm-$1', $class_string ); // This will replace "vc_col-sm-%" with "my_col-sm-%"
  $class_string = str_replace('vc_column_container', 'column_container', $class_string);
  return $class_string; // Important: you should always return modified or original $class_string
}
?>