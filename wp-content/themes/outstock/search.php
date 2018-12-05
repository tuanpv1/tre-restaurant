<?php
/**
 * The template for displaying search results.
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

$blogsidebar = '';
if(!empty($outstock_opt['sidebarblog_pos'])) {
	$blogsidebar = $outstock_opt['sidebarblog_pos'];
}
$blogcolumn = 'col-sm-12';
$coldata = 1;
if (!empty($outstock_opt['blog_column'])) {
	$blogcolumn = 'col-sm-' . $outstock_opt['blog_column'];
	switch($outstock_opt['blog_column']) {
	case 6:
		$coldata = 2;
		break;
	case 4:
		$coldata = 3;
		break;
	case 3:
		$coldata = 4;
		break;
	default:
		$coldata = 1;
	}
}
?> 
<div id="main-content">
	<div class="container base-design breadcrumb-wrapper">
		<?php echo outstock_breadcrumb(); ?>
	</div>
	<div class="container">
		<div class="row">
			<?php if($blogsidebar=='left' && is_active_sidebar( 'blog' )) :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
				<div class="col-xs-12 <?php if ( is_active_sidebar( 'blog' ) && $blogsidebar ) : ?>col-md-9<?php endif; ?> content-area" id="main-column">
					<main id="main" class="site-main">
						<?php if (have_posts()) { ?> 
						<header class="page-header">
							<h1 class="page-title"><?php printf(esc_html__('Search Results for: %s', 'outstock'), '<span>' . get_search_query() . '</span>'); ?></h1>
						</header><!-- .page-header -->
						<div class="row<?php echo ($coldata > 1) ? ' auto-grid':''; ?>" data-col="<?php echo esc_attr($coldata) ?>">
						<?php 
						// start the loop
						while (have_posts()) {
							the_post();
							
							/* Include the Post-Format-specific template for the content.
							* If you want to override this in a child theme, then include a file
							* called content-___.php (where ___ is the Post Format name) and that will be used instead.
							*/
							get_template_part('content', 'search');
						}// end while
						echo '</div>';
						outstock_bootstrap_pagination();
						?> 
						<?php } else { ?> 
						<?php get_template_part('no-results', 'search'); ?>
						<?php } // endif; ?> 
					</main>
				</div>
			<?php if($blogsidebar=='right' && is_active_sidebar( 'blog' )) :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?> 