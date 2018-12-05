<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */
?>
<?php 
$outstock_opt = get_option( 'outstock_opt' );
?>
	<div class="footer layout6">
		<?php if ( !empty($outstock_opt['back_to_top'])) { ?>
			<div id="back-top" class="hidden-xs"><i class="fa fa-angle-double-up"></i></div>
		<?php } ?>
		<?php if(is_active_sidebar('footer_2columns_left') || is_active_sidebar('footer_2columns_right') ){ ?>
		<div class="footer-top">
			<div class="container">
				<div class="row">
					
					<?php if(is_active_sidebar('footer_2columns_left')){ ?>
						<div class="col-md-6 col-sm-6">
							<?php dynamic_sidebar('footer_2columns_left'); ?>
						</div>
					<?php } ?>
					
					<?php
					if(is_active_sidebar('footer_2columns_right')) {
						dynamic_sidebar('footer_2columns_right');
					} ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="footer-bottom">
			<div class="container">
				<div class="row">
					<div class="col-sm-6">
						<div class="widget-copyright">
							<?php 
							if( !empty($outstock_opt['copyright']) ) {
								echo wp_kses($outstock_opt['copyright'], array(
									'a' => array(
										'href' => array(),
										'title' => array()
									),
									'br' => array(),
									'em' => array(),
									'strong' => array(),
								));
							} else {
								echo 'Copyright <a href="'.esc_url( home_url( '/' ) ).'">'.get_bloginfo('name').'</a> '.date('Y').'. All Rights Reserved';
							}
							?>
						</div>
					</div>
					<?php if(!empty($outstock_opt['social_icons'])) { ?>
					<div class="col-sm-6">
						<?php
							echo '<ul class="link-follow">';
							foreach($outstock_opt['social_icons'] as $key=>$value ) {
								if($value!=''){
									if($key=='vimeo'){
										echo '<li><a class="'.esc_attr($key).' social-icon" href="'.esc_url($value).'" title="'.ucwords(esc_attr($key)).'" target="_blank"><i class="fa fa-vimeo-square"></i></a></li>';
									} else {
										echo '<li><a class="'.esc_attr($key).' social-icon" href="'.esc_url($value).'" title="'.ucwords(esc_attr($key)).'" target="_blank"><i class="fa fa-'.esc_attr($key).'"></i></a></li>';
									}
								}
							}
							echo '</ul>';
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	