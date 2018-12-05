<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */
 
$outstock_opt = get_option( 'outstock_opt' );
$logo = ( !empty($outstock_opt['logo_main']['url']) ) ? $outstock_opt['logo_main']['url'] : '';
if(get_post_meta( get_the_ID(), 'outstock_logo_page', true )){
	$logo = get_post_meta( get_the_ID(), 'outstock_logo_page', true );
}
?>
	<div class="header-container layout4">
			<div class="header">
				<div class="container">
				<div class="row">
					<div class="col-md-3 col-sm-3 col-xs-12 col-logo">
						<?php if( $logo ){ ?>
							<div class="logo pull-left"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"/></a></div>
						<?php
						} else { ?>
							<h1 class="logo  pull-left"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
							<?php
						} ?>
						
					</div>
					<div class="col-md-9 col-sm-9 col-xs-12 col-search">
						<div class="nav-menus pull-left">
							<div class="nav-desktop visible-lg visible-md">
								<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container_class' => 'primary-menu-container', 'menu_class' => 'nav-menu' ) ); ?>
							</div>
							
							<div class="nav-mobile visible-xs visible-sm">
								<div class="mobile-menu-overlay"></div>
								<div class="toggle-menu"><i class="fa fa-bars"></i></div>
								<div class="mobile-navigation">
									<?php wp_nav_menu( array( 'theme_location' => 'mobilemenu', 'container_class' => 'mobile-menu-container', 'menu_class' => 'nav-menu mobile-menu' ) ); ?>
								</div>
							</div>
						</div>
							<div class="action pull-right">
							<?php if(is_active_sidebar('top_header')){ ?>
								<div class="header-top-setting pull-right">
									<i class="ion ion-navicon"> </i>
									<div class="setting-container">
									<?php if (is_active_sidebar('top_header')) { ?> 
										<?php dynamic_sidebar('top_header'); ?> 
									<?php } ?>
									</div>
								</div>
							<?php } ?>
							<?php if(class_exists('WC_Widget_Cart')) { ?>
								<div class="shoping_cart pull-right">
								<?php the_widget('WC_Widget_Cart'); ?>
								</div>
							<?php } ?>	
							<?php if(class_exists('WC_Widget_Product_Search')) { ?>
							<div class="top-search pull-right">
								<div class="dropdown">
									<div class="dropdown-toggle">
										<div class="top-search">
											<a href="javascript:void(0)"><i class="ion ion-ios-search-strong"></i><?php echo esc_html__('Search', 'outstock') ?></a>
										</div>
									</div>
									<div class="search-container">
										<?php the_widget('WC_Widget_Product_Search', array('title' => '')); ?>
									</div>
								</div>
							</div>
							<?php } ?>
							</div>
					</div>
				</div>
			</div>
			</div>
		<div class="clearfix"></div>
	</div>