<?php
/**
 * Template for dispalying single post (read full post page).
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */

get_header();

/**
 * determine main column size from actived sidebar
 */
$outstock_opt = get_option( 'outstock_opt' );
$side = is_active_sidebar( 'blog' ) ? 'right' : '';
if (isset($outstock_opt['sidebarblog_pos'])) {
	if (is_active_sidebar( 'blog' ) && $outstock_opt['sidebarblog_pos']){
		$side = $outstock_opt['sidebarblog_pos'];
	}else{
		$side = '';
	}
}
?>
<div id="main-content">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<?php outstock_breadcrumb(); ?>
			</div>
			<?php if($side=='left') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
				<div class="col-xs-12<?php echo ( $side ) ? ' col-md-9' : ''; ?> content-area" id="main-column">
					<main id="main" class="site-main single-post-content">
						<?php 
						while (have_posts()) {
							the_post();
							
							get_template_part('content', get_post_format());

							echo "\n\n";
							
							outstock_bootstrap_pagination();

							echo "\n\n";
							
							// If comments are open or we have at least one comment, load up the comment template
							if (comments_open() || '0' != get_comments_number()) {
								comments_template();
							}

							echo "\n\n";
							
							do_action( 'lionthemes_track_view_count' , get_the_ID());
						} //endwhile;
						?> 
					</main>
				</div>
			<?php if($side=='right') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?> 