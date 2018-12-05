<?php
/**
 * Template Name: Fullwidth Template
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
get_header(); 
?>
	<div id="main-content" class="is_fullwidth home-template">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php 
				the_content(); 
			?>
			
		<?php endwhile; // end of the loop. ?>
	</div>
<?php
get_footer();
