<?php
/**
 * The template for displaying posts in the Image post format
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
 
$lionthemes_banner = '';
if(get_post_meta( get_the_ID(), 'lionthemes_page_banner', true )){
	$lionthemes_banner = get_post_meta( get_the_ID(), 'lionthemes_page_banner', true );
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<?php if(!$lionthemes_banner){ ?>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header><!-- .entry-header -->
	<?php } ?>
	<div class="entry-content">
		<?php the_content(); ?> 
		<div class="clearfix"></div>
		<?php wp_link_pages(array(
			'before' => '<div class="page-links"><span>' . esc_html__('Pages:', 'outstock') . '</span><ul class="pagination">',
			'after'  => '</ul></div>',
			'separator' => ''
		)); ?>
	</div><!-- .entry-content -->
	
	<footer class="entry-meta">
		<?php outstock_bootstrap_edit_post_link(); ?> 
	</footer>
</article><!-- #post-## -->