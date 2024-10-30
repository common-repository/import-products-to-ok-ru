<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The main class of the plugin Import Products to OK.ru
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			1.0.0 (02-03-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html 
 *					https://wp-kama.ru/function/wp_list_table
 * 
 * @param		
 *
 * @return		
 *
 * @depends			classes:	WP_List_Table
 *					traits:	
 *					methods:	
 *					functions:	common_option_get
 *					constants:	
 *					options:	
 *
 */

class IP2OK_WP_List_Table extends WP_List_Table {
	public function __construct() {
		global $status, $page;
		parent::__construct( [ 
			'plural' => '', // По умолчанию: '' ($this->screen->base);
			// Название для множественного числа, используется во всяких 
			// заголовках, например в css классах, в заметках, например 'posts', тогда 'posts' будет добавлен в 
			// класс table.

			'singular' => '', // По умолчанию: ''; 
			// Название для единственного числа, например 'post'.

			'ajax' => false, // По умолчанию: false; 
			// Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод 
			// _js_vars() в подвале, чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события.

			'screen' => null, // По умолчанию: null; 
			// Строка содержащая название хука, нужного для определения текущей страницы. 
			// Если null, то будет установлен текущий экран. 	
		] );
		add_action( 'admin_footer', [ $this, 'admin_header' ] ); // меняем ширину колонок
	}

	/**
	 * 	Сейчас у таблицы стандартные стили WordPress. Чтобы это исправить, вам нужно адаптировать классы CSS, 
	 * 	которые были автоматически применены к каждому столбцу. Название класса состоит из строки «column-» и 
	 * 	ключевого имени массива $columns, например «column-isbn» или «column-author».
	 *	В качестве примера мы переопределим ширину столбцов (для простоты, стили прописаны непосредственно в 
	 *	HTML разделе head)
	 */
	public function admin_header() {
		echo '<style type="text/css">#ip2ok_feed_id, .column-ip2ok_feed_id {width: 7%;}</style>';
	}

	/**
	 * 	Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы. 
	 *	Ключи в массиве должны быть теми же, что и в массиве данных, 
	 *	иначе соответствующие столбцы не будут отображены.
	 */
	public function get_columns() {
		$columns = [ 
			// флажок сортировки. см get_bulk_actions и column_cb
			'cb' => '<input type="checkbox" />',
			'ip2ok_feed_id' => __( 'Feed ID', 'import-products-to-ok-ru' ),
			'ip2ok_url_xml_file' => __( 'YML File', 'import-products-to-ok-ru' ),
			'ip2ok_run_cron' => __( 'Automatic file creation', 'import-products-to-ok-ru' ),
			'ip2ok_step_export' => __( 'Step of export', 'import-products-to-ok-ru' ),
			'ip2ok_date_sborki_end' => __( 'Generated', 'import-products-to-ok-ru' ),
			'ip2ok_count_products_in_feed' => __( 'Products', 'import-products-to-ok-ru' ),
		];
		return $columns;
	}
	/**
	 *	Метод вытаскивает из БД данные, которые будут лежать в таблице
	 *	$this->table_data();
	 */
	private function table_data() {
		$ip2ok_settings_arr = common_option_get( 'ip2ok_settings_arr' );
		$result_arr = [];
		if ( $ip2ok_settings_arr == '' || empty( $ip2ok_settings_arr ) ) {
			return $result_arr;
		}
		$ip2ok_settings_arr_keys_arr = array_keys( $ip2ok_settings_arr );
		for ( $i = 0; $i < count( $ip2ok_settings_arr_keys_arr ); $i++ ) {
			$key = $ip2ok_settings_arr_keys_arr[ $i ];

			$text_column_ip2ok_feed_id = $key;

			if ( $ip2ok_settings_arr[ $key ]['ip2ok_file_url'] === '' ) {
				$text_column_ip2ok_url_xml_file = __( 'Not created yet', 'import-products-to-ok-ru' );
			} else {
				$text_column_ip2ok_url_xml_file = sprintf( '<a target="_blank" href="%1$s">%1$s</a>',
					urldecode( $ip2ok_settings_arr[ $key ]['ip2ok_file_url'] )
				);
			}
			if ( $ip2ok_settings_arr[ $key ]['ip2ok_feed_assignment'] === '' ) {

			} else {
				$text_column_ip2ok_url_xml_file = sprintf( '%1$s<br/>(%2$s: %3$s)',
					$text_column_ip2ok_url_xml_file,
					__( 'Feed assignment', 'import-products-to-ok-ru' ),
					$ip2ok_settings_arr[ $key ]['ip2ok_feed_assignment']
				);
			}

			$ip2ok_status_cron = $ip2ok_settings_arr[ $key ]['ip2ok_status_cron'];
			switch ( $ip2ok_status_cron ) {
				case 'off':
					$text_column_ip2ok_run_cron = __( 'Off', 'import-products-to-ok-ru' );
					break;
				case 'five_min':
					$text_column_ip2ok_run_cron = __( 'Every five minutes', 'import-products-to-ok-ru' );
					break;
				case 'hourly':
					$text_column_ip2ok_run_cron = __( 'Hourly', 'import-products-to-ok-ru' );
					break;
				case 'six_hours':
					$text_column_ip2ok_run_cron = __( 'Every six hours', 'import-products-to-ok-ru' );
					break;
				case 'twicedaily':
					$text_column_ip2ok_run_cron = __( 'Twice a day', 'import-products-to-ok-ru' );
					break;
				case 'daily':
					$text_column_ip2ok_run_cron = __( 'Daily', 'import-products-to-ok-ru' );
					break;
				case 'week':
					$text_column_ip2ok_run_cron = __( 'Once a week', 'import-products-to-ok-ru' );
					break;
				default:
					$text_column_ip2ok_run_cron = __( "Don't start", "import-products-to-ok-ru" );
			}

			if ( $ip2ok_settings_arr[ $key ]['ip2ok_date_sborki_end'] === '0000000001' ) {
				$text_date_sborki_end = '-';
			} else {
				$text_date_sborki_end = $ip2ok_settings_arr[ $key ]['ip2ok_date_sborki_end'];
			}

			if ( $ip2ok_settings_arr[ $key ]['ip2ok_count_products_in_feed'] === '-1' ) {
				$text_count_products_in_feed = '-';
			} else {
				$text_count_products_in_feed = $ip2ok_settings_arr[ $key ]['ip2ok_count_products_in_feed'];
			}

			$result_arr[ $i ] = [ 
				'ip2ok_feed_id' => $text_column_ip2ok_feed_id,
				'ip2ok_url_xml_file' => $text_column_ip2ok_url_xml_file,
				'ip2ok_run_cron' => $text_column_ip2ok_run_cron,
				'ip2ok_step_export' => $ip2ok_settings_arr[ $key ]['ip2ok_step_export'],
				'ip2ok_date_sborki_end' => $text_date_sborki_end,
				'ip2ok_count_products_in_feed' => $text_count_products_in_feed
			];
		}

		return $result_arr;
	}

	/**
	 *	prepare_items определяет два массива, управляющие работой таблицы:
	 *	$hidden - определяет скрытые столбцы 
	 *			(https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options)
	 *	$sortable - определяет, может ли таблица быть отсортирована по этому столбцу
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns(); // вызов сортировки
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		// пагинация 
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->table_data() );
		$found_data = array_slice( $this->table_data(), ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args( [ 
			'total_items' => $total_items, // Мы должны вычислить общее количество элементов
			'per_page' => $per_page // Мы должны определить, сколько элементов отображается на странице
		] );
		// end пагинация 
		$this->items = $found_data; // $this->items = $this->table_data() // Получаем данные для формирования таблицы
	}

	/** 
	 * 	Данные таблицы.
	 *	Наконец, метод назначает данные из примера на переменную представления данных класса — items.
	 *	Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например, 
	 *	function column_ip2ok_url_xml_file. 
	 *	Такой метод должен быть указан для каждого столбца. Но чтобы не создавать эти методы для всех столбцов 
	 *	в отдельности, можно использовать column_default. Эта функция обработает все столбцы, для которых не определён
	 *	специальный метод.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ip2ok_feed_id':
			case 'ip2ok_url_xml_file':
			case 'ip2ok_run_cron':
			case 'ip2ok_step_export':
			case 'ip2ok_date_sborki_end':
			case 'ip2ok_count_products_in_feed':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Мы отображаем целый массив во избежание проблем
		}
	}

	/** 
	 * 	Функция сортировки.
	 *	Второй параметр в массиве значений $sortable_columns отвечает за порядок сортировки столбца. 
	 *	Если значение true, столбец будет сортироваться в порядке возрастания, если значение false, столбец 
	 *	сортируется в порядке убывания, или не упорядочивается. Это необходимо для маленького треугольника 
	 *	около названия столбца, который указывает порядок сортировки, чтобы строки отображались 
	 *	в правильном направлении
	 */
	public function get_sortable_columns() {
		$sortable_columns = [ 
			'ip2ok_url_xml_file' => [ 'ip2ok_url_xml_file', false ],
			// 'ip2ok_run_cron'	=> [ 'ip2ok_run_cron', false ]
		];
		return $sortable_columns;
	}

	/**
	 * 	Действия.
	 *	Эти действия появятся, если пользователь проведет курсор мыши над таблицей
	 *	column_{key_name} - в данном случае для колонки ip2ok_url_xml_file - function column_ip2ok_url_xml_file
	 */
	public function column_ip2ok_url_xml_file( $item ) {
		$actions = [ 
			'edit' => sprintf( '<a href="?page=%s&action=%s&feed_id=%s">%s</a>',
				$_REQUEST['page'],
				'edit',
				$item['ip2ok_feed_id'],
				__( 'Edit', 'import-products-to-ok-ru' )
			),
			'duplicate' => sprintf( '<a href="?page=%s&action=%s&feed_id=%s&_wpnonce=%s">%s</a>',
				$_REQUEST['page'],
				'duplicate',
				$item['ip2ok_feed_id'],
				wp_create_nonce( 'nonce_duplicate' . $item['ip2ok_feed_id'] ),
				__( 'Duplicate', 'import-products-to-ok-ru' )
			)
		];

		return sprintf( '%1$s %2$s', $item['ip2ok_url_xml_file'], $this->row_actions( $actions ) );
	}

	/**
	 * 	Массовые действия.
	 *	Bulk action осуществляются посредством переписывания метода get_bulk_actions() и возврата связанного массива
	 *	Этот код просто помещает выпадающее меню и кнопку «применить» вверху и внизу таблицы
	 *	ВАЖНО! Чтобы работало нужно оборачивать вызов класса в form:
	 *	<form id="events-filter" method="get"> 
	 *	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> 
	 *	<?php $wp_list_table->display(); ?> 
	 *	</form> 
	 */
	public function get_bulk_actions() {
		$actions = [ 
			'delete' => __( 'Delete', 'import-products-to-ok-ru' )
		];
		return $actions;
	}

	/**
	 * Флажки для строк должны быть определены отдельно. Как упоминалось выше, есть метод column_{column} 
	 * для отображения столбца. cb-столбец – особый случай.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="checkbox_xml_file[]" value="%s" />', $item['ip2ok_feed_id']
		);
	}

	/**
	 * Нет элементов.
	 * Если в списке нет никаких элементов, отображается стандартное сообщение «No items found.». 
	 * Если вы хотите изменить это сообщение, вы можете переписать метод no_items():
	 */
	public function no_items() {
		_e( 'No XML feed found', 'import-products-to-ok-ru' );
	}
}