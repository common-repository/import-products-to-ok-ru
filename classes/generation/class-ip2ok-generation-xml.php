<?php if (!defined('ABSPATH')) {exit;}
/**
 * Starts feed generation
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			0.1.0 (11-02-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 * 
 * @param	string 	$feed_id (require)
 *
 * @return				
 *
 * @depends			class:		IP2OK_Error_Log
 *								IP2OK_Get_Unit
 *								IP2OK_RU_Api
 *					traits:		
 *					methods:	
 *					functions:	common_option_get
 *								common_option_upd
 *					constants:	
 *
 */

class IP2OK_Generation_XML {
	protected $feed_id;

	public function __construct($feed_id) {
		$this->feed_id = (string)$feed_id;
	}

	public function run() {
		$syncing_with_ok = common_option_get('syncing_with_ok', false, $this->get_feed_id(), 'ip2ok');
		if ($syncing_with_ok === 'disabled') {		
			common_option_upd('status_sborki', '-1', 'no', $this->get_feed_id(), 'ip2ok');
			new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; Останавливаем сборку тк включён глобальный запрет на импорт; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
		}

		$step_export = (int)common_option_get('step_export', false, $this->get_feed_id(), 'ip2ok');
		$status_sborki = (int)common_option_get('status_sborki', false, $this->get_feed_id(), 'ip2ok');
		
		// $last_element = (int)common_option_get('last_element', 0, $this->get_feed_id());
		new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; $status_sborki = '.$status_sborki.'; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);

		switch($status_sborki) {
			case -1: // сборка завершена
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; case -1; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				wp_clear_scheduled_hook('ip2ok_cron_sborki', [ $this->get_feed_id() ]);
				break;
			case 1: // импорт категорий
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; Первый шаг. Импорт категорий. Файл: class-ipytw-import-xml.php; Строка: '.__LINE__);
				$behavior_cats = common_option_get('behavior_cats', false, $this->get_feed_id(), 'ip2ok');
				switch ($behavior_cats) { 
					case 'upd_once':
						new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; NOTICE: $behavior_cats = upd_once. Обновляем категории и выставляем upd_off; Файл: class-ipytw-import-xml.php; Строка: '.__LINE__);
						$this->run_api_categories_sync();
						common_option_upd('behavior_cats', 'upd_off', 'no', $this->get_feed_id(), 'ip2ok');
						break;
					case 'upd_on':
						new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; NOTICE: $behavior_cats = upd_on. Обновляем категории; Файл: class-ipytw-import-xml.php; Строка: '.__LINE__);	
						$this->run_api_categories_sync();
						break;
					case 'upd_off':
						new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; NOTICE: $behavior_cats = upd_off. Категории обновлять не нужно; Файл: class-ipytw-import-xml.php; Строка: '.__LINE__);	
						break;
					default: 
						$this->run_api_categories_sync();
						common_option_upd('behavior_cats', 'upd_off', 'no', $this->get_feed_id(), 'ip2ok');
				}
				common_option_upd('status_sborki', '2', 'no', $this->get_feed_id(), 'ip2ok');
				break;
			default:
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; case default; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				if ($status_sborki == 2) {
					$offset = 0;
				} else if ($status_sborki == 3) {
					$offset = $step_export;
				} else {
					$offset = (($status_sborki - 1)  * $step_export) - $step_export;
				}			
				$args = [
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export,
					'offset' => $offset,
					'relation' => 'AND',
					'orderby'  => 'ID'
				];
				$args = apply_filters('ip2ok_f_query_arg', $args, $this->get_feed_id());

				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; Полная сборка; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; $args =>; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				new IP2OK_Error_Log($args);

				$featured_query = new \WP_Query($args);
				$prod_id_arr = [ ]; 
				if ($featured_query->have_posts()) { 		
					for ($i = 0; $i < count($featured_query->posts); $i++) {
						$prod_id_arr[$i]['ID'] = $featured_query->posts[$i]->ID;
						$prod_id_arr[$i]['post_modified_gmt'] = $featured_query->posts[$i]->post_modified_gmt;
					}
					wp_reset_query(); /* Remember to reset */
					unset($featured_query); // чутка освободим память					
					$this->run_api($prod_id_arr);
					$status_sborki++;
					new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; status_sborki увеличен на '.$step_export.' и равен '.$status_sborki.'; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
					common_option_upd('status_sborki', $status_sborki, 'no', $this->get_feed_id(), 'ip2ok' );
				} else { // если постов нет, останавливаем импорт
					$this->stop();
				}
			// end default
		} // end switch($status_sborki)
		return; // final return from public function phase()
	} 

	public function stop() {
		common_option_upd('status_sborki', '-1', 'no', $this->get_feed_id(), 'ip2ok');			
		wp_clear_scheduled_hook('ip2ok_cron_sborki', [ $this->get_feed_id() ]);
	}

	public function run_api($ids_arr) {
		$api = new IP2OK_RU_Api(); 
		for ($i = 0; $i < count($ids_arr); $i++) {
			$product_id = (int)$ids_arr[$i]['ID'];
			$answer_arr = $api->product_sync($product_id); 
			if ($answer_arr['status'] == true) {
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; товара с $product_id = '.$product_id.' успешно импортирован; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
			} else {
				new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; ошибка добавления товара с $product_id = '.$product_id.'; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				new IP2OK_Error_Log($answer_arr);
			}
		}
	}

	public function run_api_categories_sync() {
		$api = new IP2OK_RU_Api();
		$product_cat_arr = get_terms('product_cat', 'orderby=name&hide_empty=0');
		if ($product_cat_arr) {
			new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; Категории на сайте есть. Приступим к созданию каталогов; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
			foreach ($product_cat_arr as $category) {
				if (get_term_meta($category->term_id, 'thumbnail_id', true) == '') {
					$args_arr = [
						'category_name' => $category->name,
					];
				} else {
					$category_thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
					$fullsize_path = get_attached_file($category_thumbnail_id);
					$args_arr = [
						'category_name' => $category->name,
						'category_pic_url' => $fullsize_path,
						'category_pic_id' => $category_thumbnail_id
					];
				}
				$answer_arr = $api->category_sync($category->term_id, $args_arr);
				if ($answer_arr['status'] == true) {
					new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; категория с $category->term_id = '.$category->term_id.' успешно импортирована; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
				} else {
					new IP2OK_Error_Log('FEED № '.$this->get_feed_id().'; ошибка добавления товара с $category->term_id = '.$category->term_id.'; Файл: class-ip2ok-generation-xml.php; Строка: '.__LINE__);
					new IP2OK_Error_Log($answer_arr);
				}

				/* для тестов раскоментить break ниже */
				// break;
			}
		}
	}

	// проверим, нужно ли отправлять запрос к API при обновлении поста
	public function check_ufup($post_id) {
		$ip2ok_ufup = common_option_get('syncing_with_ok', false, $this->get_feed_id(), 'ip2ok');
		if ($ip2ok_ufup === 'enabled') {
			$status_sborki = (int)common_option_get('status_sborki', false, $this->get_feed_id(), 'ip2ok');
			if ($status_sborki > -1) { // если идет сборка фида - пропуск
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	protected function get_feed_id() {
		return $this->feed_id;
	}
}