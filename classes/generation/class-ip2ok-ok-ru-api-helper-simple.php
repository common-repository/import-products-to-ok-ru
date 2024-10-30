<?php
/**
 * The class will help you connect your store to OK.ru using OK.ru API
 *
 * @package                 Import Products to OK.ru
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 2.0.4 (03-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param      string       $product - Required
 * @param      string       $actions - Required
 * @param      string       $feed_id - Optional
 *
 * @depends                 classes:	IP2OK_Error_Log
 *                                      IP2OK_RU_Api
 *                          traits:     IP2OK_T_Common_Get_CatId
 *                                      IP2OK_T_Common_Skips
 *                          methods:    
 *                          functions:  common_option_get
 *                          			common_option_upd
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2OK_RU_Api_Helper_Simple {
	use IP2OK_T_Common_Get_CatId;
	use IP2OK_T_Common_Skips;

	/**
	 * @var WC_Product
	 */
	protected $product;
	protected $feed_id;
	protected $result_arr = [];
	protected $skip_reasons_arr = [];

	public function __construct( $product, $actions, $feed_id = '1' ) {
		$this->product = $product;
		$this->feed_id = $feed_id;
		$this->set_category_id();
		$this->get_skips();
		switch ( $actions ) {
			case 'product_add':
				$this->product_add();
				break;
			case 'product_upd':
				$this->product_upd();
				break;
			case 'product_del':
				$this->product_del();
				break;
		}
	}

	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v;
	}

	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

	protected function add_skip_reason( $reason ) {
		$reason_string = sprintf(
			'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
			$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
		);

		$this->set_skip_reasons_arr( $reason_string );
		new IP2OK_Error_Log( $reason_string );
	}

	public function product_add() {
		$picture_info_arr = $this->get_picture();
		if ( empty( $picture_info_arr ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'the product does not have a photo', 'import-products-to-ok-ru' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2ok-ok-ru-api-helper-simple.php',
				'line' => __LINE__
			] );
			// $result_arr = [];
			return;
		}

		$obj = new IP2OK_RU_Api();
		$answ = $obj->send_pic( $picture_info_arr['url'], $picture_info_arr['id'] );
		if ( true === $answ['status'] ) {
			$existing_photo_id = $answ['photo_id_on_ok'];
		} else {
			// $this->add_skip_reason( [ 
			//	'reason' => __( "I can't upload a photo", "import-products-to-ok-ru" ),
			//	'post_id' => $this->get_product()->get_id(),
			//	'file' => 'class-ip2ok-ok-ru-api-helper-simple.php',
			//	'line' => __LINE__
			// ] );
			// return;
			$existing_photo_id = '';
		}
		unset( $obj );

		$description = $this->get_description();
		if ( empty( $description ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'description', 'import-products-to-ok-ru' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2ok-ok-ru-api-helper-simple.php',
				'line' => __LINE__
			] );
			// $result_arr = [];
			return;
		}

		$this->result_arr = [ 'media' =>
			[ 
				[ 'type' => 'text', 'text' => $this->get_name() ],
				[ 'type' => 'text', 'text' => $description ],
				[ 
					'type' => 'photo',
					'list' => [ // array
						[ 
							'existing_photo_id' => $existing_photo_id, // string
							'group' => true // bool
						]
					]
				],
				[ 
					'type' => 'product',
					'price' => $this->get_price(), // float
					'currency' => $this->get_currency(), // string
					'lifetime' => 30 // int
				]
			]
		];
	}

	public function product_upd() {
		$picture_info_arr = $this->get_picture();
		if ( empty( $picture_info_arr ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Missing pictures', 'import-products-to-ok-ru' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2ok-ok-ru-api-helper-simple.php',
				'line' => __LINE__
			] );
			$result_arr = [];
			return;
		}
		$obj = new IP2OK_RU_Api();
		$answ = $obj->send_pic( $picture_info_arr['url'], $picture_info_arr['id'] );
		if ( true === $answ['status'] ) {
			$existing_photo_id = $answ['photo_id_on_ok'];
		} else {
		}
		unset( $obj );

		$description = $this->get_description();
		if ( empty( $description ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'Missing description', 'import-products-to-ok-ru' ),
				'post_id' => $this->get_product()->get_id(),
				'file' => 'class-ip2ok-ok-ru-api-helper-simple.php',
				'line' => __LINE__
			] );
			$result_arr = [];
			return;
		}

		$this->result_arr = [ 'media' =>
			[ 
				[ 'type' => 'text', 'text' => $this->get_name() ],
				[ 'type' => 'text', 'text' => $description ],
				[ 
					'type' => 'photo',
					'list' => [ // array
						[ 
							'existing_photo_id' => $existing_photo_id, // string
							'group' => true // bool
						]
					]
				],
				[ 
					'type' => 'product',
					'price' => $this->get_price(), // float
					'currency' => $this->get_currency(), // string
					'lifetime' => 30 // int
				]
			]
		];
	}

	public function product_del() {

	}

	public function get_currency() {
		$currency_id_maybe = [ 'RUB', 'USD', 'KZT', 'UAH', 'GEL', 'UZS', 'KGS', 'AZN', 'USD', 'EUR', 'BYN' ];
		$currency_id_ok = get_woocommerce_currency();
		if ( ! in_array( $currency_id_ok, $currency_id_maybe ) ) {
			$currency_id_ok = 'RUB';
		}
		return $currency_id_ok;
	}

	public function get_price() {
		/**
		 * $product->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $product->get_regular_price() - обычная цена
		 * $product->get_sale_price() - цена скидки
		 */
		$price = $this->get_product()->get_price();
		$regular_price = $this->get_product()->get_regular_price();
		$sale_price = $this->get_product()->get_sale_price();

		$sale_price = apply_filters( 'ip2ok_f_change_sale_price_simple', $sale_price, [ 'product' => $this->get_product() ], $this->get_feed_id() );
		$regular_price = apply_filters( 'ip2ok_f_change_regular_price_simple', $regular_price, [ 'product' => $this->get_product() ], $this->get_feed_id() );

		if ( $price > 0 && $price == $sale_price ) { // скидка есть
			$old_price = common_option_get( 'old_price', false, $this->get_feed_id(), 'ip2ok' );
			if ( $old_price === 'enabled' ) {
				return $sale_price;
			} else {
				return $regular_price;
			}
		} else { // скидки нет
			return $regular_price;
		}
	}

	public function get_name() {
		$name = $this->get_product()->get_title();
		$name = apply_filters( 'ip2ok_f_simple_name',
			$name,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $name;
	}

	/**
	 * Summary of get_url
	 * 
	 * @return string|null
	 */
	public function get_url() {
		$value = null;
		$product_link_button = common_option_get( 'product_link_button', false, $this->get_feed_id(), 'ip2ok' );
		if ( $product_link_button === 'enabled' ) {
			$value = htmlspecialchars( get_permalink( $this->get_product()->get_id() ) );
		}
		$value = apply_filters( 'ip2ok_f_simple_product_link_button',
			$value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $value;
	}

	/**
	 * Get the Picture info 
	 * 
	 * @return array
	 */
	public function get_picture() {
		$res_arr = [];
		$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );

		if ( ! empty( $thumb_id ) ) { // есть картинка у товара
			// если она больше 8 Мб, то пробуем вытащить более мелкие размеры
			if ( filesize( get_attached_file( $thumb_id ) > 8388608 ) ) {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'large', true );
				if ( filesize( get_attached_file( $thumb_id ) > 8388608 ) ) {
					$thumb_url = wp_get_attachment_image_src( $thumb_id, 'medium', true );
				}
			} else {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
			}
			$res_arr['url'] = $thumb_url[0]; /* урл оригинал миниатюры товара */
			$res_arr['id'] = $thumb_id; /* id миниатюры товара */

			$image_upload_method = common_option_get( 'image_upload_method', false, $this->get_feed_id(), 'ip2ok' );
			if ( $image_upload_method !== 'url' ) {
				$res_arr['url'] = get_attached_file( $res_arr['id'], $unfiltered = false );
			}
		}
		return $res_arr;
	}

	public function get_description() {
		$description_source = common_option_get( 'description', false, $this->get_feed_id(), 'ip2ok' );
		$desc_val = '';

		switch ( $description_source ) {
			case "full":
				$desc_val = $this->get_product()->get_description();
				break;
			case "excerpt":
				$desc_val = $this->get_product()->get_short_description();
				break;
			case "fullexcerpt":
				$desc_val = $this->get_product()->get_description();
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_short_description();
				}
				break;
			case "excerptfull":
				$desc_val = $this->get_product()->get_short_description();
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_description();
				}
				break;
			case "fullplusexcerpt":
				$desc_val = sprintf( '%1$s<br/>%2$s',
					$this->get_product()->get_description(),
					$this->get_product()->get_short_description()
				);
				break;
			case "excerptplusfull":
				$desc_val = sprintf( '%2$s<br/>%1$s',
					$this->get_product()->get_description(),
					$this->get_product()->get_short_description()
				);
				break;
			default:
				$desc_val = $this->get_product()->get_description();
		}

		$desc_val = apply_filters( 'ip2ok_f_simple_description',
			$desc_val,
			[ 
				'description_source' => $description_source,
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		// Заменим переносы строк, чтоб не вываливалась ошибка аттача
		// $desc_val = str_replace( [ "\r\n", "\r", "\n", PHP_EOL ], "\\n", $desc_val);
		$desc_val = strip_tags( $desc_val );
		// $desc_val = htmlspecialchars($desc_val);
		return $desc_val;
	}

	/* Getters */

	/**
	 * Get product
	 * 
	 * @return WC_Product
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	public function get_result() {
		return $this->result_arr;
	}
}