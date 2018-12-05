<?php 
get_header(); 
$outstock_opt = get_option( 'outstock_opt' );
?> 

	<div class="page-404">
		<div class="container text-center">
			<article>
				<h1><?php esc_html_e('404', 'outstock'); ?></h1>
				
				<div class="error-content">
					<?php if(!empty($outstock_opt['404-content'])){ ?>
					<?php echo wp_kses($outstock_opt['404-content'], array(
								'a' => array(
									'href' => array(),
									'title' => array()
								),
								'div' => array(
									'class' => array(),
								),
								'img' => array(
									'src' => array(),
									'alt' => array()
								),
								'h1' => array(
									'class' => array(),
								),
								'h2' => array(
									'class' => array(),
								),
								'h3' => array(
									'class' => array(),
								),
								'h4' => array(
									'class' => array(),
								),
								'ul' => array(),
								'li' => array(),
								'i' => array(
									'class' => array()
								),
								'br' => array(),
								'em' => array(),
								'strong' => array(),
								'p' => array(),
								)); ?>
					<?php } else { ?>
						<h3><?php echo esc_html__('Component not found', 'outstock') ?></h3>
						<h2><?php echo esc_html__('Oh my gosh! You found it!!!', 'outstock') ?></h2>
						<p><?php echo esc_html__('The page are looking for has moved or does not exist anymore, If you like you can return our homepage.', 'outstock') ?></p>
					<?php } ?>
				</div>
				
				<div class="button-group">
					<a class="btn" href="<?php echo home_url( '/' ) ?>"><?php echo esc_html__('Return to Home', 'outstock') ?></a>
					<a class="btn primary-bg" href="<?php echo home_url( '/shop' ) ?>"><?php echo esc_html__('Continue Shopping', 'outstock') ?></a>
				</div>
			</article>
		</div>

	</div>
	

<?php get_footer(); ?> 