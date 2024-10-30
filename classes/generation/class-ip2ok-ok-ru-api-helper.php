<?php if (!defined('ABSPATH')) {exit;}
/**
 * The class will help you connect your store to OK.ru using OK.ru API
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			0.1.0 (03-03-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 *
 * @param	string	$post_id (require)
 *
 * @return	array	$product_data_arr
 *
 * @depends			classes:	IP2OK_RU_Api_Helper_Simple
 *								IP2OK_RU_Api_Helper_Variable
 *					traits:		
 *					methods:	
 *					functions:	
 *					constants:	
 */

final class IP2OK_RU_Api_Helper {
	protected $feed_id;
	protected $product_data_arr = [ ];
	protected $category_id_on_ok = '';
	protected $skip_reasons_arr = [ ];

	public function __construct() {
		$this->feed_id = '1';
	}

	public function set_product_data($product_id, $actions) {
		$product = wc_get_product($product_id);
		if ($product == null) { 
			$this->add_skip_reason( ['reason' => __('There is no product with this ID', 'import-products-to-ok-ru'), 'post_id' => $product_id, 'file' => 'class-ip2ok-ok-ru-api-helper.php', 'line' => __LINE__ ] );
			return;
		}

		$terms_post = get_the_terms($product_id, 'product_cat');
		if (empty($terms_post)) {
			$this->category_id_on_ok = '';
		} else {
			foreach ($terms_post as $term_cat) {
				$term_cat_id = $term_cat->term_id;
				$category_id_ok = $this->is_category_exists($term_cat_id);
				if (false !== $category_id_ok) {
					$this->category_id_on_ok = (string)$category_id_ok;
				} else {
					$this->category_id_on_ok = '';
				}
				break;
			}
		}
		
		if ($product->is_type('simple')) {
			$obj = new IP2OK_RU_Api_Helper_Simple($product, $actions, $this->get_feed_id());
			$skip_reasons_arr = $obj->get_skip_reasons_arr();
			if (empty($skip_reasons_arr)) {
				$this->product_data_arr = $obj->get_result();	
			} else { 
				$this->skip_reasons_arr = $skip_reasons_arr;
			}
			unset($obj);
			return;
		} else if ($product->is_type('variable')) {
			$variations_arr = $product->get_available_variations();
			$variation_count = count($variations_arr);
			for ($i = 0; $i < $variation_count; $i++) {
				$offer_id = $variations_arr[$i]['variation_id'];
				$offer = new WC_Product_Variation($offer_id); // получим вариацию

				$obj = new IP2OK_RU_Api_Helper_Variable($product, $actions, $offer, $variation_count, $this->get_feed_id());
				$skip_reasons_arr = $obj->get_skip_reasons_arr();
				if (empty($skip_reasons_arr)) {
					$this->product_data_arr = $obj->get_result();	
				} else { 					
					$this->skip_reasons_arr = $skip_reasons_arr;
					continue;
				}
				unset($obj);
				return;
			}
		} else {
			$this->add_skip_reason( ['reason' => __('The product is not simple or variable', 'import-products-to-ok-ru'), 'post_id' => $product_id, 'file' => 'class-ip2ok-ok-ru-api-helper.php', 'line' => __LINE__ ] );
			return;
		}
	}

	public function get_product_data() {
		return $this->product_data_arr;
	}

	public function get_category_id_on_ok() {
		return $this->category_id_on_ok;
	}
	
	public function is_photo_exists($thumb_id) {
		if (get_post_meta($thumb_id, 'iptok_existing_photo_id', true) == '') {
			return false;
		} else {
			return get_post_meta($thumb_id, 'iptok_existing_photo_id', true);
		}
	}

	public function is_product_exists($product_id) {
		if (get_post_meta($product_id, 'iptok_prod_id_on_ok', true) == '') {
			return false;
		} else {
			return get_post_meta($product_id, 'iptok_prod_id_on_ok', true);
		}
	}

	public function is_category_exists($category_id) {
		$ip2ok_ok_product_category = get_term_meta($category_id, 'iptok_ok_product_category', true);
		if ($ip2ok_ok_product_category == '') {
			return false;
		} else {
			return get_term_meta($category_id, 'iptok_ok_product_category', true);
		}
	}
	
	public function set_photo_exists($thumb_id, $photo_ids, $photo_id_on_ok) {
		update_post_meta($thumb_id, 'iptok_photo_ids', $photo_ids); // $token
		update_post_meta($thumb_id, 'iptok_existing_photo_id', $photo_id_on_ok);
		return;
	}

	public function set_product_exists($product_id, $product_id_on_ok) {
		update_post_meta($product_id, 'iptok_prod_id_on_ok', $product_id_on_ok);
		return;
	}

	public function set_category_exists($category_id, $category_id_on_ok) {
		update_term_meta($category_id, 'iptok_ok_product_category', $category_id_on_ok);
		return;
	}
	
	public function set_skip_reasons_arr($v) {
		$this->skip_reasons_arr[] = $v;
	}

	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

	protected function add_skip_reason($reason) {
		if (isset($reason['offer_id'])) {
			$reason_string = sprintf(
				'FEED № %1$s; Вариация товара (postId = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}

/*! */	echo $reason_string;
		$this->set_skip_reasons_arr($reason_string);
		new IP2OK_Error_Log($reason_string); 
	}

	/* Getters */

	public function get_feed_id() {
		return $this->feed_id;
	}

	public function get_result() {
		return $this->product_data_arr;
	}
}