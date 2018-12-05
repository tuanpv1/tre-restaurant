<?php
/**
 * Project Loop Start
 *
 * @package WordPress
 * @subpackage Outstock_Themes
 * @since Outstock Themes 1.2
 */
 
?>
<?php
	global $outstock_opt;

	$col = $outstock_opt['portfolio_columns'];

	if (isset($_GET['col'])){
		$col = (int)$_GET['col'];
	}
	$col = ($col > 0) ? $col : 3;
?>
<div id="projects_list" class="auto-grid" data-col="<?php echo esc_attr($col); ?>">