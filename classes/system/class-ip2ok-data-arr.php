<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Set and Get the Plugin Data
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			0.1.1 (13-04-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 * 
 * @param
 *
 * @return			
 *
 * @depends			classes:	
 *					traits:	
 *					methods:	
 *					functions:	
 *								
 *					constants:	
 *
 */

class IP2OK_Data_Arr {
	private $data_arr = [];

	public function __construct( $data_arr = [] ) {
		$this->data_arr = [ 
			[ 
				'opt_name' => 'status_sborki',
				'def_val' => '-1',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ // дата начала сборки
				'opt_name' => 'date_sborki',
				'def_val' => '0000000001',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ // дата завершения сборки
				'opt_name' => 'date_sborki_end',
				'def_val' => '0000000001',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ // дата сохранения настроек плагина
				'opt_name' => 'date_save_set',
				'def_val' => '0000000001',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ // число товаров, попавших в выгрузку
				'opt_name' => 'count_products_in_feed',
				'def_val' => '-1',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],
			[ 
				'opt_name' => 'status_cron',
				'def_val' => 'off',
				'mark' => 'private',
				'required' => true,
				'type' => 'auto',
				'tab' => 'none'
			],

			[ 
				'opt_name' => 'application_id',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'api_tab',
				'data' => [ 
					'label' => __( 'Application ID', 'import-products-to-ok-ru' ),
					'desc' => 'Application ID - ' . __( 'from a letter from OK.ru', 'import-products-to-ok-ru' ),
					'placeholder' => ''
				]
			],
			[ 
				'opt_name' => 'public_key',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'api_tab',
				'data' => [ 
					'label' => __( 'Public key', 'import-products-to-ok-ru' ),
					'desc' => 'public_key - ' . __( 'from a letter from OK.ru', 'import-products-to-ok-ru' ),
					'placeholder' => ''
				]
			],
			[ 
				'opt_name' => 'group_id',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'api_tab',
				'data' => [ 
					'label' => __( 'Group ID', 'import-products-to-ok-ru' ),
					'desc' => 'group_id - ' . __( 'from the settings of the group to which we export products', 'import-products-to-ok-ru' ),
					'placeholder' => ''
				]
			],
			[ 
				'opt_name' => 'access_token',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'api_tab',
				'data' => [ 
					'label' => __( 'Access token', 'import-products-to-ok-ru' ),
					'desc' => 'access_token - ' . __( 'from the app settings', 'import-products-to-ok-ru' ),
					'placeholder' => ''
				]
			],
			[ 
				'opt_name' => 'private_key',
				'def_val' => '',
				'mark' => 'public',
				'required' => true,
				'type' => 'text',
				'tab' => 'api_tab',
				'data' => [ 
					'label' => __( 'Private key', 'import-products-to-ok-ru' ),
					'desc' => 'private_key - ' . __( 'from a letter from OK.ru', 'import-products-to-ok-ru' ),
					'placeholder' => ''
				]
			],

			[ 
				'opt_name' => 'syncing_with_ok',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Syncing with OK.ru', 'import-products-to-ok-ru' ),
					'desc' => __( 'Using this parameter, you can stop the plugin completely', 'import-products-to-ok-ru' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-products-to-ok-ru' ) ]
					],
					'tr_class' => 'ip2ok_tr'
				]
			],
			[ 
				'opt_name' => 'run_cron',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'The frequency of full synchronization of products', 'import-products-to-ok-ru' ),
					'desc' => __( 'With the specified frequency, the plugin will transmit information about all your products to OK.ru', 'import-products-to-ok-ru' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'hourly', 'text' => __( 'Hourly', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'six_hours', 'text' => __( 'Every six hours', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'twicedaily', 'text' => __( 'Twice a day', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'daily', 'text' => __( 'Daily', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'daily', 'text' => __( 'Once a week', 'import-products-to-ok-ru' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'step_export',
				'def_val' => '500',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Step export', 'import-products-to-ok-ru' ),
					'desc' => __( 'Determines the maximum number of products uploaded to OK.ru in one minute', 'import-products-to-ok-ru' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => '50', 'text' => '50' ],
						[ 'value' => '100', 'text' => '100' ],
						[ 'value' => '200', 'text' => '200' ],
						[ 'value' => '300', 'text' => '300' ],
						[ 'value' => '400', 'text' => '400' ],
						[ 'value' => '500', 'text' => '500 (' . __( 'The maximum value allowed by OK.ru', 'import-products-to-ok-ru' ) . ')' ]
					]
				]
			],
			[ 
				'opt_name' => 'image_upload_method',
				'def_val' => 'path',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Image upload method', 'import-products-to-ok-ru' ),
					'desc' => __( 'If you use a separate server for storing images, then select', 'import-products-to-ok-ru' ) . ' URL',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'path', 'text' => 'path' ],
						[ 'value' => 'url', 'text' => 'URL' ]
					]
				]
			],
			[ 
				'opt_name' => 'behavior_cats',
				'def_val' => 'id',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Categories on OK.ru', 'import-products-to-ok-ru' ),
					'desc' => sprintf( '%s "%s" %s <a href="https://icopydoc.ru/sbivayutsya-oblozhki-v-kataloge-tovarov-pri-importe-na-ok-ru-reshenie/?utm_source=%s">%s</a>',
						__( 'If you set the value', 'import-products-to-ok-ru' ),
						__( 'Always update', 'import-products-to-ok-ru' ),
						__( 'then with each import, the covers in the product catalog may be lost', 'import-products-to-ok-ru' ),
						'import-products-to-ok-ru&utm_medium=organic&utm_campaign=in-plugin-import-products-to-ok-ru&utm_content=settings&utm_term=sbivayutsya-oblozhki',
						__( 'Read more', 'import-products-to-ok-ru' )
					),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'upd_once', 'text' => __( 'Update once', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'upd_on', 'text' => __( 'Always update', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'upd_off', 'text' => __( 'Do not update', 'import-products-to-ok-ru' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'description',
				'def_val' => 'fullexcerpt',
				'mark' => 'public',
				'required' => true,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Description of the product', 'import-products-to-ok-ru' ),
					'desc' => '[description] - ' . __( 'The source of the description', 'import-products-to-ok-ru' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'excerpt', 'text' => __( 'Only Excerpt description', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'full', 'text' => __( 'Only Full description', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'excerptfull', 'text' => __( 'Excerpt or Full description', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'fullexcerpt', 'text' => __( 'Full or Excerpt description', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'excerptplusfull', 'text' => __( 'Excerpt plus Full description', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'fullplusexcerpt', 'text' => __( 'Full plus Excerpt description', 'import-products-to-ok-ru' ) ]
					],
					'tr_class' => 'ip2ok_tr'
				]
			],
			[ 
				'opt_name' => 'var_desc_priority',
				'def_val' => 'enabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'The varition description takes precedence over others', 'import-products-to-ok-ru' ),
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-products-to-ok-ru' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'skip_missing_products',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Skip missing products', 'import-products-to-ok-ru' ) . ' (' . __( 'except for products for which a pre-order is permitted', 'import-products-to-ok-ru' ) . ')',
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-products-to-ok-ru' ) ]
					],
					'tr_class' => 'ip2ok_tr'
				]
			],
			[ 
				'opt_name' => 'skip_backorders_products',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Skip backorders products', 'import-products-to-ok-ru' ),
					'desc' => '',
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-products-to-ok-ru' ) ]
					]
				]
			],
			[ 
				'opt_name' => 'old_price',
				'def_val' => 'disabled',
				'mark' => 'public',
				'required' => false,
				'type' => 'select',
				'tab' => 'main_tab',
				'data' => [ 
					'label' => __( 'Old price', 'import-products-to-ok-ru' ),
					'desc' => __( 'In oldprice indicates the old price of the goods, which must necessarily be higher than the new price (price)', 'import-products-to-ok-ru' ),
					'woo_attr' => false,
					'key_value_arr' => [ 
						[ 'value' => 'disabled', 'text' => __( 'Disabled', 'import-products-to-ok-ru' ) ],
						[ 'value' => 'enabled', 'text' => __( 'Enabled', 'import-products-to-ok-ru' ) ]
					]
				]
			]
		];


		if ( ! empty( $data_arr ) ) {
			$this->data_arr = $data_arr;
		}
		/*
			  array_push($this->data_arr,
				  array('shop_name', $this->blog_title, 'public'),
				  array('company_name', $this->blog_title, 'public')
			  );*/

		$this->data_arr = apply_filters( 'ip2ok_set_default_feed_settings_result_arr_filter', $this->data_arr );
	}

	public function get_data_arr() {
		return $this->data_arr;
	}

	// @return array([0] => opt_key1, [1] => opt_key2, ...)
	public function get_data_for_tabs( $whot = '' ) {
		$res_arr = [];
		if ( $this->data_arr ) {
			for ( $i = 0; $i < count( $this->data_arr ); $i++ ) {
				switch ( $whot ) {
					case "main_tab":
						if ( $this->data_arr[ $i ]['tab'] === 'main_tab' ) {
							$arr = $this->data_arr[ $i ]['data'];
							$arr['opt_name'] = $this->data_arr[ $i ]['opt_name'];
							$arr['tab'] = $this->data_arr[ $i ]['tab'];
							$arr['type'] = $this->data_arr[ $i ]['type'];
							$res_arr[] = $arr;
						}
						break;
					case "api_tab":
						if ( $this->data_arr[ $i ]['tab'] === 'api_tab' ) {
							$arr = $this->data_arr[ $i ]['data'];
							$arr['opt_name'] = $this->data_arr[ $i ]['opt_name'];
							$arr['tab'] = $this->data_arr[ $i ]['tab'];
							$arr['type'] = $this->data_arr[ $i ]['type'];
							$res_arr[] = $arr;
						}
						break;
					default:
						$arr = $this->data_arr[ $i ]['data'];
						$arr['opt_name'] = $this->data_arr[ $i ]['opt_name'];
						$arr['tab'] = $this->data_arr[ $i ]['tab'];
						$arr['type'] = $this->data_arr[ $i ]['type'];
						$res_arr[] = $arr;
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	// @return array([0] => opt_key1, [1] => opt_key2, ...)
	public function get_opts_name( $whot = '' ) {
		$res_arr = [];
		if ( $this->data_arr ) {
			for ( $i = 0; $i < count( $this->data_arr ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->data_arr[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->data_arr[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->data_arr[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->data_arr[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->data_arr[ $i ]['opt_name'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	// @return array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)
	public function get_opts_name_and_def_date( $whot = 'all' ) {
		$res_arr = [];
		if ( $this->data_arr ) {
			for ( $i = 0; $i < count( $this->data_arr ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->data_arr[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->data_arr[ $i ]['opt_name'] ] = $this->data_arr[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->data_arr[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->data_arr[ $i ]['opt_name'] ] = $this->data_arr[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->data_arr[ $i ]['opt_name'] ] = $this->data_arr[ $i ]['def_val'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {
		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$res_arr[] = new IP2OK_Data_Arr_Helper( $key, $value ); // return unit obj
		}
		return $res_arr;
	}
}
class IP2OK_Data_Arr_Helper {
	private $opt_name;
	private $opt_def_value;

	public function __construct( $name = '', $def_value = '' ) {
		$this->opt_name = $name;
		$this->opt_def_value = $def_value;
	}

	public function get_name() {
		return $this->opt_name;
	}

	public function get_value() {
		return $this->opt_def_value;
	}
}