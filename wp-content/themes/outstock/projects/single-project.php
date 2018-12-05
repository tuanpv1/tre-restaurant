<?php
/**
 * The Template for displaying all single projects.
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'projects' ); ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php projects_get_template_part( 'content', 'single-project' ); ?>

		<?php endwhile; // end of the loop. ?>

<?php get_footer( 'projects' );