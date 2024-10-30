<?php if (!defined('ABSPATH')) {exit;}
/**
 * Traits for variable products
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			0.1.0 (11-02-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 *
 * @return 			$result_xml (string)
 *
 * @depends			class:		
 *					methods: 	get_product
 *								get_feed_id
 *								get_feed_category_id
 *					variable:	
  *					methods:	
 *					functions:	
 *					constants:	
 */

trait IP2OK_T_Common_Skips {
	public function get_skips() {
		$product = $this->get_product();
		$skip_flag = false;
	
		if ($product == null) {
			$this->add_skip_reason( [ 'reason' => __('There is no product with this ID', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
		}

		if ($product->is_type('grouped')) {
			$this->add_skip_reason( [ 'reason' => __('Product is grouped', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
		}

		if ($product->is_type('external')) {
			$this->add_skip_reason( [ 'reason' => __('Product is External/Affiliate product', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
		}

		if ($product->get_status() !== 'publish') {
			$this->add_skip_reason( [ 'reason' => __('The product status/visibility is', 'import-products-to-ok-ru').' "'.$product->get_status().'"', 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
		}
				 
		// что выгружать
		$ip2ok_whot_export = common_option_get('whot_export', false, $this->get_feed_id(), 'ip2ok');
		if ($product->is_type('variable')) {	
			if ($ip2ok_whot_export === 'simple') {
				$this->add_skip_reason( [ 'reason' => __('Product is simple', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ]; 
			}
		}
		if ($product->is_type('simple')) {	
			if ($ip2ok_whot_export === 'variable') {
				$this->add_skip_reason( [ 'reason' => __('Product is variable', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
			}
		}

		$skip_flag = apply_filters('ip2ok_f_skip_flag', $skip_flag, [ 'product' => $product, 'catid' => $this->get_feed_category_id() ], $this->get_feed_id());
		if ($skip_flag !== false) {
			$this->add_skip_reason( [ 'reason' => $skip_flag, 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
		}

		// пропуск товаров, которых нет в наличии
		$ip2ok_skip_missing_products = common_option_get('skip_missing_products', false, $this->get_feed_id(), 'ip2ok');
		if ($ip2ok_skip_missing_products == 'enabled') {
			if ($product->is_in_stock() == false) { 
				$this->add_skip_reason( [ 'reason' => __('Skip missing products', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
			}
		}

		// пропускаем товары на предзаказ
		$skip_backorders_products = common_option_get('skip_backorders_products', false, $this->get_feed_id(), 'ip2ok');
		if ($skip_backorders_products == 'enabled') {
			if ($product->get_manage_stock() == true) { // включено управление запасом  
				if (($product->get_stock_quantity() < 1) && ($product->get_backorders() !== 'no')) { 
					$this->add_skip_reason( [ 'reason' => __('Skip backorders products', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
				}
			} else {
				if ($product->get_stock_status() !== 'instock') { 
					$this->add_skip_reason( [ 'reason' => __('Skip backorders products', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
				}
			}
		}

		if ($product->is_type('variable')) {	
			$offer = $this->offer;

			// пропуск вариаций, которых нет в наличии
			$ip2ok_skip_missing_products = common_option_get('skip_missing_products', false, $this->get_feed_id(), 'ip2ok');
			if ($ip2ok_skip_missing_products == 'enabled') {
				if ($offer->is_in_stock() == false) { 
					$this->add_skip_reason( [ 'offer_id' => $offer->get_id(), 'reason' => __('Skip missing products', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'traits-ip2ok-variable.php', 'line' => __LINE__ ] ); return [ ];
				}
			}
					
			// пропускаем вариации на предзаказ
			$skip_backorders_products = common_option_get('skip_backorders_products', false, $this->get_feed_id(), 'ip2ok');
			if ($skip_backorders_products == 'enabled') {
				if ($offer->get_manage_stock() == true) { // включено управление запасом			  
					if (($offer->get_stock_quantity() < 1) && ($offer->get_backorders() !== 'no')) {
						$this->add_skip_reason( [ 'offer_id' => $offer->get_id(), 'reason' => __('Skip backorders products', 'import-products-to-ok-ru'), 'post_id' => $product->get_id(), 'file' => 'traits-ip2ok-variable.php', 'line' => __LINE__ ] ); return [ ];
					}
				}
			}

			$skip_flag = apply_filters('ip2ok_f_skip_flag_variable', $skip_flag, array('product' => $product, 'offer' => $offer, 'catid' => $this->get_feed_category_id()), $this->get_feed_id());
			if ($skip_flag !== false) {
				$this->add_skip_reason( [ 'offer_id' => $offer->get_id(), 'reason' => $skip_flag, 'post_id' => $product->get_id(), 'file' => 'trait-ip2ok-t-common-skips.php', 'line' => __LINE__ ] ); return [ ];
			}
		}
	}

}