<?php
/**
 * The sidebar containing the main widget area
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
?>

<?php if ( is_active_sidebar( 'page' ) ) : ?>
	<div class="col-md-3" id="sidebar-page">
		<?php do_action('before_sidebar'); ?> 
		<?php dynamic_sidebar( 'page' ); ?>
	</div><!-- #sidebar -->
<?php endif; ?>