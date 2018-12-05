<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices(); ?>
<div class="row">
	<div class="col-xs-12 col-sm-3 myaccount-navigation">
		<?php 
			do_action( 'woocommerce_account_navigation' );
		?>
	</div>
	<div class="col-xs-12 col-sm-9 myaccount-content">
		<?php
			do_action( 'woocommerce_account_content' );
		?>
	</div>
</div>
