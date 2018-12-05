<?php if (is_active_sidebar('blog')) { ?> 
				<div class="sidebar-blog col-md-3" id="secondary">
					<?php do_action('before_sidebar'); ?> 
					<?php dynamic_sidebar('blog'); ?> 
				</div>
<?php } ?> 