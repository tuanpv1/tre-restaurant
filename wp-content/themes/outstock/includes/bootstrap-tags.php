<?php
/**
 * Custom template tags for this theme
*
* @package WordPress
* @subpackage Outstock_theme
* @since Outstock Themes 1.2
 */


if (!function_exists('outstock_bootstrap_categories_list')) {
	/**
	 * Display categories list with bootstrap icon
	 * 
	 * @param string $categories_list list of categories.
	 * @return string
	 */
	function outstock_bootstrap_categories_list($categories_list = '') 
	{
		return sprintf('<span class="cat-title">' . esc_html__('Categories:', 'outstock') .  '</span> %1$s', $categories_list);
	}// outstock_bootstrap_categories_list
}


if (!function_exists('outstock_bootstrap_comment')) {
	/**
	 * Displaying a comment
	 * 
	 * @param object $comment
	 * @param array $args
	 * @param integer $depth
	 * @return string the content already echo.
	 */
	function outstock_bootstrap_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;

		if ('pingback' == $comment->comment_type || 'trackback' == $comment->comment_type) { 
			echo '<li id="comment-';
				comment_ID();
				echo '" ';
				comment_class('comment-type-pt');
			echo '>';
			echo '<div class="comment-body media">';
				echo '<div class="media-body">';
					esc_html_e('Pingback:', 'outstock');
					comment_author_link(); 
					edit_comment_link(esc_html__('Edit', 'outstock'), '<span class="edit-link">', '</span>');
				echo '</div>';
			echo '</div>';
		} else {
			echo '<li id="comment-';
				comment_ID();
				echo '" ';
				comment_class(empty($args['has_children']) ? '' : 'parent media');
			echo '>';

			echo '<article id="div-comment-';
				comment_ID();
			echo '" class="comment-body media">';

				// footer
				echo '<footer class="comment-meta pull-left">';
					if (0 != $args['avatar_size']) {
						echo get_avatar($comment, $args['avatar_size']);
					}
				echo '</footer><!-- .comment-meta -->';
				// end footer

				// comment content
				echo '<div class="comment-content media-body">';
					echo '<div class="comment-author vcard">';
						echo '<div class="comment-metadata">';

						// date-time
						echo '<a href="';
							echo esc_url(get_comment_link($comment->comment_ID));
						echo '">';
						echo '<time datetime="';
							comment_time('c');
						echo '">';
						printf(_x('%1$s at %2$s', '1: date, 2: time', 'outstock'), get_comment_date(), get_comment_time());
						echo '</time>';
						echo '</a>';
						// end date-time

						echo ' ';

						edit_comment_link('<span class="fa fa-pencil-square-o "></span>' . esc_html__('Edit', 'outstock'), '<span class="edit-link">', '</span>');

						echo '</div><!-- .comment-metadata -->';

						// if comment was not approved
						if ('0' == $comment->comment_approved) {
							echo '<div class="comment-awaiting-moderation text-warning"> <span class="glyphicon glyphicon-info-sign"></span> ';
								esc_html_e('Your comment is awaiting moderation.', 'outstock');
							echo '</div>';
						} //endif;

						// comment author says
						printf(wp_kses(__('%s <span class="says">says:</span>', 'outstock'), array('span')), sprintf('<cite class="fn">%s</cite>', get_comment_author_link()));
					echo '</div><!-- .comment-author -->';

					// comment content body
					comment_text();
					// end comment content body

					// reply link
					comment_reply_link(array_merge($args, array(
						'add_below' => 'div-comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'reply_text' => '<span class="fa fa-reply"></span> ' . esc_html__('Reply', 'outstock'),
						'login_text' => '<span class="fa fa-reply"></span> ' . esc_html__('Log in to Reply', 'outstock')
					)));
					// end reply link
				echo '</div><!-- .comment-content -->';
				// end comment content



			echo '</article><!-- .comment-body -->';
		} //endif;
	}// outstock_bootstrap_comment
}


if (!function_exists('outstock_bootstrap_comments_popup_link')) {
	/**
	 * Custom comment popup link
	 * 
	 * @return string
	 */
	function outstock_bootstrap_comments_popup_link() 
	{
		$comment_icon = '<span class="comment-icon glyphicon glyphicon-comment"><small class="comment-total">%d</small></span>';
		$comments_icon = '<span class="comment-icon glyphicon glyphicon-comment"><small class="comment-total">%s</small></span>';
		return comments_popup_link(sprintf($comment_icon, ''), sprintf($comment_icon, '1'), sprintf($comments_icon, '%'), 'btn btn-default btn-xs');
	}// outstock_bootstrap_comments_popup_link
}


if (!function_exists('outstock_bootstrap_edit_post_link')) {
	/**
	 * Display edit post link
	 */
	function outstock_bootstrap_edit_post_link() 
	{
		if ( is_user_logged_in() ){
			if( current_user_can( 'manage_options' ) ){
				$edit_post_link = get_edit_post_link();
				$edit_btn = '<a class="post-edit-link btn btn-default btn-xs" href="'.esc_url($edit_post_link).'" title="' . esc_attr__('Edit', 'outstock') . '"><i class="edit-post-icon glyphicon glyphicon-pencil" title="' . esc_attr__('Edit', 'outstock') . '"></i></a>';
				unset($edit_post_link);
				echo '' . $edit_btn;
			}
		}
	}// outstock_bootstrap_edit_post_link
}


if (!function_exists('outstock_bootstrap_fullpage_search_form')) {
	/**
	 * Display full page search form
	 * 
	 * @return string the search form element
	 */
	function outstock_bootstrap_fullpage_search_form() 
	{
		$output = '<form class="form-horizontal" method="get" action="' . esc_url(home_url('/')) . '">';
		$output .= '<div class="form-group">';
		$output .= '<div class="col-xs-10">';
		$output .= '<input type="text" name="s" value="' . esc_attr(get_search_query()) . '" placeholder="' . esc_attr_x('Search &hellip;', 'placeholder', 'outstock') . '" title="' . esc_attr_x('Search &hellip;', 'label', 'outstock') . '" class="form-control" />';
		$output .= '</div>';
		$output .= '<div class="col-xs-2">';
		$output .= '<button type="submit" class="btn btn-default">' . esc_html__('Search', 'outstock') . '</button>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</form>';

		return $output;
	}// outstock_bootstrap_fullpage_search_form
}


if (!function_exists('outstock_bootstrap_get_link_in_content')) {
	/**
	 * get the link in content
	 * 
	 * @return string
	 */
	function outstock_bootstrap_get_link_in_content() 
	{
		$content = get_the_content();
		$has_url = get_url_in_content($content);

		if ($has_url) {
			return $has_url;
		} else {
			return apply_filters('the_permalink', get_permalink());
		}
	}// outstock_bootstrap_get_link_in_content
}


if (!function_exists('outstock_bootstrap_more_link_text')) {
	/**
	 * Custom more link (continue reading) text
	 * @return string
	 */
	function outstock_bootstrap_more_link_text() 
	{
		return wp_kses(__('Continue reading <span class="meta-nav">&rarr;</span>', 'outstock'), array('span'));
	}// outstock_bootstrap_more_link_text
}


if (!function_exists('outstock_bootstrap_pagination')) {
	/**
	 * display pagination (1 2 3 ...) instead of previous, next of wordpress style.
	 * 
	 * @param string $pagination_align_class
	 * @return string the content already echo
	 */
	function outstock_bootstrap_pagination($pagination_align_class = 'pagination-center pagination-row') 
	{
		global $wp_query;
			$big = 999999999;
			$pagination_array = paginate_links(array(
				'base' => str_replace($big, '%#%', get_pagenum_link($big)),
				'format' => '/page/%#%',
				'current' => max(1, get_query_var('paged')),
				'total' => $wp_query->max_num_pages,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'type' => 'array'
			));

			unset($big);

			if (is_array($pagination_array) && !empty($pagination_array)) {
				echo '<nav class="' . $pagination_align_class . '">';
				echo '<ul class="pagination">';
				foreach ($pagination_array as $page) {
					echo '<li';
					if (strpos($page, '<a') === false && strpos($page, '&hellip;') === false) {
						echo ' class="active"';
					}
					echo '>';
					if (strpos($page, '<a') === false && strpos($page, '&hellip;') === false) {
						echo '<span>' . $page . '</span>';
					} else {
						echo '' . $page;
					}
					echo '</li>';
				}
				echo '</ul>';
				echo '</nav>';
			}

			unset($page, $pagination_array);
	}// outstock_bootstrap_pagination
}


if (!function_exists('outstock_bootstrap_post_on')) {
	/**
	 * display post date/time and author
	 * 
	 * @return string
	 */
	function outstock_bootstrap_post_on() 
	{
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		/* if (get_the_time('U') !== get_the_modified_time('U')) {
			$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
		} */

		$time_string = sprintf($time_string,
			esc_attr(get_the_date('c')),
			esc_html(get_the_date()),
			esc_attr(get_the_modified_date('c')),
			esc_html(get_the_modified_date())
		);

		printf(wp_kses(__('<span class="posted-on">Posted on %1$s</span><span class="byline"> by %2$s</span>', 'outstock'), array('span')),
			sprintf('<a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>',
				esc_url(get_permalink()),
				esc_attr(get_the_time()),
				$time_string
			),
			sprintf('<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
				esc_url(get_author_posts_url(get_the_author_meta('ID'))),
				esc_attr(sprintf(esc_html__('View all posts by %s', 'outstock'), get_the_author())),
				esc_html(get_the_author())
			)
		);
	}// outstock_bootstrap_post_on
}


if (!function_exists('outstock_bootstrap_tags_list')) {
	/**
	 * display tags list
	 * 
	 * @param string $tags_list
	 * @return string
	 */
	function outstock_bootstrap_tags_list($tags_list = '') 
	{
		return sprintf('<span class="tags-title">'. esc_html__( 'Tags:', 'outstock' ) .'</span>&nbsp; %1$s', $tags_list);
	}// outstock_bootstrap_tags_list
}


if (!function_exists('outstock_bootstrap_attached_image')) {
	/**
	 * Display attach image with link.
	 * 
	 * @return string image element with link.
	 */
	function outstock_bootstrap_attached_image() 
	{
		$post                = get_post();
		$attachment_size     = apply_filters('bootstrap_basic_attachment_size', array(1140, 1140));
		$next_attachment_url = wp_get_attachment_url();

		/**
		 * Grab the IDs of all the image attachments in a gallery so we can get the
		 * URL of the next adjacent image in a gallery, or the first image (if
		 * we're looking at the last image in a gallery), or, in a gallery of one,
		 * just the link to that image file.
		 */
		$attachment_ids = get_posts(array(
			'post_parent'    => $post->post_parent,
			'fields'         => 'ids',
			'numberposts'    => -1,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'menu_order ID'
		));

		// If there is more than 1 attachment in a gallery...
		if (count($attachment_ids) > 1) {
			foreach ($attachment_ids as $attachment_id) {
				if ($attachment_id == $post->ID) {
					$next_id = current($attachment_ids);
					break;
				}
			}


			if ($next_id) {
				// get the URL of the next image attachment...
				$next_attachment_url = get_attachment_link($next_id);
			} else {
				// or get the URL of the first image attachment.
				$next_attachment_url = get_attachment_link(array_shift($attachment_ids));
			}
		}

		printf('<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
			esc_url($next_attachment_url),
			the_title_attribute(array('echo' => false)),
			wp_get_attachment_image($post->ID, $attachment_size, false, array('class' => 'img-responsive aligncenter'))
		);
	}// outstock_bootstrap_attached_image
}