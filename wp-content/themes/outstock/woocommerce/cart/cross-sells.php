<?php
/**
 * Cross-sells
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop, $outstock_opt;
$woocommerce_loop['columns'] = 1; //apply for carousel work
if ( $cross_sells ) : ?>

	<div class="cross-sells">

		<h3 class="widget-title">
			<span>
				<?php if (!empty($outstock_opt['crosssells_title'])){ ?>
					<?php echo esc_html($outstock_opt['crosssells_title']); ?>
				<?php }else{ ?>
					<?php esc_html_e( 'You may be interested in&hellip;', 'outstock' ) ?>
				<?php } ?>
			</span>
		</h3>

		<?php woocommerce_product_loop_start(); ?>
			<div data-owl="slide" data-item-slide="2" data-margin="20" data-mobile="1" data-tablet="2" data-ow-rtl="false" class="owl-carousel owl-theme cross-sells-slide">
			<?php foreach ( $cross_sells as $cross_sell ) : ?>

				<?php
				 	$post_object = get_post( $cross_sell->get_id() );

					setup_postdata( $GLOBALS['post'] =& $post_object );

					wc_get_template_part( 'content', 'product' ); ?>

			<?php endforeach; ?>
			</div>
		<?php woocommerce_product_loop_end(); ?>

	</div>

<?php endif;

wp_reset_query();
