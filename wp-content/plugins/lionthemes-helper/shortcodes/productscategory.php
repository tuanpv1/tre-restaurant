<?php
function outstock_productscategory_shortcode( $atts ) {
	global $outstock_opt;
	
	$atts = shortcode_atts( array(
							'title' => '',
							'widget_style' => '',
							'short_desc' => '',
							'item_layout'=>'box',
							'category' => '',
							'number' => 10,
							'columns'=> '4',
							'rows'=> '1',
							'el_class' => '',
							'style'=>'grid',
							'desksmall' => '4',
							'tablet_count' => '3',
							'tabletsmall' => '2',
							'mobile_count' => '1',
							'margin' => '30',
							'shownav' => 'false',
							'autoplay'=> 'false',
							'autoplay_timeout'=> '5000',
							'smart_speed'=> '250',
							), $atts, 'productscategory' ); 
	extract($atts);
	switch ($columns) {
		case '6':
			$class_column='col-lg-2 col-md-3 col-sm-4 col-xs-6';
			break;
		case '5':
			$class_column='col-md-20 col-sm-4 col-xs-6';
			break;
		case '4':
			$class_column='col-sm-3 col-xs-6';
			break;
		case '3':
			$class_column='col-sm-4 col-xs-6';
			break;
		case '2':
			$class_column='col-sm-6 col-xs-6';
			break;
		default:
			$class_column='col-sm-12 col-xs-6';
			break;
	}
	if($category=='') return;
	$_id = outstock_make_id();
	$loop = outstock_woocommerce_query('',$number, $category);
	$owl_data = '';
	if($style == 'carousel'){
		$owl_data .= 'data-dots="false" data-nav="'. $shownav .'" data-owl="slide" data-ow-rtl="false" ';
		$owl_data .= 'data-data-desksmall="'. esc_attr($desksmall) .'" ';
		$owl_data .= 'data-tabletsmall="'. esc_attr($tabletsmall) .'" ';
		$owl_data .= 'data-mobile="'. esc_attr($mobile_count) .'" ';
		$owl_data .= 'data-tablet="'. esc_attr($tablet_count) .'" ';
		$owl_data .= 'data-margin="'. esc_attr($margin) .'" ';
		$owl_data .= 'data-item-slide="'. esc_attr($columns) .'" ';
		$owl_data .= 'data-autoplay="'. esc_attr($autoplay) .'" ';
		$owl_data .= 'data-playtimeout="'. esc_attr($autoplay_timeout) .'" ';
		$owl_data .= 'data-speed="'. esc_attr($smart_speed) .'" ';
	}
	if ( $loop->have_posts() ){ 
		ob_start();
	?>
		<?php $_total = $loop->post_count; ?>
		<div class="woocommerce<?php echo esc_attr($el_class); ?>">
			<?php if($title){ ?><h3 class="vc_widget_title vc_products_title <?php echo esc_attr($widget_style); ?>"><span><?php echo esc_html($title); ?></span></h3><?php } ?>
			<?php if($short_desc){ ?><div class="short_desc"><?php echo wpautop($short_desc) ?></div><?php } ?>
			<div class="inner-content <?php echo esc_attr($widget_style); ?>">
				<?php wc_get_template( 'product-layout/'.$style.'.php', array( 
							'show_rating' => true,
							'_id'=>$_id,
							'loop'=>$loop,
							'columns_count'=>$columns,
							'class_column' => $class_column,
							'_total'=>$_total,
							'number'=>$number,
							'rows'=>$rows,
							'owl_attrs' => $owl_data,
							'itemlayout'=> $item_layout,
							 ) ); ?>
			</div>
		</div>
	<?php 
		$content = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		return $content;
	} 
} 
add_shortcode( 'productscategory', 'outstock_productscategory_shortcode' );
?>