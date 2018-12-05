<?php
/**
 * Result Count
 *
 * Shows text: Showing x - x of x results.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/result-count.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( ! woocommerce_products_will_display() )
	return;
?>
<p class="woocommerce-result-count">
	<?php
	if ( $total <= $per_page || -1 === $per_page ) {
		printf( _n( 'Showing the single result', 'Showing all %d results', $total, 'outstock' ), $total );
	} else {
		$first    = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );
		printf( _nx( 'Showing the single result', 'Showing %1$d&ndash;<span>%2$d</span> of %3$d results', $total, '%1$d = first, %2$d = last, %3$d = total', 'outstock' ), $first, $last, $total );
	}
	?>
</p>
