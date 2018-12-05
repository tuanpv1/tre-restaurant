<?php
function outstock_testimonials_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'title'=>'',
		'widget_style' => '',
		'el_class' => '',
		'number' => 10,
		'order' => '',
		'style'=>'carousel',
		'columns' => 1,
		'desksmall' => '4',
		'tablet_count' => '3',
		'tabletsmall' => '2',
		'mobile_count' => '1',
		'margin' => '30',
		'nav'=> 'true',
		'dot'=> 'false',
		'autoplay'=> 'false',
		'autoplay_timeout'=> '5000',
		'smart_speed'=> '250',
	), $atts, 'specifyproducts' ) );

	$_id = outstock_make_id();
	$args = array(
		'post_type' => 'testimonial',
		'posts_per_page' => $number,
		'post_status' => 'publish'
	);
	if($order){
		$args['orderby'] = $order;
	}
	$owl = array(
		'data-owl="slide"',
		'data-desksmall="'. esc_attr($desksmall).'"',
		'data-tabletsmall="'. esc_attr($tabletsmall) .'"',
		'data-mobile="'. esc_attr($mobile_count) .'"',
		'data-tablet="'. $tablet_count .'"',
		'data-margin="'. esc_attr($margin) .'"',
		'data-item-slide="'. esc_attr($columns) .'"',
		'data-dots="' . esc_attr($dot) . '"',
		'data-nav="' . esc_attr($nav) . '"',
		'data-autoplay="' . esc_attr($autoplay) . '"',
		'data-playtimeout="' . esc_attr($autoplay_timeout) . '"',
		'data-speed="' . esc_attr($smart_speed) . '"',
	);
	
$query = new WP_Query($args);
?>
<?php if($query->have_posts()){ ob_start(); ?>
	<div class="testimonials <?php echo esc_attr($el_class); ?>">
		<?php if($title){ ?><h3 class="vc_widget_title vc_testimonial_title <?php echo esc_attr($widget_style); ?>"><span><?php echo esc_html($title); ?></span></h3><?php } ?>
		<div class="inner-content <?php echo esc_attr($widget_style); ?>">
			<div <?php echo ($style == 'carousel') ? implode(' ', $owl) : ''; ?> class="testimonials-list<?php echo ($style == 'carousel') ? ' owl-carousel owl-theme':'' ?>">
				<?php $i=0; while($query->have_posts()): $query->the_post(); $i++; ?>
					<!-- Wrapper for slides -->
					<div class="quote">
						<div class="author-avatar">
							<div class="avatar">
								<?php the_post_thumbnail( 'thumbnail' ); ?>
							</div>
							
							<p class="author">
								<span><?php the_title(); ?></span>
							</p>
							<?php if(get_post_meta(get_the_ID(), '_byline', true)){ ?>
							<p class="byline">
							<?php echo get_post_meta(get_the_ID(), '_byline', true); ?>
							</p>
							<?php } ?>
							<span class="date"><i class="fa fa-calendar"></i><?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ); ?></span>
						</div>
						<blockquote class="testimonials-text">
							<?php the_content(); ?>
						</blockquote>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
<?php 
	$content = ob_get_contents();
	ob_end_clean();
	wp_reset_postdata();
	return $content;
	}
}
add_shortcode( 'testimonials', 'outstock_testimonials_shortcode' );
?>