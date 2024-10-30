<?php if (!defined('ABSPATH')) {exit;}
// 1.0.2 (07-04-2023)
// Maxim Glazunov (https://icopydoc.ru)
// This code adds several useful functions to the WooCommerce.
// 'import-products-to-ok-ru' - slug for translation (be sure to make an autocorrect)

/**
 * @since 1.0.0
 *
 * @return string/null
 *
 * Возвращает версию Woocommerce
 */ 
if (!function_exists('get_woo_version_number')) {
	function get_woo_version_number() {
		// If get_plugins() isn't available, require it
		if (!function_exists('get_plugins')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php');
		}
		// Create the plugins folder and file variables
		$plugin_folder = get_plugins('/' . 'woocommerce');
		$plugin_file = 'woocommerce.php';
		
		// If the plugin version number is set, return it 
		if (isset($plugin_folder[$plugin_file]['Version'])) {
			return $plugin_folder[$plugin_file]['Version'];
		} else {	
			return null;
		}
	}
}

/**
 * @since 1.0.0
 *
 * @return array
 *
 * Получает все атрибуты вукомерца 
 */
if (!function_exists('get_woo_attributes')) {
	function get_woo_attributes() {
		$result_arr = [];
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if (count($attribute_taxonomies) > 0) {
			$i = 0;
			foreach($attribute_taxonomies as $one_tax ) {
				/**
				* $one_tax->attribute_id => 6
				* $one_tax->attribute_name] => слаг (на инглише или русском)
				* $one_tax->attribute_label] => Еще один атрибут (это как раз название)
				* $one_tax->attribute_type] => select 
				* $one_tax->attribute_orderby] => menu_order
				* $one_tax->attribute_public] => 0			
				*/
				$result_arr[$i]['id'] = $one_tax->attribute_id;
				$result_arr[$i]['name'] = $one_tax->attribute_label;
				$i++;
			}
		}
		return $result_arr;
	}
}

/**
 * @since 1.0.0 (07-04-2023)
 *
 * @param string 		$term_name (not require)
 * @param int 			$term_id (not require)
 * @param array 		$value_arr (not require) - id выбранных ранее глобальных атрибутов
 * @param string 		$separator (not require)
 * @param bool			$parent_shown (not require)
 * 
 * Возвращает дерево таксономий, обернутое в <option></option>
 */
if (!function_exists('the_cat_tree')) {
	function the_cat_tree($term_name = '', $term_id = -1, $value_arr = [], $separator = '', $parent_shown = true) {
		// $value_arr - массив id отмеченных ранее select-ов
		$result = '';
		$args = 'hierarchical=1&taxonomy='.$term_name.'&hide_empty=0&orderby=id&parent=';
		if ($parent_shown) {
			$term = get_term($term_id , $term_name);
			$selected = '';
			if (!empty($value_arr)) {
				foreach ($value_arr as $value) {
					if ($value == $term->term_id) {
						$selected = 'selected'; break;
					}
				}
			}
			$result = sprintf(
				'<option title="%1$s; ID: %2$s; %3$s: %4$s" class="hover" value="%2$s" %5$s>%6$s%7$s</option>',
					$term->name,
					$term->term_id,
					__('products', 'import-products-to-ok-ru'),
					$term->count,
					$selected,
					$separator,
					$term->name
			);
			$parent_shown = false;
		}
		$separator .= '-';  
		$terms = get_terms($term_name, $args . $term_id);
		if (count($terms) > 0) {
			foreach ($terms as $term) {
				$selected = '';
				if (!empty($value_arr)) {
					foreach ($value_arr as $value) {
						if ($value == $term->term_id) {
							$selected = 'selected'; break;
						}
					}
				}
				$result .= sprintf(
					'<option title="%1$s; ID: %2$s; %3$s: %4$s" class="hover" value="%2$s" %5$s>%6$s%7$s</option>',
						$term->name,
						$term->term_id,
						__('products', 'import-products-to-ok-ru'),
						$term->count,
						$selected,
						$separator,
						$term->name
				);
				$result .= the_cat_tree($term_name, $term->term_id, $value_arr, $separator, $parent_shown);
			}
		}
		return $result;
	}
}