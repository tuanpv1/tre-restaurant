<?php
/**
 * Single Project Image
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $projects, $project;

?>
<div class="project-gallery">

	<?php

		$attachment_ids = projects_get_gallery_attachment_ids();

		if ( $attachment_ids ) { ?>

			<?php
				$loop = 0;
				$columns = apply_filters( 'projects_project_gallery_columns', 3 ); ?>
				
				<div class="sub-images owl-carousel owl-theme">
				<?php
				foreach ( $attachment_ids as $attachment_id ) {
				
					$classes = array( 'zoom' );

					if ( $loop == 0 || $loop % $columns == 0 )
						$classes[] = 'first';

					if ( ( $loop + 1 ) % $columns == 0 )
						$classes[] = 'last';

					$image_link = wp_get_attachment_url( $attachment_id );

					if ( ! $image_link )
						continue;
		
					
					$image       = wp_get_attachment_image( $attachment_id );
					$image_class = esc_attr( implode( ' ', $classes ) );
					$image_title = esc_attr( get_the_title( $attachment_id ) );

					if ( apply_filters( 'projects_gallery_link_images', true ) ) {
						echo '<a class="prfancybox" rel="gallery1" href="' . esc_url($image_link) . '" title="' . esc_attr($image_title) . '">' . $image . '</a>';
					} else {
						echo wp_kses($image, array(
							'a'=>array(
								'href'=>array(),
								'title'=>array(),
								'class'=>array(),
							),
							'img'=>array(
								'src'=>array(),
								'height'=>array(),
								'width'=>array(),
								'class'=>array(),
								'alt'=>array(),
							)
						));
					}
					
					$loop++;

				} // endforeach ?>
			</div>
		<?php } // endif ?>

</div>
