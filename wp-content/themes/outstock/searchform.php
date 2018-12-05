<?php
/**
 * Template for displaying search form in theme
 *
 * @package WordPress
 * @subpackage Outstock_theme
 * @since Outstock Themes 1.2
 */
?>
<form method="get" class="search-form form" action="<?php echo esc_url(home_url('/')); ?>">
	<label for="form-search-input" class="sr-only"><?php _ex('Search for', 'label', 'outstock'); ?></label>
	<div class="input-group">
		<input type="search" id="form-search-input" class="form-control" placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder', 'outstock'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s" title="<?php echo esc_attr_x('Search for:', 'label', 'outstock'); ?>">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-default"><?php esc_html_e('Search', 'outstock'); ?></button>
		</span>
	</div>
</form>