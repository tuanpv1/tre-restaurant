<?php
/**
* Theme specific widgets
*
* @package WordPress
* @subpackage LionThemes
* @since LionThemes 1.0.0
*/
 
/**
 * Register widgets
 *
 * @return void
 */
function lionthemes_widgets_init() {
	register_widget( 'Lionthemes_Widget_Post' );
	register_widget( 'Lionthemes_Widget_Recent_Comment' );
}
add_action( 'widgets_init', 'lionthemes_widgets_init' ); 


//custom blog widget
class Lionthemes_Widget_Post extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'description' => esc_html__( 'Lionthemes recent post', 'lionthemes' )
		);
		parent::__construct( 'lionthemes_recent_post', esc_html__( 'Lionthemes - Recent Post', 'lionthemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		if ( empty( $instance['number'] ) || !$number = absint( $instance['number'] ) ) {
			$number = 10;
		}
		$args_sql = array(
			'post_type' => 'post', 
			'numberposts' => $number,
			'post_status' => 'publish, future',
			'date_query' => array(
				array(
				   'before' => date('Y-m-d H:i:s', current_time( 'timestamp' ))
				)
			 )
		);
		$recents = wp_get_recent_posts($args_sql);
		
		if ( !empty($recents) ){
			echo $before_widget;
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo '<ul>';
			foreach( $recents as $recent ){ ?>
				<li>
					<a class="post-thumbnail pull-left<?php echo (!get_the_post_thumbnail( $recent["ID"], 'thumbnail' )) ? ' no-thumb':''; ?>" href="<?php echo get_permalink($recent["ID"]); ?>">
						<?php echo get_the_post_thumbnail( $recent["ID"], 'thumbnail' ); ?>
					</a>
					<div class="post-info media-body">
						<a class="post-title" href="<?php echo get_permalink($recent["ID"]); ?>">
							<?php echo esc_html($recent["post_title"]); ?>
						</a>
						<span class="post-date"><?php echo get_the_date(get_option( 'date_format' ), $recent["ID"]); ?></span>
					</div>
				</li>
			<?php }
			echo '</ul>';
			echo $after_widget;
		}
		
	}
	// widget options
	function form( $instance ){
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 10;
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php echo esc_html__( 'Title:', 'lionthemes' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php echo esc_html__( 'Number of post to show:', 'lionthemes' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" /></p>
		<?php
	}
}

//custom recent comment widget
class Lionthemes_Widget_Recent_Comment extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'description' => esc_html__( 'Lionthemes recent comment', 'lionthemes' )
		);
		parent::__construct( 'lionthemes_recent_comment', esc_html__( 'Lionthemes - Recent Comment', 'lionthemes' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		if ( empty( $instance['number'] ) || !$number = absint( $instance['number'] ) ) {
			$number = 10;
		}
		$args = array();
		$args['post_type'] = empty( $instance['post_type'] ) ? '' : $instance['post_type'];
		$args['status'] = 'approve';
		$args['number'] = $number;
		$comments = get_comments($args);
		if ( !empty($comments) ){
			echo $before_widget;
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			echo '<ul>';
			foreach( $comments as $comment ){ ?>
				<li>
					<div class="avatar pull-left"><?php echo get_avatar( $comment->comment_author_email ) ?></div>
					<div class="comment_info media-body">
						<p class="author"><?php echo esc_html($comment->comment_author) ?></p>
						<p class="comment_content"><?php echo wp_trim_words( $comment->comment_content, $num_words = 5, $more = '...' ) ?></p>
						<p class="on_post"><?php echo esc_html__('on', 'lionthemes') ?> <a href="<?php echo get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID; ?>"><?php echo get_the_title($comment->comment_post_ID) ?></a></p>
					</div>
				</li>
			<?php }
			echo '</ul>';
			echo $after_widget;
		}
		
	}
	// widget options
	function form( $instance ){
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 10;
		$post_type = isset( $instance['post_type'] ) ? esc_attr( $instance['post_type'] ) : '';
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php echo esc_html__( 'Title:', 'lionthemes' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php echo esc_html__( 'Number of post to show:', 'lionthemes' ); ?></label>
		<input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" /></p>
		
		<p><label for="<?php echo esc_attr($this->get_field_id( 'post_type' )); ?>"><?php echo esc_html__( 'Type of list:', 'lionthemes' ); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id( 'post_type' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'post_type' )); ?>">
				<option value=""><?php echo esc_html__('All', 'lionthemes' ) ?></option>
				<option value="product" <?php echo ($post_type == 'product') ? 'selected="selected"': ''; ?>><?php echo esc_html__('Products', 'lionthemes' ) ?></option>
				<option value="post" <?php echo ($post_type == 'post') ? 'selected="selected"': ''; ?>><?php echo esc_html__('Post', 'lionthemes' ) ?></option>
			</select>
		</p>
		
		<?php
	}
}
