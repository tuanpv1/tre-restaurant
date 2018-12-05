<?php
function outstock_blogposts_shortcode( $atts ) {
	global $outstock_opt;
	$post_index = 0;
	$atts = shortcode_atts( array(
		'title' => '',
		'widget_style' => '',
		'short_desc' => '',
		'number' => 5,
		'order' => 'DESC',
		'orderby' => 'post_date',
		'image' => 'wide', //square
		'length' => 20,
		'style' => 'carousel',
		'columns' => '1',
		'rows' => '1',
		'desksmall' => '4',
		'tablet_count' => '3',
		'tabletsmall' => '2',
		'mobile_count' => '1',
		'margin' => '30'
	), $atts, 'latestposts' );
	extract($atts);
	if($image == 'wide'){
		$imagesize = 'outstock-post-thumbwide';
	} else {
		$imagesize = 'outstock-post-thumb';
	}
	$html = '';

	$postargs = array(
		'posts_per_page'   => $number,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => $orderby,
		'order'            => $order,
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'post',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'post_status'      => 'publish',
		'suppress_filters' => true );
	$postslist = get_posts( $postargs );
	$total = count($postslist);
	if($total == 0) return;
	switch ($columns) {
		case '5':
			$class_column='col-sm-20 col-xs-6';
			break;
		case '4':
			$class_column='col-sm-3 col-xs-6';
			break;
		case '3':
			$class_column='col-lg-4 col-md-4 col-sm-4 col-xs-6';
			break;
		case '2':
			$class_column='col-lg-6 col-md-6 col-sm-6 col-xs-6';
			break;
		default:
			$class_column='col-lg-12 col-md-12 col-sm-12 col-xs-6';
			break;
	}
	$row_cl = ' row';
	if($style != 'grid'){
		$row_cl = $class_column = '';
	}
	$html.='<div class="blog-posts '. esc_attr($row_cl) .'">';
		$html .= ($title) ? '<h3 class="vc_widget_title vc_blog_title '. esc_attr($widget_style) .'"><span>'. esc_html($title) .'</span></h3>' : '';
		$html .= ($short_desc) ? '<div class="short_desc">'. wpautop($short_desc) .'</div>' : '';
		$html .= '<div class="inner-content '.  esc_attr($widget_style) .'">';
			$html .= ($style == 'carousel') ? '<div class="owl-carousel owl-theme" data-desksmall="'. esc_attr($desksmall) .'" data-tabletsmall="'. esc_attr($tabletsmall) .'" data-mobile="'. esc_attr($mobile_count) .'" data-tablet="'. esc_attr($tablet_count) .'" data-margin="'. esc_attr($margin) .'" data-dots="false" data-nav="true" data-owl="slide" data-item-slide="'. esc_attr($columns) .'" data-ow-rtl="false">':'';
			$duration = 0;
			foreach ( $postslist as $post ) {
				$duration = $duration + 100;
				if($rows > 1 && $style == 'carousel'){
					$post_index ++;
					if ( (( $post_index - 1 ) % $rows == 0) && $post_index < $total){
						$html .= '<div class="group">';
					}
				}
				$class_nothumb = '';
				$diff_time = sprintf( _x( '%s ago', '%s = human-readable time difference', 'outstock' ), human_time_diff( get_the_time( 'U', $post->ID ), current_time( 'timestamp' ) ) );
				if(!get_the_post_thumbnail( $post->ID, $imagesize )) $class_nothumb = ' no-thumb';
				$html.='<div class="item-post post-'. $post->ID .' ' . $class_column . $class_nothumb . ' wow fadeInUp" data-wow-delay="'. esc_attr($duration) .'ms" data-wow-duration="0.5s">';
					$html.='<div class="post-wrapper">';
						$html.='<div class="post-thumb">';
							if(get_the_post_thumbnail( $post->ID, $imagesize )){
								$html.='<a href="'.get_the_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID, $imagesize).'</a>';
							}
						$html.='</div>';
						$html.='<div class="post-info">';							
							$html.='<h3 class="post-title"><a href="'.get_the_permalink($post->ID).'">'.get_the_title($post->ID).'</a></h3>';
							$author_id = $post->post_author;
							$html .= '<div class="post-by"><span class="author">'. esc_html__('By', 'arion') .'</span><a class="author" href="'. get_author_posts_url($author_id) .'"> '. get_the_author_meta( 'user_nicename' , $author_id ) .'</a></div>';
							$html .= '<div class="post-date"><span class="day">'. get_the_date( 'd', $post->ID ) .'</span>, <span class="month"> '. get_the_date( 'F', $post->ID ) .'</span></div>';
							$html.='<div class="post-excerpt">';
								$html.= outstock_get_the_excerpt($post->ID);
							$html.='</div>';
							$html.= '<a href="'. get_the_permalink($post->ID) .'"><span class="readmore-text">'. esc_html__('Read more', 'arion') .'</span> </a>';
						
						$html.='</div>';
					$html.='</div>';
				$html.='</div>';
				if($rows > 1 && $style == 'carousel'){
					if (($post_index % $rows == 0) || $post_index == $total ) {
						$html .= '</div>';
					}
				}
			}
		$html .= ($style == 'carousel') ? '</div>':'';
	$html.='</div>';
	$html.='</div>';

	wp_reset_postdata();
	
	return $html;
}
add_shortcode( 'blogposts', 'outstock_blogposts_shortcode' );
?>