<?php
/**
 * The template for displaying posts in the Image post format
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
$outstock_opt = get_option( 'outstock_opt' );
$blogcolumn = (isset($outstock_opt['blogcolumn'])) ? $outstock_opt['blogcolumn'] : '';
if(is_single()) $blogcolumn = '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class($blogcolumn); ?>>
	<div class="post-wrapper">
		
		<?php if ( ! post_password_required() && ! is_attachment() ) : ?>
		<?php 
			if ( is_single() ) { ?>
				<?php if ( has_post_thumbnail() ) { ?>
					<div class="post-thumbnail">
						<?php the_post_thumbnail(); ?>
					</div>
				<?php } ?>
			<?php }
		?>
		<?php if ( !is_single() ) { ?>
			<?php if ( has_post_thumbnail() ) { ?>
			<div class="post-thumbnail">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('outstock-post-thumb'); ?></a>
			</div>
			<?php } ?>
		<?php } ?>
		<?php endif; ?>
		
		<div class="post-info<?php if ( !has_post_thumbnail() ) { echo ' no-thumbnail';} ?>">
			<header class="entry-header">
				<?php if(get_the_title()){ ?>
					<?php if ( !is_single() ) { ?>
					<h3 class="entry-title">
						<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h3>
					<?php }else{ ?>
						<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php } ?>
				<?php } ?>

				<ul class="post-entry-data">
					<?php if(!is_single() && !get_the_title()){ ?>
					<li class="post-date"><a href="<?php the_permalink(); ?>"><?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ) ?></a></li>
					<?php } else { ?>
					<li class="post-date"><?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ) ?></li>
					<?php } ?>
					<li class="post-comments"><?php echo sprintf(esc_html__('%d Comment(s)', 'outstock'), get_comments_number( $post->ID )) ?></li>
				</ul>
			</header>

			
			<?php if (is_search()) { // Only display Excerpts for Search ?> 
			<div class="entry-summary">
				<?php the_excerpt(); ?> 
				<div class="clearfix"></div>
			</div><!-- .entry-summary -->
			<?php } else { ?> 
				<?php if ( is_single() ) : ?>
					<div class="entry-content">
						<?php the_content( esc_html__( 'Continue reading <span class="meta-nav">&rarr;</span>', 'outstock' ) ); ?>
						<?php wp_link_pages(array(
							'before' => '<div class="page-links"><span>' . esc_html__('Pages:', 'outstock') . '</span><ul class="pagination">',
							'after'  => '</ul></div>',
							'separator' => ''
						)); ?>
					</div>
				<?php else : ?>
					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div>
					<a class="readmore-link" href="<?php the_permalink(); ?>"><?php echo (!empty($outstock_opt['readmore_text'])) ? esc_html($outstock_opt['readmore_text']): esc_html__('Read more', 'outstock'); ?></a>
				<?php endif; ?>
			<?php } //endif; ?> 

			<?php if ( is_single() ){ ?>
			<footer class="entry-meta">
				<?php if ('post' == get_post_type()) { // Hide category and tag text for pages on Search ?> 
				<div class="entry-meta-category-tag">
					<?php
						/* translators: used between list items, there is a space after the comma */
						$categories_list = get_the_category_list(esc_html__(', ', 'outstock'));
						if (!empty($categories_list)) {
					?> 
					<span class="cat-links">
						<?php echo outstock_bootstrap_categories_list($categories_list); ?> 
					</span>
					<?php } // End if categories ?> 

					<?php
						/* translators: used between list items, there is a space after the comma */
						$tags_list = get_the_tag_list('', esc_html__(', ', 'outstock'));
						if ($tags_list) {
					?> 
					<span class="tags-links">
						<?php echo outstock_bootstrap_tags_list($tags_list); ?> 
					</span>
					<?php } // End if $tags_list ?> 
				</div>
				<?php } // End if 'post' == get_post_type() ?> 

				<div class="entry-counter">
					<div class="post-comments" title="<?php echo esc_html__('Total Comments', 'outstock') ?>" data-toggle="tooltip"><i class="fa fa-comments"></i><?php echo get_comments_number( get_the_ID() ) ?></div>
					<?php do_action( 'lionthemes_view_count_button' , get_the_ID()); ?>
					<?php do_action( 'lionthemes_like_button' , get_the_ID()); ?>
				</div>
				<?php if( is_single() ) { ?>
					<?php do_action( 'lionthemes_end_single_post' ); ?>
				<?php } ?>
			</footer>
			<?php } ?>
		</div>
	</div>
</article><!-- #post-## -->