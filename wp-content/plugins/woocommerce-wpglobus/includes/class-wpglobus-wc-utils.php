<?php

/**
 * @package WPGlobus_WC
 */
class WPGlobus_WC_Utils {

	/**
	 * Get WC taxonomy type
	 *
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public static function taxonomy_type( $taxonomy ) {
		$result = array();
		if ( $taxonomy == 'product_cat' ) {
			$result['type']  = 'product';
			$result['title'] = 'Product category';
			$result['name']  = 'product_cat';
		} elseif ( false !== strpos( $taxonomy, 'pa_' ) ) {
			/** @global wpdb $wpdb */
			global $wpdb;
			$result['type']  = 'product_attribute';
			$result['title'] = 'Product attribute';
			$result['name']  = str_replace( 'pa_', '', $taxonomy );

			$r =
				$wpdb->get_results( $wpdb->prepare( "SELECT attribute_id, attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $result['name'] ) );

			$result['attribute_id'] = $r[0]->attribute_id;
			$result['source']       = $r[0]->attribute_label;

		} else {
			$result['type']  = 'product_child';
			$result['title'] = 'Product category';
			$result['name']  = $taxonomy;
		}

		return $result;
	}

	/**
	 * Get a product attributes label.
	 *
	 * @param string $by
	 * @param string $attr
	 *
	 * @return string
	 */
	public static function attribute_label_by( $by = 'id', $attr = '' ) {

		//		if ( 'name' == $by ) {
		//			return wc_attribute_label($name);
		//		}

		if ( 'id' == $by ) {
			if ( ! $attr ) {
				return '';
			}

			/** @global wpdb $wpdb */
			global $wpdb;

			$label =
				$wpdb->get_var( $wpdb->prepare( "SELECT attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %s;", $attr ) );

			return $label;
		}

		return '';
	}

	/**
	 * Get family chain
	 *
	 * @param stdClass $parent_term
	 * @param stdClass $child_term
	 * @param array  $terms
	 *
	 * @return string
	 */
	public static function get_family_chain( $parent_term, $child_term, $terms ) {

		$chain = $parent_term->slug . ' >> ';
		$find  = $child_term->parent;

		foreach ( $terms as $term ) {
			if ( $term->term_id == $find ) {
				$chain .= $term->slug . ' >> ';
				$find = $term->term_id;
			}
		}

		$chain .= $child_term->slug;

		return $chain;

	}

	/**
	 * Check for enabled post_types, taxonomies
	 *
	 * @param string[] $enabled_entities
	 * @param          $entity String
	 *
	 * @return bool
	 */
	public static function enabled_entity( $enabled_entities, $entity = '' ) {
		if ( empty( $entity ) ) {
			/**
			 * Try to get entity from url. Ex. edit-tags.php?taxonomy=product_cat&post_type=product
			 */
			if ( ! empty( $_GET['post_type'] ) ) {
				$entity = $_GET['post_type'];
			} elseif ( ! empty( $_GET['taxonomy'] ) ) {
				$entity = $_GET['taxonomy'];
			}
		}

		return in_array( $entity, $enabled_entities );
	}

} // class

# --- EOF
