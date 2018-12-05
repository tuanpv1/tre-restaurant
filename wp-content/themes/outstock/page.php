<?php
/**
 * The template for default page
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
global $wp_query;  
$page_id = $wp_query->get_queried_object_id();
?> 
<div id="main-content">
	<?php if(get_post_meta( $page_id, 'lionthemes_page_banner', true )){ ?>
		<?php do_action( 'lionthemes_page_banner' ); ?>
	<?php } else { ?>
	<div class="container base-design breadcrumb-wrapper">
		<?php echo outstock_breadcrumb(); ?>
	</div>
	<?php } ?>
	<div class="container">
		<div class="row">
			<div class="col-xs-12 content-area" id="main-column">
				<main id="main" class="site-main">
					<?php 
					while (have_posts()) {
						the_post();

						get_template_part('content', 'page');

						echo "\n\n";
						
						// If comments are open or we have at least one comment, load up the comment template
						if (comments_open() || '0' != get_comments_number()) {
							comments_template();
						}

						echo "\n\n";

					} //endwhile;
					?> 
				</main>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?> 