<?php
function lionthemes_mailchimpform_shortcode( $atts ) {

	$atts = shortcode_atts( array(
							'title' => '',
							'id' => '',
							'short_text' => '',
							'style' => '',
							), $atts, 'lionthemes_mailchimpform' );
	extract($atts);
	
	if(empty($id)) return;
	
	ob_start();
	?>
	<div class="mailchimpform">
		<?php if($title){ ?>
		<h3><?php echo $title ?></h3>
		<?php } ?>
		<?php if($short_text){ ?>
		<div class="short_text"><?php echo urldecode(base64_decode($short_text)); ?></div>
		<?php } ?>
		<?php echo do_shortcode('[mc4wp_form id="'. intval($id) .'"]'); ?>
	</div>
	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}
add_shortcode( 'lionthemes_mailchimpform', 'lionthemes_mailchimpform_shortcode' );
?>