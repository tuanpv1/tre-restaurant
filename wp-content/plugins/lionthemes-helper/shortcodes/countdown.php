<?php
function outstock_countdown_shortcode( $atts ) {
	global $outstock_opt;

	$atts = shortcode_atts( array(
							'title' => '',
							'short_desc' => '',
							'datetime' => '',
							'el_class' => ''
							), $atts, 'ourbrands' );
	extract($atts);
	
	if(!$datetime) return;
	
	$datetime = strtotime($datetime);

	$current_date = current_time( 'timestamp' );
	
	if($datetime <= $current_date) return;
	
	$timestemp_left = $datetime - $current_date;
	
	ob_start();
	echo '<div class="countdown_widget '. esc_attr($el_class) .'">';
	echo ($title) ? '<h3 class="vc_widget_title vc_countdown_title"><span>'. esc_html($title) .'</span></h3>' : '';
	echo ($short_desc) ? '<div class="short_desc">'. wpautop($short_desc) .'</div>' : '';
	if($timestemp_left > 0){
		$day_left = floor($timestemp_left / (24 * 60 * 60));
		$hours_left = floor(($timestemp_left - ($day_left * 60 * 60 * 24)) / (60 * 60));
		$mins_left = floor(($timestemp_left - ($day_left * 60 * 60 * 24) - ($hours_left * 60 * 60)) / 60);
		$secs_left = floor($timestemp_left - ($day_left * 60 * 60 * 24) - ($hours_left * 60 * 60) - ($mins_left * 60));
		?>
		<div class="deals-countdown">
			<span class="countdown-row">
				<span class="countdown-section">
					<span class="countdown-val days_left"><?php echo $day_left; ?></span>
					<span class="countdown-label"><?php echo esc_html__('Days', 'outstock'); ?></span>
				</span>
				<span class="countdown-section">
					<span class="countdown-val hours_left"><?php echo $hours_left; ?></span>
					<span class="countdown-label"><?php echo esc_html__('Hrs', 'outstock'); ?></span>
				</span>
				<span class="countdown-section">
					<span class="countdown-val mins_left"><?php echo $mins_left; ?></span>
					<span class="countdown-label"><?php echo esc_html__('Mins', 'outstock'); ?></span>
				</span>
				<span class="countdown-section">
					<span class="countdown-val secs_left"><?php echo $secs_left; ?></span>
					<span class="countdown-label"><?php echo esc_html__('Secs', 'outstock'); ?></span>
				</span>
			</span>
		</div>
	<?php } 
	echo '</div>';
	$content = ob_get_contents();
	ob_end_clean();
	
	return $content;
}
add_shortcode( 'outstock_countdown', 'outstock_countdown_shortcode' );
?>