<?php
/**
 * The template for displaying product search form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/product-searchform.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $outstock_opt;
$real_id = outstock_make_id();
$categories = array();
if(!empty($outstock_opt['categories_search'])){
	$categories = get_terms(array(
		'taxonomy' => 'product_cat',
		'hide_empty' => false,
		'include' => $outstock_opt['categories_search']
	));
}
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
?>

<form role="search" method="get" id="search_mini_form" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/'  ) ); ?>">
	<?php if(!empty($outstock_opt['main_search'])){ ?>
		<div class="search-content-popup">
			<a class="close-popup" href="javascript:void(0)"><span class="ion ion-ios-close-empty"></span></a>
			<h3><?php echo esc_html__('Search', 'outstock') ?></h3>
			<?php if(!empty($categories)){ ?>
			<div class="categories-list">
				<ul class="items-list">
					<li class="cat-item<?php echo (!$cat) ? ' selected' : ''; ?>"><a href="javascript:void(0)" data-slug=""><?php echo esc_html__('All categories', 'outstock') ?></a></li>
					<?php foreach($categories as $category){ ?>
					<li class="cat-item<?php echo ($category->slug == $cat) ? ' selected' : ''; ?>"><a data-slug="<?php echo esc_attr($category->slug); ?>" href="javascript:void(0)"><?php echo esc_html($category->name); ?></a></li>
					<?php } ?>
				</ul>
				<input type="hidden" name="cat" value="<?php echo esc_attr($cat) ?>" />
			</div>
			<?php }  ?>
			<div class="field-container">
				<input type="search" id="woocommerce-product-search-field-<?php echo esc_attr($real_id); ?>" class="search-field" placeholder="<?php echo esc_attr_x( 'Search Products&hellip;', 'placeholder', 'outstock' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'outstock' ); ?>" />
				<input type="submit" class="btn-search" value="<?php echo esc_attr_x( 'Search', 'submit button', 'outstock' ); ?>" /><i class=" ion ion-ios-search"></i>
			</div>
			<input type="hidden" name="post_type" value="product" />
		</div>
	<?php }else{ ?>
		<div class="search-content">
			<label class="screen-reader-text" for="woocommerce-product-search-field-<?php echo esc_attr($real_id); ?>"><?php esc_html_e( 'Search for:', 'outstock' ); ?></label>
			<input type="search" id="woocommerce-product-search-field-<?php echo esc_attr($real_id); ?>" class="search-field" placeholder="<?php echo esc_attr_x( 'Search Products&hellip;', 'placeholder', 'outstock' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'outstock' ); ?>" />
			<input type="submit" class="btn-search" value="<?php echo esc_attr_x( 'Search', 'submit button', 'outstock' ); ?>" />
			<input type="hidden" name="post_type" value="product" />
		</div>
	<?php } ?>
</form>