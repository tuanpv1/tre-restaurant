<div <?php post_class( ); ?>>
    <div class="product-block product product-list">
		<div class="product-image pull-left">
			<div class="image">
		        <?php woocommerce_show_product_loop_sale_flash(); ?>
		        <a href="<?php the_permalink(); ?>">
		            <?php
		                /**
		                * woocommerce_before_shop_loop_item_title hook
		                *
		                * @hooked woocommerce_show_product_loop_sale_flash - 10
		                * @hooked woocommerce_template_loop_product_thumbnail - 10
		                */
		                do_action( 'woocommerce_before_shop_loop_item_title' );
		            ?>
		        </a>
		    </div>
		</div>
		<div class="product-meta">
			<h4 class="name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
           	<?php 
           		woocommerce_template_loop_rating();
           		woocommerce_template_single_excerpt();
           	?>
           	<div class="button-groups">
                <div class="button-item clearfix">
                    <?php do_action('woocommerce_after_shop_loop_item'); ?>
                </div>
            </div>
		</div>
	</div>
</div>