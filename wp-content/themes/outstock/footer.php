<?php
/**
 * The template for displaying the footer
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
?>
<?php 
	$outstock_opt = get_option( 'outstock_opt' ); 
	$outstock_footer = (!isset($outstock_opt['footer_layout']) || $outstock_opt['footer_layout'] == 'default') ? 'first' : $outstock_opt['footer_layout'];
	if(get_post_meta( get_the_ID(), 'lionthemes_footer_page', true )){
		$outstock_footer = get_post_meta( get_the_ID(), 'lionthemes_footer_page', true );
	}
	$content_layout = '';
	if(get_the_ID()){
		$content_layout = get_post_meta( get_the_ID(), 'lionthemes_content_layout', true );
	}
?>
		
		</div><!--.site-content-->
		<footer id="site-footer" class="<?php echo ($content_layout) ? esc_attr('footer_' . $content_layout):''; ?>">
			<?php
				get_footer($outstock_footer);
			?>
		</footer>
	</div><!--.main wrapper-->
	<?php wp_footer(); ?>
</body>
</html>