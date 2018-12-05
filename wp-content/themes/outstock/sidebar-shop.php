<?php if (is_active_sidebar('shop')) { ?> 
				<div class="col-md-3 sidebar-shop" id="secondary">
					<?php do_action('before_sidebar'); ?> 
					<?php dynamic_sidebar('shop'); ?> 
				</div>
<?php } ?> 