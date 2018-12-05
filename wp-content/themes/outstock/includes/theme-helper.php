<?php

// All Outstock theme helper functions in here
function outstock_woocommerce_query($type, $post_per_page=-1, $cat=''){
	$args = outstock_woocommerce_query_args($type,$post_per_page,$cat);
	return new WP_Query($args);
}
function outstock_vc_custom_css_class( $param_value, $prefix = '' ) {
	$css_class = preg_match( '/\s*\.([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $param_value ) ? $prefix . preg_replace( '/\s*\.([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', '$1', $param_value ) : '';
	return $css_class;
}
function outstock_woocommerce_query_args($type,$post_per_page=-1,$cat=''){
	global $woocommerce;
    remove_filter( 'posts_clauses', array( $woocommerce->query, 'order_by_popularity_post_clauses' ) );
	$product_visibility_term_ids = wc_get_product_visibility_term_ids();
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $post_per_page,
        'post_status' => 'publish',
		'date_query' => array(
				array(
				   'before' => date('Y-m-d H:i:s', current_time( 'timestamp' ))
				)
			 ),
		 'tax_query' => array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
				'operator' => 'NOT IN',
			)
		 ),
		 'post_parent' => 0
    );
    switch ($type) {
        case 'best_selling':
            $args['meta_key']='total_sales';
            $args['orderby']='meta_value_num';
            $args['ignore_sticky_posts']   = 1;
            $args['meta_query'] = array();
            break;
        case 'featured_product':
            $args['ignore_sticky_posts'] = 1;
            $args['meta_query'] = array();
            $args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['featured'],
			);
            break;
        case 'top_rate':
            $args['meta_key']='_wc_average_rating';
            $args['orderby']='meta_value_num';
            $args['order']='DESC';
            $args['meta_query'] = array();
            break;
        case 'recent_product':
            $args['meta_query'] = array();
            break;
        case 'on_sale':
            $args['meta_query'] = array();
            $args['post__in'] = wc_get_product_ids_on_sale();
            break;
        case 'recent_review':
            if($post_per_page == -1) $_limit = 4;
            else $_limit = $post_per_page;
            global $wpdb;
            $query = "SELECT c.comment_post_ID FROM {$wpdb->posts} p, {$wpdb->comments} c WHERE p.ID = c.comment_post_ID AND c.comment_approved > 0 AND p.post_type = 'product' AND p.post_status = 'publish' AND p.comment_count > 0 ORDER BY c.comment_date ASC LIMIT 0, %d";
            $safe_sql = $wpdb->prepare( $query, $_limit );
			$results = $wpdb->get_results($safe_sql, OBJECT);
            $_pids = array();
            foreach ($results as $re) {
                $_pids[] = $re->comment_post_ID;
            }

            $args['meta_query'] = array();
            $args['post__in'] = $_pids;
            break;
        case 'deals':
            $args['meta_query'] = array();
            $args['meta_query'][] = array(
                                 'key' => '_sale_price_dates_to',
                                 'value' => '0',
                                 'compare' => '>');
            $args['post__in'] = wc_get_product_ids_on_sale();
            break;
    }

    if($cat!=''){
        $args['product_cat']= $cat;
    }
    return $args;
}
function outstock_make_id($length = 5){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
//Change excerpt length
add_filter( 'excerpt_length', 'outstock_excerpt_length', 999 );
function outstock_excerpt_length( $length ) {
	global $outstock_opt;
	if(isset($outstock_opt['excerpt_length'])){
		return $outstock_opt['excerpt_length'];
	}
	return 22;
}
function outstock_get_the_excerpt($post_id) {
	global $post;
	$temp = $post;
    $post = get_post( $post_id );
    setup_postdata( $post );
    $excerpt = get_the_excerpt();
    wp_reset_postdata();
    $post = $temp;
    return $excerpt;
}

//Add breadcrumbs
function outstock_breadcrumb() {
	global $post, $outstock_opt;
	$month_format = (!empty($outstock_opt['blog_archive_month_format'])) ? esc_html($outstock_opt['blog_archive_month_format']) : 'F, Y';
	$year_format = (!empty($outstock_opt['blog_archive_year_format'])) ? esc_html($outstock_opt['blog_archive_year_format']) : 'Y';
	$brseparator = '<span class="separator">/</span>';
	if (!is_home()) {
		echo '<div class="breadcrumbs">';
		
		echo '<a href="';
		echo esc_url( home_url( '/' ) );
		echo '">';
		echo esc_html__('Home', 'outstock');
		echo '</a>'.$brseparator;
		if (is_category() || is_single()) {
			the_category($brseparator);
			if (is_single()) {
				echo ''.$brseparator;
				the_title();
			}
		} elseif (is_page()) {
			if($post->post_parent){
				$anc = get_post_ancestors( $post->ID );
				$title = get_the_title();
				foreach ( $anc as $ancestor ) {
					$output = '<a href="'. esc_url(get_permalink($ancestor)).'" title="'.esc_attr(get_the_title($ancestor)).'">'. esc_html(get_the_title($ancestor)) .'</a>'.$brseparator;
				}
				echo wp_kses($output, array(
						'a'=>array(
							'href' => array(),
							'title' => array()
						),
						'span'=>array(
							'class'=>array()
						)
					)
				);
				echo '<span title="'.esc_attr($title).'"> '.esc_html($title).'</span>';
			} else {
				echo '<span> '. esc_html(get_the_title()).'</span>';
			}
		}
		elseif (is_tag()) {single_tag_title();}
		elseif (is_day()) {echo "<span>" . sprintf(esc_html__('Archive for %s', 'outstock'), get_the_time(get_option( 'date_format' ))) . '</span>';}
		elseif (is_month()) {echo "<span>" . sprintf(esc_html__('Archive for %s', 'outstock'), get_the_time($month_format)) . '</span>';}
		elseif (is_year()) {echo "<span>" . sprintf(esc_html__('Archive for %s', 'outstock'), get_the_time($year_format)) . '</span>';}
		elseif (is_author()) {echo "<span>" . esc_html__('Author Archive', 'outstock'); echo '</span>';}
		elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {echo "<span>" . esc_html__('Blog Archives', 'outstock'); echo '</span>';}
		elseif (is_search()) {echo "<span>" . esc_html__('Search Results', 'outstock'); echo '</span>';}
		
		echo '</div>';
	} else {
		echo '<div class="breadcrumbs">';
		
		echo '<a href="';
		echo esc_url( home_url( '/' ) );
		echo '">';
		echo esc_html__('Home', 'outstock');
		echo '</a>'.$brseparator;
		
		if(isset($outstock_opt['blog_header_text']) && $outstock_opt['blog_header_text']!=""){
			echo esc_html($outstock_opt['blog_header_text']);
		} else {
			echo esc_html__('Blog', 'outstock');
		}
		
		echo '</div>';
	}
}

//add quickview container
add_action( 'wp_footer', 'outstock_quickview_container');
function outstock_quickview_container(){
	
	echo '<div class="quickview-wrapper"><div class="overlay-bg" onclick="hideQuickView()"></div><div class="quick-modal"><span class="qvloading"></span><span class="closeqv"><i class="fa fa-times"></i></span><div id="quickview-content"></div><div class="clearfix"></div></div></div>';
}
?>