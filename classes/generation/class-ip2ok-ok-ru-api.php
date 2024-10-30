<?php
/**
 * The class will help you connect your store to Yandex Market using Yandex API
 *
 * @package                 IP2OK RU_API
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.0.4 (03-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param      string       $post_id - Required
 *
 * @depends                 classes:    IP2OK_RU_Api_Helper
 *                                      IP2OK_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2OK_RU_Api {
	/**
	 * Summary of application_id
	 * @var string
	 */
	protected $application_id;
	/**
	 * Summary of access_token
	 * @var string
	 */
	protected $access_token;
	/**
	 * Summary of group_id
	 * @var string
	 */
	protected $group_id;
	/**
	 * Summary of public_key
	 * @var string
	 */
	protected $public_key;
	/**
	 * Summary of private_key
	 * @var string
	 */
	protected $private_key;

	/**
	 * Summary of debug
	 * @var string
	 */
	protected $debug; // добавляет к url запроса GET-параметр для дебага
	/**
	 * Summary of feed_id
	 * @var string
	 */
	protected $feed_id = '1';

	/**
	 * Summary of __construct
	 * 
	 * @param array $args_arr - Optional
	 */
	public function __construct( $args_arr = [] ) {
		$this->application_id = common_option_get( 'application_id', false, '1', 'ip2ok' );
		$this->access_token = common_option_get( 'access_token', false, '1', 'ip2ok' );
		$this->group_id = common_option_get( 'group_id', false, '1', 'ip2ok' );
		$this->public_key = common_option_get( 'public_key', false, '1', 'ip2ok' );
		$this->private_key = common_option_get( 'private_key', false, '1', 'ip2ok' );
		if ( isset( $args_arr['debug'] ) ) {
			$this->debug = $args_arr['debug'];
		}
		if ( isset( $args_arr['feed_id'] ) ) {
			$this->feed_id = $args_arr['feed_id'];
		}

		add_action( 'parse_request', [ $this, 'listen_request' ] ); // Хук парсера запросов
		add_action( 'admin_init', [ $this, 'listen_submits' ], 9 );
	}

	/**
	 * Summary of listen_submits
	 * 
	 * @return void
	 */
	public function listen_submits() {
		return;
	}

	/**
	 * Summary of listen_request
	 * 
	 * @return void
	 */
	public function listen_request() {
		return;
	}

	/**
	 * Синхронизация товара
	 * 
	 * @version			0.1.0
	 * @see				
	 * 
	 * @param	int		$product_id (require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	public function product_sync( $product_id ) {
		$answer_arr = [ 
			'status' => false
		];

		$helper = new IP2OK_RU_Api_Helper();
		$product_id_ok = $helper->is_product_exists( $product_id );
		if ( false === $product_id_ok ) {
			$product_data = $helper->set_product_data( $product_id, 'product_add' );
			if ( empty( $helper->get_skip_reasons_arr() ) ) {
				$answer_arr = $this->product_add( $helper->get_product_data(), $helper->get_category_id_on_ok() );
			} else {
				$answer_arr['skip_reasons'] = $helper->get_skip_reasons_arr();
			}
		} else {
			$product_data = $helper->set_product_data( $product_id, 'product_upd' );
			if ( empty( $helper->get_skip_reasons_arr() ) ) { // нужно обновить товар
				$answer_arr = $this->product_upd(
					$product_id_ok,
					$helper->get_product_data(),
					$helper->get_category_id_on_ok()
				);
			} else { // у нас есть причины пропуска. Удалим товар
				$answer_arr['skip_reasons'] = $helper->get_skip_reasons_arr();
				$res_d = $this->product_del( $product_id_ok );
				if ( true == $res_d['status'] ) {
					$helper->set_product_exists( $product_id, '' );
				}
			}
		}

		if ( true === $answer_arr['status'] ) {
			$helper->set_product_exists( $product_id, $answer_arr['product_id'] );
		} else {
			if ( isset( $answer_arr['errors'] ) ) {
				if ( $answer_arr['errors']->error_code == 300 ) {
					// товар был удалён на сайте ок.ру, синхроним этот момент
					$helper->set_product_exists( $product_id, '' );
					// и пробуем повторно залить
					$answer_arr = $this->product_sync( $product_id );
				}
			}
		}
		return $answer_arr;
	}

	/**
	 * Синхронизация категории
	 * 
	 * @version			0.1.0
	 * @see				
	 * 
	 * @param	int		$category_id (require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['catalog_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	public function category_sync( $category_id, $args_arr ) {
		$answer_arr = [ 
			'status' => false
		];

		$helper = new IP2OK_RU_Api_Helper();
		$category_id_ok = $helper->is_category_exists( $category_id );
		if ( false === $category_id_ok ) {
			$answer_arr = $this->category_add( $args_arr );
		} else { // нужно обновить категорию
			$args_arr['catalog_id'] = $category_id_ok;
			$answer_arr = $this->category_upd( $args_arr );
		}

		if ( true === $answer_arr['status'] ) {
			if ( isset( $answer_arr['catalog_id'] ) ) {
				// проверка нужна тк в случае с category_upd апи одноклассников не возрващает catalog_id
				$helper->set_category_exists( $category_id, $answer_arr['catalog_id'] );
			}
		} else {
			if ( isset( $answer_arr['errors'] ) ) {
				if ( $answer_arr['errors']->error_code == 300 ) {
					// категория была удалена на сайте ок.ру, синхроним этот момент
					$helper->set_category_exists( $category_id, '' );
					// и пробуем повторно залить
					$answer_arr = $this->product_sync( $category_id, $args_arr );
				}
			}
		}

		return $answer_arr;
	}

	/**
	 * Добавление категории
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.addCatalog
	 * 
	 * @param	array	$args_arr (require)
	 * 						['category_name'] - (require)
	 * 						['category_pic_url'] - (not require)
	 * 						['category_pic_id'] - (not require)
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['catalog_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ['error_code'] => int(101)
	 *						- ['error_msg'] => string(37)
	 *						- ['error_data'] => NULL
	 */
	public function category_add( $args_arr ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.addCatalog',
			'type' => 'GROUP_PRODUCT',
			'name' => $args_arr['category_name'],
		];
		if ( isset( $args_arr['category_pic_url'] ) ) {
			$answ = $this->send_pic( $args_arr['category_pic_url'], $args_arr['category_pic_id'] );
			if ( true === $answ['status'] ) {
				$params_arr['group_photo.id'] = (string) $answ['photo_id_on_ok'];
			}
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log(
				sprintf( 'FEED № %1$s; %2$s %3$s! Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'ERROR: Ошибка создания категори товара body_answer =',
					$answer_arr['body_answer']->error_msg,
					'class-ip2ok-ok-ru-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		} else {
			// object(stdClass)#18810 (2) { ["success"]=> bool(true) ["catalog_id"]=> string(15) "154695762776866" } 
			$result = [ 
				'status' => true,
				'catalog_id' => $answer_arr['body_answer']->catalog_id
			];
		}
		return $result;
	}

	/**
	 * Редактирование категории
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.editCatalog
	 * 
	 * @param	array	$args_arr (require)
	 *						['category_id'] - (require)
	 * 						['category_name'] - (require)
	 * 						['category_pic_url'] - (not require)
	 * 						['category_pic_id'] - (not require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- ['error_code'] => int(101)
	 *						- ['error_msg'] => string(37)
	 *						- ['error_data'] => NULL
	 */
	public function category_upd( $args_arr ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.editCatalog',
			'type' => 'GROUP_PRODUCT',
			'name' => $args_arr['category_name'],
			'catalog_id' => $args_arr['catalog_id']
		];
		if ( isset( $args_arr['category_pic_url'] ) ) {
			$answ = $this->send_pic( $args_arr['category_pic_url'], $args_arr['category_pic_id'] );
			if ( true === $answ['status'] ) {
				$params_arr['group_photo.id'] = (string) $answ['photo_id_on_ok'];
			}
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка создания категори товара body_answer = ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		} else {
			// object(stdClass)#18810 (1) { ["success"]=> bool(true) } 	
			$result = [ 
				'status' => true
			];
		}

		return $result;
	}

	/**
	 * Удаление категории
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.deleteCatalog
	 * 
	 * @param	string	$catalog_id (require)
	 * @param	bool	$delete_products (not require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- ['error_code'] => int(101)
	 *						- ['error_msg'] => string(37)
	 *						- ['error_data'] => NULL
	 */
	public function category_del( $catalog_id, $delete_products = false ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.deleteCatalog',
			'type' => 'GROUP_PRODUCT',
			'catalog_id' => $catalog_id,
			'delete_products' => (string) $delete_products
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка создания категори товара body_answer = ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		} else {
			// object(stdClass)#18815 (1) { ["success"]=> bool(true) }  	
			$result = [ 
				'status' => true
			];
		}

		return $result;
	}

	/**
	 * Добавление товара
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.add
	 * 
	 * @param	array	product_data (require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	public function product_add( $product_data, $category_id_ok = '' ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.add',
			'type' => 'GROUP_PRODUCT',
			'attachment' => json_encode( $product_data ),
		];
		if ( ! empty( $category_id_ok ) ) {
			$params_arr['catalog_ids'] = $category_id_ok;
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка добавления товара body_answer = ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		}

		// object(stdClass)#18911 (2) { ["success"]=> bool(true) ["product_id"]=> string(15) "154698495759138" }
		$result = [ 
			'status' => true,
			'product_id' => $answer_arr['body_answer']->product_id
		];

		return $result;
	}


	/**
	 * Редактирование товара
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.edit
	 * 
	 * @param	array	product_data (require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	public function product_upd( $product_id, $product_data, $category_id_ok = '' ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.edit',
			'type' => 'GROUP_PRODUCT',
			'product_id' => (string) $product_id, // тут нужен id с сайта Ок-ру
			'attachment' => json_encode( $product_data ),
		];
		if ( ! empty( $category_id_ok ) ) {
			$params_arr['catalog_ids'] = $category_id_ok;
		}
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка обновления товара body_answer = ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
			/**
			 * ["error_code"] => int(101)
			 * ["error_msg"] => string(37)
			 * ["error_data"] => NULL
			 */
		}

		// object(stdClass)#18910 (2) { ["success"]=> bool(true) ["product_id"]=> string(15) "154698511160098" }
		$result = [ 
			'status' => true,
			'product_id' => $answer_arr['body_answer']->product_id
		];

		return $result;
	}

	/**
	 * Удаление товара
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/market/market.delete
	 * 
	 * @param	string	$product_id_ok (require)
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	public function product_del( $product_id_ok ) {
		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'market.delete',
			'type' => 'GROUP_PRODUCT',
			'product_id' => (string) $product_id_ok // тут нужен id с сайта Ок-ру
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка удаления товара body_answer = ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		}

		$result = [ 
			'status' => true,
			'product_id' => $answer_arr['body_answer']
		];

		return $result;
	}

	/**
	 * Импорт картинки
	 * 
	 * @version			0.1.0
	 * @see				https://apiok.ru/dev/methods/rest/photosV2/photosV2.getUploadUrl
	 *
	 * @param	string	$pic_url (require) - урл загружаемой картинки
	 * @param	string	$thumb_id (require) - id миниатюры
	 * @param	int		$type (not require) - число картинок на загрузку
	 *
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['photo_id_on_ok'] - string - токен загруженной фотки 
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["error_data"] => NULL
	 */
	function send_pic( $pic_url, $thumb_id, $num_pic = 1 ) {
		new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; $pic_url = ' . $pic_url . '; $thumb_id = ' . $thumb_id . '; $num_pic = ' . $num_pic . '; Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );

		if ( false === get_post_type( $thumb_id ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; WARNING: get_post_type вернула false. Загрузка картинки не возможна! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result = [ 
				'status' => false
			];
			return $result;
		}

		// Проверим. Возможно фотка уже на ок.ру
		$helper = new IP2OK_RU_Api_Helper();
		$photo_exists = $helper->is_photo_exists( $thumb_id );
		if ( false === $photo_exists ) {
			// фото нет
		} else {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Загружать фото не нужно. Оно уже есть на ок.ру с $photo_exists = ' . $photo_exists . '. Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result = [ 
				'status' => true,
				'photo_id_on_ok' => $photo_exists
			];
			return $result;
		}

		$result = [ 
			'status' => false
		];

		$params_arr = [ 
			'method' => 'photosV2.getUploadUrl',
			'count' => 1, // количество фото для загрузки
		];

		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка Шага 1 загрузки фото! ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		}
		$photo_ids = $answer_arr['body_answer']->photo_ids[0]; // Идентификатор для загрузки фото
		$upload_url = $answer_arr['body_answer']->upload_url;
		new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Шаг 1 загрузки фото. Идентификатор для загрузки фото получен. $answer_arr[body_answer]->photo_ids[0] = ' . $photo_ids . '; Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );

		// Шаг 2. Загрузка фото 
		$params_arr = [ 
			"pic1" => new \CURLFile( $pic_url ), // $pic_url '/home/p1/www/site.ru/wp-content/uploads/2023/1.jpg'
		];
		// Отправляем картинку на сервер, подписывать не нужно
		$answer_arr = $this->curl(
			$upload_url,
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'dont_encode' // не нужно кодировать переменную
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка Шага 2 загрузки фото! ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result['errors'] = $answer_arr['body_answer'];
			return $result;
		}

		$token_ph = $answer_arr['body_answer']->photos->$photo_ids->token; // Токен загруженной фотки

		// Шаг 3. photosV2.commit
		$params_arr = [ 
			'method' => 'photosV2.commit',
			'photo_id' => $photo_ids,
			'token' => $token_ph
		];
		$params_arr = $this->get_sig( $params_arr );

		$answer_arr = $this->curl(
			'https://api.ok.ru/fb.do',
			$params_arr,
			$this->get_headers_arr(),
			'POST',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->error_code ) ) {
			new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка Шага 1 загрузки фото! ' . $answer_arr['body_answer']->error_msg . '! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
			$result[1] = $answer_arr['body_answer'];
			return $result;
		}

		new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; Шаг 3 загрузки фото успешен. После photosV2.commit body_answer ==>' . __LINE__ );
		$photo_id_on_ok = $answer_arr['body_answer']->photos[0]->assigned_photo_id;
		new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; $photo_id_on_ok = ' . $photo_id_on_ok . __LINE__ );

		$helper->set_photo_exists( $thumb_id, $photo_ids, $photo_id_on_ok );

		$result = [ 
			'status' => true,
			'photo_id_on_ok' => $photo_id_on_ok,
			'token_ph' => $token_ph
		];
		new IP2OK_Error_Log( 'FEED № ' . $this->get_feed_id() . '; send_pic отработала успешно! Файл: class-ip2ok-ok-ru-api.php; Строка: ' . __LINE__ );
		new IP2OK_Error_Log( $result );

		return $result;
	}

	/**
	 * Отправка запросов курлом
	 * 
	 * @version			0.1.0
	 * @see				https://snipp.ru/php/curl
	 * 
	 * @param	string	$request_url - Required
	 * @param	array	$postfields_arr - Optional
	 * @param	array	$headers_arr - Optional
	 * @param	string	$request_type - Optional
	 * @param	array	$pwd_arr - Optional
	 * @param	string	$encode_type - Optional
	 * @param	int		$timeout - Optional
	 * @param	string	$proxy - Optional) // example: '165.22.115.179:8080
	 * @param	bool	$debug - Optional
	 * @param	string	$sep - Optional
	 * @param	string	$useragent - Optional
	 * 
	 * @return 	array	keys: errors, status, http_code, body, header_request, header_answer
	 * 
	 */
	private function curl( $request_url, $postfields_arr = [], $headers_arr = [], $request_type = 'POST', $pwd_arr = [], $encode_type = 'json_encode', $timeout = 40, $proxy = '', $debug = false, $sep = PHP_EOL, $useragent = 'PHP Bot' ) {
		if ( ! empty( $this->get_debug() ) ) {
			$request_url = $request_url . '?dbg=' . $this->get_debug();
		}

		$curl = curl_init(); // инициализация cURL
		if ( ! empty( $pwd_arr ) ) {
			if ( isset( $pwd_arr['login'] ) && isset( $pwd_arr['pwd'] ) ) {
				$userpwd = $pwd_arr['login'] . ':' . $pwd_arr['pwd']; // 'логин:пароль'
				curl_setopt( $curl, CURLOPT_USERPWD, $userpwd );
			}
		}
		curl_setopt( $curl, CURLOPT_URL, $request_url );

		// проверять ли подлинность присланного сертификата сервера
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		// задает проверку имени, указанного в сертификате удаленного сервера, при установлении SSL соединения. 
		// Значение 0 - без проверки, значение 1 означает проверку существования имени, значение 2 - кроме того, 
		// и проверку соответствия имени хоста. Рекомендуется 2.
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );

		// количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers_arr );
		curl_setopt( $curl, CURLOPT_USERAGENT, $useragent );

		$answer_arr = [];
		$answer_arr['body_request'] = null;
		if ( $request_type !== 'GET' ) {
			switch ( $encode_type ) {
				case 'json_encode':
					$answer_arr['body_request'] = json_encode( $postfields_arr );
					break;
				case 'http_build_query':
					$answer_arr['body_request'] = http_build_query( $postfields_arr );
					break;
				case 'dont_encode':
					$answer_arr['body_request'] = $postfields_arr;
					break;
				default:
					$answer_arr['body_request'] = json_encode( $postfields_arr );
			}
		}

		if ( $request_type === 'POST' ) { // отправляется POST запрос
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
			// $postfields_arr - массив с передаваемыми параметрами POST
		}

		if ( $request_type === 'DELETE' ) { // отправляется DELETE запрос
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
		}

		if ( $request_type === 'PUT' ) { // отправляется PUT запрос
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $answer_arr['body_request'] );
			// http_build_query($postfields_arr, '', '&') // $postfields_arr - массив с передаваемыми параметрами POST
		}

		if ( ! empty( $proxy ) ) {
			// зададим максимальное кол-во секунд для выполнения cURL-функций
			curl_setopt( $curl, CURLOPT_TIMEOUT, 400 );

			// HTTP-прокси, через который будут направляться запросы
			curl_setopt( $curl, CURLOPT_PROXY, $proxy );
		}

		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // вернуть результат запроса, а не выводить в браузер
		curl_setopt( $curl, CURLOPT_HEADER, true ); // опция позволяет включать в ответ от сервера его HTTP - заголовки
		curl_setopt( $curl, CURLINFO_HEADER_OUT, true ); // true - для отслеживания строки запроса дескриптора

		usleep(300000); // притормозим на 0,3 секунды
		$result = curl_exec( $curl ); // выполняем cURL

		// Обработка результата выполнения запроса
		if ( ! $result ) {
			$answer_arr['errors'] = 'Ошибка cURL: ' . curl_errno( $curl ) . ' - ' . curl_error( $curl );
			$answer_arr['body_answer'] = null;
		} else {
			$answer_arr['status'] = true; // true - получили ответ
			// Разделение полученных HTTP-заголовков и тела ответа
			$response_headers_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
			$response_headers = substr( $result, 0, $response_headers_size );
			$response_body = substr( $result, $response_headers_size );
			$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			$answer_arr['http_code'] = $http_code;

			if ( $http_code == 200 ) {
				// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
				$decoded_body = json_decode( $response_body );
				$answer_arr['body_answer'] = $decoded_body;
			} else {
				// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
				if ( ! empty( $response_body ) ) {
					$decoded_body = json_decode( $response_body );
					if ( $decoded_body != null ) {
						// Если ответ содержит тело в формате JSON, 
						// то возвращаем отформатированное тело в формате JSON
						$answer_arr['body_answer'] = $decoded_body;
					} else {
						// Если не удалось декодировать JSON либо тело имеет другой формат, 
						// то возвращаем преобразованное тело ответа
						$answer_arr['body_answer'] = htmlspecialchars( $response_body );
					}
				} else {
					$answer_arr['body_answer'] = null;
				}
			}
			// Вывод необработанных HTTP-заголовков запроса и ответа
			$answer_arr['header_request'] = curl_getinfo( $curl, CURLINFO_HEADER_OUT ); // Заголовки запроса
			$answer_arr['header_answer'] = $response_headers; // Заголовки ответа
		}

		curl_close( $curl );

		return $answer_arr;
	}

	/* Getters */

	/**
	 * Summary of get_headers_arr
	 * 
	 * @return array
	 */
	private function get_headers_arr() {
		return [];
	}

	/**
	 * Summary of get_sig
	 * 
	 * @param array $params_arr
	 * 
	 * @return array
	 */
	private function get_sig( $params_arr ) {
		$params_arr['application_key'] = $this->get_public_key();
		$params_arr['gid'] = $this->get_group_id();
		$params_arr['format'] = 'json';
		// Подпишем запрос
		$sig = md5( $this->conv_arr_as_str( $params_arr ) . md5( $this->get_access_token() . $this->get_private_key() ) );
		$params_arr['access_token'] = $this->get_access_token();
		$params_arr['sig'] = $sig;

		return $params_arr;
	}

	/**
	 * Summary of conv_arr_as_str
	 * 
	 * @param array $array
	 * 
	 * @return string
	 */
	private function conv_arr_as_str( $array ) {
		ksort( $array );
		$string = "";
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$string .= $key . "=" . $this->conv_arr_as_str( $val );
			} else {
				$string .= $key . "=" . $val;
			}
		}
		return $string;
	}

	/**
	 * Summary of get_application_id
	 * 
	 * @return string
	 */
	private function get_application_id() {
		return $this->application_id;
	}

	/**
	 * Summary of get_public_key
	 * 
	 * @return string
	 */
	private function get_public_key() {
		return $this->public_key;
	}

	/**
	 * Summary of get_private_key
	 * 
	 * @return string
	 */
	private function get_private_key() {
		return $this->private_key;
	}

	/**
	 * Summary of get_group_id
	 * 
	 * @return string
	 */
	private function get_group_id() {
		return $this->group_id;
	}

	/**
	 * Summary of get_access_token
	 * 
	 * @return string
	 */
	private function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Summary of get_debug
	 * 
	 * @return string
	 */
	private function get_debug() {
		return $this->debug;
	}

	/**
	 * Summary of get_feed_id
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}
}