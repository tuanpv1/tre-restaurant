<?php
/**
 * The template for displaying product widget entries
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.4.0
 */
global $product; ?>

<?php 
    $class = (isset($class_column)) ? $class_column : '';
    if(isset($is_animate) && $is_animate){ 
        $class .= ' wow fadeInUp';
    }
    if(!isset($delay)){
        $delay = 0;
    }
	if(!isset($show_rating)) $show_rating = true;
	if(!isset($show_buttons)) $show_buttons = false;
?>
<div class="item-product-widget <?php echo esc_attr($class); ?>" data-wow-duration="0.5s" data-wow-delay="<?php echo esc_attr($delay); ?>ms">
    <div class="images pull-left">
        <?php echo '' . $product->get_image(); ?>
    </div>
    <div class="product-meta">
        <div class="product-title separator">
            <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $product->get_name() ); ?>">
                <?php echo '' . $product->get_name(); ?>
            </a>
        </div>
        <?php if($show_rating){ ?>
        <div class="separator">
            <?php if ( $rating_html = wc_get_rating_html($product->get_average_rating()) ) { ?>
            <?php echo '' . $rating_html; ?>
            <?php } else { ?>
                <div class="star-rating"></div>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="price separator">
            <?php echo '' . $product->get_price_html(); ?>
        </div>
    </div>
</div>