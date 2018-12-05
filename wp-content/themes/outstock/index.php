<?php
/**
 * The main template file
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */

get_header();

/**
 * determine main column size from actived sidebar
 */

$outstock_opt = get_option( 'outstock_opt' );

$blogsidebar = is_active_sidebar( 'blog' ) ? 'right' : '';
if (isset($outstock_opt['sidebarblog_pos'])) {
	if (is_active_sidebar( 'blog' ) && $outstock_opt['sidebarblog_pos']){
		$blogsidebar = $outstock_opt['sidebarblog_pos'];
	}else{
		$blogsidebar = '';
	}
}
if(isset($_GET['side']) && $_GET['side']!=''){
	$blogsidebar = $_GET['side'];
}
switch($blogsidebar) {
	case 'right':
	case 'left':
		$blogclass = 'blog-sidebar';
		$blogcolclass = 9;
		break;
	default:
		$blogclass = 'blog-nosidebar';
		$blogcolclass = 12;
		$blogsidebar = '';
}

$coldata = 1;
if(!isset($outstock_opt['blog_column'])){
	$blogcolumn = 'col-sm-12';
	$col_class = 'one';
}else{
	$blogcolumn = 'col-sm-' . $outstock_opt['blog_column'];
	switch($outstock_opt['blog_column']) {
		case 6:
			$col_class = 'two';
			$coldata = 2;
			break;
		case 4:
			$col_class = 'three';
			$coldata = 3;
			break;
		case 3:
			$col_class = 'four';
			$coldata = 4;
			break;
		default:
			$col_class = 'one';
			$coldata = 1;
	}
	
}
if(isset($_GET['col']) && $_GET['col']!=''){
	$col = $_GET['col'];
	switch($col) {
		case 2:
			$blogcolumn = 'col-sm-6';
			$col_class = 'two';
			$coldata = 2;
			break;
		case 3:
			$blogcolumn = 'col-sm-4';
			$col_class = 'three';
			$coldata = 3;
			break;
		case 4:
			$blogcolumn = 'col-sm-3';
			$col_class = 'four';
			$coldata = 4;
			break;
		default:
			$blogcolumn = 'col-sm-12';
			$col_class = 'one';
			$coldata = 1;
	}
}

$outstock_opt['blogcolumn'] = $blogcolumn;

update_option( 'outstock_opt', $outstock_opt );
global $wp_query;  
$page_id = $wp_query->get_queried_object_id();
?>
<div id="main-content">
	<?php if(get_post_meta( $page_id, 'lionthemes_page_banner', true )){ ?>
		<?php do_action( 'lionthemes_page_banner' ); ?>
	<?php } else { ?>
		<div class="container base-design breadcrumb-wrapper">
			<?php echo outstock_breadcrumb(); ?>
		</div>
		<?php if(!empty($outstock_opt['blog_header_text'])) { ?>
		<header class="entry-header">
			<div class="container">
				<h1 class="entry-title"><?php echo esc_html($outstock_opt['blog_header_text']); ?></h1>
			</div>
		</header>
		<?php } ?>
	<?php } ?>
	<div class="container">
		<div class="row">
			<?php if($blogsidebar == 'left') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
				<div class="col-xs-12 <?php echo 'col-md-'.$blogcolclass; ?> content-area" id="main-column">
					<main id="main" class="blog-page blog-<?php echo esc_attr($col_class); ?>-column<?php echo ($blogsidebar != 'none') ? '-' . esc_attr($blogsidebar) : ''; ?> site-main">
						<?php if (have_posts()) { ?> 
						<div class="row<?php echo ($coldata > 1) ? ' auto-grid':''; ?>" data-col="<?php echo esc_attr($coldata) ?>">
						<?php 
						// start the loop
						while (have_posts()) {
							the_post();
							get_template_part('content', get_post_format());
						}// end while
						?> 
						</div>
						<?php outstock_bootstrap_pagination(); ?>
						<?php } else { ?> 
						<?php get_template_part('no-results', 'index'); ?>
						<?php } // endif; ?> 
					</main>
				</div>
			<?php if($blogsidebar == 'right') :?>
				<?php get_sidebar('blog'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?> 