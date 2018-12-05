<?php

global $product, $outstock_opt, $item_layout;
$time_modifiy = get_the_modified_date('Y-m-d');
$new_hot = '';
if(!empty($outstock_opt['new_pro_from'])){
	if(strtotime($time_modifiy) >= strtotime($outstock_opt['new_pro_from']) && !empty($outstock_opt['new_pro_label'])){
		$new_hot = '<span class="newlabel"><span>'. esc_html($outstock_opt['new_pro_label']) .'</span></span>';
	}elseif($product->is_featured() && !empty($outstock_opt['featured_pro_label'])){
		$new_hot = '<span class="hotlabel"><span>'. esc_html($outstock_opt['featured_pro_label']) .'</span></span>';
	}
}elseif($product->is_featured() && !empty($outstock_opt['featured_pro_label'])){
	$new_hot = '<span class="hotlabel"><span>'. esc_html($outstock_opt['featured_pro_label']) .'</span></span>';
}
?>
	<div class="product-wrapper<?php echo (isset($item_layout) && $item_layout == 'list') ? ' item-list-layout':' item-box-layout'; ?>">
		<div class="list-col4">
			<div class="product-image">
				<?php if ( $product->is_on_sale() ) : ?>
					<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale"><span class="sale-bg"></span><span class="sale-text">' . esc_html__( 'Sale', 'outstock' ) . '</span></span>', $post, $product ); ?>
				<?php endif; ?>
				<?php echo '' . $new_hot; ?>
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $product->get_name() ); ?>">
					<?php 
					echo ''.$product->get_image('shop_catalog', array('class'=>'primary_image'));
					
					if(isset($outstock_opt['second_image'])){
						if($outstock_opt['second_image']){
							$attachment_ids = $product->get_gallery_image_ids();
							if ( $attachment_ids ) {
								echo wp_get_attachment_image( $attachment_ids[0], apply_filters( 'single_product_small_thumbnail_size', 'shop_catalog' ), false, array('class'=>'secondary_image') );
							}
						}
					}
					?>
				</a>
			</div>
		</div>
		
		<?php if((isset($item_layout) && $item_layout == 'box') || (!isset($item_layout))){ ?>
			
		<div class="list-col8">
			<div class="gridview">
				<div class="grid-info">
					<h2 class="product-name">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="ratings"><?php echo wc_get_rating_html($product->get_average_rating()); ?></div>
					<div class="price-box"><?php echo ''.$product->get_price_html(); ?></div>
				</div>
				<?php
					$current_date = current_time( 'timestamp' );
					$sale_end = get_post_meta( get_the_ID(), '_sale_price_dates_to', true );
					$timestemp_left = $sale_end + 24*60*60 - 1 - $current_date;
					if($timestemp_left > 0){
						$day_left = floor($timestemp_left / (24 * 60 * 60));
						$hours_left = floor(($timestemp_left - ($day_left * 60 * 60 * 24)) / (60 * 60));
						$mins_left = floor(($timestemp_left - ($day_left * 60 * 60 * 24) - ($hours_left * 60 * 60)) / 60);
						$secs_left = floor($timestemp_left - ($day_left * 60 * 60 * 24) - ($hours_left * 60 * 60) - ($mins_left * 60));
						?>
						<div class="deals-countdown">
							<span class="countdown-row">
								<span class="countdown-section">
									<span class="countdown-val days_left"><?php echo esc_html($day_left); ?></span>
									<span class="countdown-label"><?php echo esc_html__('Days', 'outstock'); ?></span>
								</span>
								<span class="countdown-section">
									<span class="countdown-val hours_left"><?php echo esc_html($hours_left); ?></span>
									<span class="countdown-label"><?php echo esc_html__('Hrs', 'outstock'); ?></span>
								</span>
								<span class="countdown-section">
									<span class="countdown-val mins_left"><?php echo esc_html($mins_left); ?></span>
									<span class="countdown-label"><?php echo esc_html__('Mins', 'outstock'); ?></span>
								</span>
								<span class="countdown-section">
									<span class="countdown-val secs_left"><?php echo esc_html($secs_left); ?></span>
									<span class="countdown-label"><?php echo esc_html__('Secs', 'outstock'); ?></span>
								</span>
							</span>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
