<?php
/**
 * The Header template for our theme
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
?>
<?php 

$outstock_opt = get_option( 'outstock_opt' );

?>


<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
</head>
<?php
	$outstock_layout = (isset($outstock_opt['page_layout']) && $outstock_opt['page_layout'] == 'box') ? 'box-layout':'';
	$outstock_header = (empty($outstock_opt['header_layout']) || $outstock_opt['header_layout'] == 'default') ? 'first': $outstock_opt['header_layout'];
	if(get_post_meta( get_the_ID(), 'lionthemes_header_page', true )){
		$outstock_header = get_post_meta( get_the_ID(), 'lionthemes_header_page', true );
	}
	if(get_post_meta( get_the_ID(), 'lionthemes_layout_page', true ) && !is_search()){
		$outstock_layout = (get_post_meta( get_the_ID(), 'lionthemes_layout_page', true ) == 'box') ? 'box-layout' : '';
	}
?>
<body <?php body_class(); ?>>
<div class="main-wrapper <?php echo esc_attr($outstock_layout); ?>">
<?php do_action('before'); ?> 
	<header>
	<?php
		get_header($outstock_header);
	?>
	</header>
	<div id="content" class="site-content">