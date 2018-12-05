<?php
/**
 * The template for displaying project content in the single-project.php template
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $wpdb, $outstock_opt;

?>
<div class="main-container">
	<div class="entry-header">
		<div class="container">
			<h1 class="entry-title"><?php esc_html_e('Portfolio', 'outstock');?></h1>
		</div>
	</div>
	<div class="container">
		<?php
			/**
			 * projects_before_single_project hook
			 *
			 */
			 do_action( 'projects_before_single_project' );
		?>

		<div id="project-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<div class="row">
				<?php $attachment_ids = projects_get_gallery_attachment_ids(); ?>
				<?php if ( $attachment_ids || has_post_thumbnail()) { ?>
				<div class="col-xs-12 col-sm-12 col-md-6">
					<div class="cover-image">
						<?php the_post_thumbnail( 'full' ); ?>
					</div>
					<?php do_action( 'projects_single_project_gallery' ); ?>
				</div>
				<?php } ?>
				<div class="col-xs-12 col-md-<?php if ( $attachment_ids || has_post_thumbnail()) { echo '6';} else {echo '12';} ?>">
					<h3 class="project-title"><?php the_title() ?></h3>
					
					<div class="entry-summary">
						<?php
							the_content();
						?>
					</div>
					
					<?php
						/* translators: used between list items, there is a space after the comma */
						$prcates = get_the_terms(get_the_ID(), 'project-category' );
						$datagroup = array();
						if($prcates){
							foreach ($prcates as $category ) {
								$datagroup[] = '<a href="' . esc_url(get_term_link( $category )) . '" title="'. $category->name .'">'. $category->name .'</a>';
							}
						}
					?> 
					<div class="project-meta">
						<div class="project_date ">
							<p><strong><?php echo esc_html__('Date', 'outstock') ?></strong></p>
							<span class="day"><?php echo get_the_date(esc_html__('d', 'outstock')); ?></span>
							<span class="separator">/</span>
							<span class="month"><?php echo get_the_date(esc_html__('M', 'outstock')); ?></span>
						</div>
					
					
						<?php if (!empty($datagroup)) { ?>
						<div class="categories-entry">
							<p class="cat-label"><strong><?php echo esc_html__('Categories', 'outstock') ?></strong></p> <?php echo implode(", ", $datagroup); ?> 
						</div>
						<?php } // End if categories ?> 
						
						<?php $client = esc_attr( get_post_meta( get_the_ID(), '_client', true ) ); ?>
						<?php if($client){ ?>
						<div class="client-entry">
							<p class="client-label"><strong><?php echo esc_html__('Client', 'outstock') ?></strong></p> <?php echo esc_html($client); ?>
						</div>
						<?php } ?>
						<?php $url = esc_attr( get_post_meta( get_the_ID(), '_url', true ) ); ?>
						<?php if($url){ ?>
						<div class="url-entry">
							<p class="link-label"><strong><?php echo esc_html__('Link', 'outstock') ?></strong></p> <a href="<?php echo esc_url($url) ?>"><?php echo esc_html__('Visit project', 'outstock') ?></a>
						</div>
						<?php } ?>
						<?php $testimonial = esc_attr( get_post_meta( get_the_ID(), '_testimonials_search', true ) ); ?>
						<?php if($testimonial){ ?>
						<div class="testimonial-entry">
							<p class="testimonial-label"><strong><?php echo esc_html__('Testimonial', 'outstock') ?></strong></p> <?php echo esc_html($testimonial); ?>
						</div>
						<?php } ?>
						<div class="social-sharing clearfix">
							<?php do_action( 'lionthemes_end_single_post' ); ?>
							<?php do_action( 'lionthemes_like_button' , get_the_ID()); ?>
						</div>
					</div>
				</div>
			</div>
		</div><!-- #project-<?php the_ID(); ?> -->

		<?php
			/**
			 * projects_after_single_project hook
			 *
			 * @hooked projects_single_pagination - 10
			 */
			//do_action( 'projects_after_single_project' );
		?>
	<?php
		$include_categories = array();
		$terms = get_the_terms($post->ID, 'project-category' );
		foreach ($terms as $term) {
			$include_categories[] = $term->term_id;
		}
		$args = array(
			'post_type'				=> 'project',
			'post_status' 			=> 'publish',
			'post__not_in'			=> array($post->ID),
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> 4,
			'orderby' 				=> 'date',
			'order' 				=> 'DESC',
			'tax_query' 			=> array(
										array(
											'taxonomy' 	=> 'project-category',
											'field' 	=> 'id',
											'terms' 	=> $include_categories,
											'operator' 	=> 'IN'
										)
									)
		);
		ob_start();
		$projects = new WP_Query( $args );
		if ( $projects->have_posts() ) : ?>
		<div class="related_projects">
			<h3 class="related-title"><?php echo esc_html($outstock_opt['related_project_title']); ?></h3>
				<div data-owl="slide" data-desksmall="3" data-tablet="2" data-mobile="1" data-tabletsmall="2" data-item-slide="4" data-margin="30" data-ow-rtl="false" class="owl-carousel owl-theme projects-slide">
				<?php while ( $projects->have_posts() ) : $projects->the_post(); ?>
					<div class="item-related">
							<?php if ( has_post_thumbnail() ) {
								$image       		= get_the_post_thumbnail( $post->ID, 'large' );
								$image_title 		= get_the_title( get_post_thumbnail_id() );
							?>
							<div class="project-thumbnail">
								<?php echo wp_kses($image, array(
									'img'=>array(
										'src'=>array(),
										'height'=>array(),
										'width'=>array(),
										'class'=>array(),
										'alt'=>array(),
									)
								));?>
								<div class="icon-group">
									<div class="project-link"><a data-toggle="tooltip" title="<?php echo esc_html__('View more', 'outstock') ?>" href="<?php the_permalink(); ?>" class="project-permalink"><i class="fa fa-link"></i></a></div>
									<?php do_action( 'lionthemes_like_button' , get_the_ID()); ?>
								</div>
								
							</div>
							<div class="project-info">
								<h3 class="project-title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h3>
								<div class="project-date"><?php echo get_the_date( get_option( 'date_format' ), get_the_ID() ); ?></div>
							</div>
							
							<?php } ?>
					</div>

				<?php endwhile; // end of the loop. ?>
				</div>
		</div>
		<?php endif; wp_reset_postdata(); ?>
	</div>
</div>