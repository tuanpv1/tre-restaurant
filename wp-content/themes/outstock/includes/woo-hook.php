<?php
//WooCommerce Hook

//add brands after product detail page
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );

add_action( 'woocommerce_before_main_content', 'outstock_woocommerce_category_image', 2 );

//remove product link before - after product inner
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
// hook before mini cart
add_action('woocommerce_before_mini_cart', 'outstock_woocommerce_before_mini_cart');
function outstock_woocommerce_before_mini_cart(){
	$qty = WC()->cart->get_cart_contents_count();
	?>
	<div class="topcart">
		<div class="icon-cart-header">
			<i class="ion ion-bag"></i>
		</div>
		<a class="cart-toggler" href="javascript:void(0)"></i><?php echo esc_html__('Cart', 'outstock') ?>(<span class="qty"><?php echo esc_html($qty); ?></span>)</a>
		<div class="topcart_content">
	<?php
}
// hook after mini cart
add_action('woocommerce_after_mini_cart', 'outstock_woocommerce_after_mini_cart');
function outstock_woocommerce_after_mini_cart(){
	?>
	</div></div>
	<?php
}

// Add image to category description
function outstock_woocommerce_category_image() {
	if ( is_product_category() ){
		global $wp_query;
		
		$cat = $wp_query->get_queried_object();
		$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
		$image = wp_get_attachment_url( $thumbnail_id );
		if ( $image ) {
			echo '<div class="image-wrap"><img src="' . esc_url($image) . '" alt="" /></div>';
				echo '<div class="page-banner-content">';
				echo '<h1 class="category-title entry-title">'. $cat->name .'</h1>';
				woocommerce_breadcrumb();
			echo '</div>';
		}else{
			echo '<div class="container">';
			echo '<h1 class="category-title entry-title">'. $cat->name .'</h1>';
			woocommerce_breadcrumb();
			echo '</div>';
		}
	}
}
// hook to custom gallery thumbnail images size in product page
add_filter ('woocommerce_get_image_size_gallery_thumbnail', 'outstock_get_image_size_gallery_thumbnail');
function outstock_get_image_size_gallery_thumbnail($size) {
	$outstock_opt = get_option( 'outstock_opt' );
	if (!empty($outstock_opt['gallery_thumbnail_size'])) {
		$size['width'] = $outstock_opt['gallery_thumbnail_size']['width'];
		$size['height'] = $outstock_opt['gallery_thumbnail_size']['height'];
		if (!$size['height']) {
			$size['crop']   = 0;
		}
	}
	return $size;
}

function outstock_ourbrands(){
	echo do_shortcode( '[ourbrands rows="1" colsnumber="6" style="carousel"]' );
}

add_action( 'wp_ajax_outstock_product_remove', 'outstock_product_remove' );
add_action( 'wp_ajax_nopriv_outstock_product_remove', 'outstock_product_remove' );
function outstock_product_remove(){
    global $wpdb, $woocommerce;
	$cart = WC()->instance()->cart;
	if(!empty($_POST['remove_item'])){
	   $cart->remove_cart_item($_POST['remove_item']);
	}
	$qty = WC()->cart->get_cart_contents_count();
	$subtotal = WC()->cart->get_cart_subtotal();
    echo json_encode(array(
			'qty'=> intval($qty), 
			'subtotal' => strip_tags($subtotal),
			'qtycount' => intval($qty)
		));
    die();
}


//quickview ajax
add_action( 'wp_ajax_product_quickview', 'outstock_product_quickview' );
add_action( 'wp_ajax_nopriv_product_quickview', 'outstock_product_quickview' );

function outstock_product_quickview() {
	global $product, $post, $woocommerce_loop, $outstock_opt;
	if($_POST['data']){
		$productid = intval( $_POST['data'] );
		$product = wc_get_product( $productid );
		$post = get_post( $productid );
	}
	?>
	<div class="woocommerce product">
		<div class="product-images">
			<?php $image_link = wp_get_attachment_url( $product->get_image_id() );?>
			<div class="main-image"><img src="<?php echo esc_attr($image_link);?>" alt="" /></div>
			<?php
			$attachment_ids = $product->get_gallery_image_ids();

			if ( $attachment_ids ) {
				?>
				<div class="quick-thumbnails">
					<?php $image_link = wp_get_attachment_url( $product->get_image_id() );?>
					<div>
						<a href="<?php echo esc_attr($image_link);?>">
							<?php echo wp_kses($product->get_image('shop_thumbnail'),array(
								'img'=>array(
									'src'=>array(),
									'alt'=>array(),
									'class'=>array(),
									'id'=>array()
								)
							));?>
						</a>
					</div>
					<?php

					$loop = 0;
					$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

					foreach ( $attachment_ids as $attachment_id ) {
						?>
						<div>
						<?php
						$classes = array( 'zoom' );

						if ( $loop == 0 || $loop % $columns == 0 )
							$classes[] = 'first';

						if ( ( $loop + 1 ) % $columns == 0 )
							$classes[] = 'last';

						$image_link = wp_get_attachment_url( $attachment_id );

						if ( ! $image_link )
							continue;

						$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
						$image_class = esc_attr( implode( ' ', $classes ) );
						$image_title = esc_attr( get_the_title( $attachment_id ) );

						echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<a href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]">%s</a>', $image_link, $image_class, $image_title, $image ), $attachment_id, $product->get_id(), $image_class );

						$loop++;
						?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			} ?>
		</div>
		<div class="product-info">
			<h1><?php echo esc_html($product->get_title()); ?></h1>
			
			<div class="price-box" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				<p class="price">
					<?php echo wp_kses($product->get_price_html(),array(
						'p'=>array(
							'class'=>array()
						),
						'span'=>array(
							'class'=>array()
						)
					));?>
				</p>
			</div>
			
			<a class="see-all" href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($outstock_opt['quickview_link_text']); ?></a>
			<div class="quick-add-to-cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>
			<div class="quick-desc"><?php echo do_shortcode(get_post($productid)->post_excerpt); ?></div>
			<?php do_action('lionthemes_quickview_after_product_info'); ?>
		</div>
	</div>
	<?php
	die();
}

// Count number of products from shortcode
add_filter( 'woocommerce_shortcode_products_query', 'outstock_woocommerce_shortcode_count');
function outstock_woocommerce_shortcode_count( $args ) {
	global $outstock_opt, $outstock_productsfound;
	$outstock_productsfound = new WP_Query($args);
	$outstock_productsfound = $outstock_productsfound->post_count;
	return $args;
}

// number products per page
add_filter( 'loop_shop_per_page', 'outstock_shop_per_page', 20 );
function outstock_shop_per_page() {
	global $outstock_opt;
	return $outstock_opt['product_per_page'];
}

//WooProjects - Project organize
remove_action( 'projects_before_single_project_summary', 'projects_template_single_title', 10 );
add_action( 'projects_single_project_summary', 'projects_template_single_title', 5 );
remove_action( 'projects_before_single_project_summary', 'projects_template_single_short_description', 20 );
remove_action( 'projects_before_single_project_summary', 'projects_template_single_gallery', 40 );
add_action( 'projects_single_project_gallery', 'projects_template_single_gallery', 40 );


//re-order product detail summery
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );


function outstock_ajax_add_to_cart_button(){
	global $product;
	
	if ( $product ) {
		echo '<p class="add_to_cart_inline">';
		$defaults = array(
			'quantity' => 1,
			'class'    => implode( ' ', array_filter( array(
					'button',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			) ) ),
			'attributes' => array(
				'data-product_id'  => $product->get_id(),
				'data-product_sku' => $product->get_sku(),
				'aria-label'       => $product->add_to_cart_description(),
				'rel'              => 'nofollow',
			),
		);
		$args = array();
		$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

		wc_get_template( 'loop/add-to-cart.php', $args );
		echo '</p>';
	}
}

function outstock_get_product_schema(){
	return ((is_ssl()) ? 'https' : 'http') . '://schema.org/Product';
}
?>
