<?php
/**
 * Single product short description
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;
$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );
if ( !$short_description ) {
	return;
}

?>
<div itemprop="description">
	<?php echo ''. $short_description ?>
</div>
