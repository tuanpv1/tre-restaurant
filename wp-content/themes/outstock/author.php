<?php
/**
 * The template for displaying Author Archive pages
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */

get_header();

$outstock_opt = get_option( 'outstock_opt' );
$side = is_active_sidebar( 'blog' ) ? 'right' : '';
if (isset($outstock_opt['sidebarblog_pos'])) {
	if (is_active_sidebar( 'blog' ) && $outstock_opt['sidebarblog_pos']){
		$side = $outstock_opt['sidebarblog_pos'];
	}else{
		$side = '';
	}
}
$coldata = 1;
if(!isset($outstock_opt['blog_column'])){
	$blogcolumn = 'col-sm-12';
	$col_class = 'one';
}else{
	$blogcolumn = 'col-sm-' . $outstock_opt['blog_column'];
	switch($outstock_opt['blog_column']) {
		case 6:
			$col_class = 'two';
			$coldata = 2;
			break;
		case 4:
			$col_class = 'three';
			$coldata = 3;
			break;
		case 3:
			$col_class = 'four';
			$coldata = 4;
			break;
		default:
			$col_class = 'one';
			$coldata = 1;
	}
	
}
$outstock_opt['blogcolumn'] = $blogcolumn;

update_option( 'outstock_opt', $outstock_opt );

?>
<div class="main-container page-wrapper">
	<div class="container base-design breadcrumb-wrapper">
		<?php echo outstock_breadcrumb(); ?>
	</div>
	<div class="container">
		<div class="row">
			<?php if(!empty($outstock_opt['blog_header_text'])) { ?>
				<header class="entry-header">
					<div class="container">
						<h1 class="entry-title"><?php echo esc_html($outstock_opt['blog_header_text']); ?></h1>
					</div>
				</header>
			<?php } ?>
			<?php if($side=='left') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
			<div class="col-xs-12<?php echo ( $side ) ? ' col-md-9' : ''; ?>">
				<div class="page-content blog-page grid-layout">
					<?php if ( have_posts() ) : ?>

						<?php
							/* Queue the first post, that way we know
							 * what author we're dealing with (if that is the case).
							 *
							 * We reset this later so we can run the loop
							 * properly with a call to rewind_posts().
							 */
							the_post();
						?>

						<header class="archive-header">
							<h1 class="archive-title"><?php printf( esc_html__( 'Author Archives: %s', 'outstock' ), '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( "ID" ) ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' ); ?></h1>
						</header><!-- .archive-header -->

						<?php
							/* Since we called the_post() above, we need to
							 * rewind the loop back to the beginning that way
							 * we can run the loop properly, in full.
							 */
							rewind_posts();
						?>

						<?php
						// If a user has filled out their description, show a bio on their entries.
						if ( get_the_author_meta( 'description' ) ) : ?>
						<div class="author-info archives">
							<div class="author-avatar">
								<?php
								/**
								 * Filter the author bio avatar size.
								 *
								 * @since Outstock Themes 1.2
								 *
								 * @param int $size The height and width of the avatar in pixels.
								 */
								$author_bio_avatar_size = apply_filters( 'outstock_author_bio_avatar_size', 68 );
								echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
								?>
							</div><!-- .author-avatar -->
							<div class="author-description">
								<h2><?php printf( esc_html__( 'About %s', 'outstock' ), get_the_author() ); ?></h2>
								<p><?php the_author_meta( 'description' ); ?></p>
							</div><!-- .author-description	-->
						</div><!-- .author-info -->
						<?php endif; ?>

						<?php /* Start the Loop */ ?>
						<div class="grid-wrapper">
						<div id="shufflegrid" class="row<?php echo ($coldata > 1) ? ' auto-grid':''; ?>" data-col="<?php echo esc_attr($coldata) ?>">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_template_part( 'content', get_post_format() ); ?>
						<?php endwhile; ?>
						</div>
						</div>
						<div class="pagination">
							<?php outstock_bootstrap_pagination(); ?>
						</div>

					<?php else : ?>
						<?php get_template_part( 'content', 'none' ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php if($side=='right') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
		</div>
		
	</div>
</div>
<?php get_footer(); ?>