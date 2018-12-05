<?php 
	$_delay = 100;
	$_count = 1;
?>
<div class="products-block shop-products products grid-view">
	<div class="row">
	<?php while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
		<!-- Product Item -->
		<div class="item-col <?php echo esc_attr($class_column); ?> product wow fadeInUp" data-wow-duration="0.5s" data-wow-delay="<?php echo esc_attr($_delay); ?>ms">
			<?php 
				if(isset($is_deals) && $is_deals){
					wc_get_template_part( 'content', 'product-deals' );
				}else{
					wc_get_template_part( 'content', 'product-inner' );
				}
			?>
		</div>
		<?php $_delay+=100; ?>
		<!-- End Product Item -->
		<?php
			if($_count==$columns_count){
				$_count=0;$_delay=100;
			}
			$_count++;
		?>
	<?php endwhile; ?>
	</div>
</div>