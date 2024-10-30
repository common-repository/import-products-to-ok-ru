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
 * @version			0.1.0 (11-02-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 * 
 * @param			
 *
 * @return			
 *
 * @depends			classes:	IP2OK_Data_Arr
 *								IP2OK_Settings_Page
 *								IP2OK_Debug_Page
 *								IP2OK_Error_Log
 *								IP2OK_Generation_XML
 *								IP2OK_RU_Api_Helper
 *					traits:	
 *					methods:	
 *					functions:	common_option_get
 *								common_option_upd
 *					constants:	IP2OK_PLUGIN_VERSION
 *								IP2OK_PLUGIN_BASENAME
 *								IP2OK_PLUGIN_DIR_URL
 *
 */

final class IP2OK {
	private $plugin_version = IP2OK_PLUGIN_VERSION; // 0.1.0

	protected static $instance;
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'ip2ok_keeplogs', '' );
			add_blog_option( get_current_blog_id(), 'ip2ok_disable_notices', '' );
			add_blog_option( get_current_blog_id(), 'ip2ok_group_content', '' );

			add_blog_option( get_current_blog_id(), 'ip2ok_settings_arr', [] );
			// add_blog_option(get_current_blog_id(), 'ip2ok_registered_groups_arr', [ ]);
		} else {
			add_option( 'ip2ok_keeplogs', '' );
			add_option( 'ip2ok_disable_notices', '' );
			add_option( 'ip2ok_group_content', '' );

			add_option( 'ip2ok_settings_arr', [] );
			// add_option('ip2ok_registered_groups_arr', [ ]);
		}
	}

	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

	}

	public function __construct() {
		$this->check_options_upd(); // проверим, нужны ли обновления опций плагина
		$this->init_classes();
		$this->init_hooks(); // подключим хуки
	}

	public function check_options_upd() {
		if ( false == common_option_get( 'ip2ok_version' ) ) { // это первая установка
			$ip2ok_data_arr_obj = new IP2OK_Data_Arr();
			$opts_arr = $ip2ok_data_arr_obj->get_opts_name_and_def_date( 'all' ); // массив дефолтных настроек
			common_option_upd( 'ip2ok_settings_arr', $opts_arr, 'no' ); // пишем все настройки
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'ip2ok_version', $this->plugin_version );
			} else {
				update_option( 'ip2ok_version', $this->plugin_version );
			}
		} else {
			$this->set_new_options();
		}
	}

	public function init_classes() {
		// new IP2OK_RU_Api_Helper();
		return;
	}

	public function init_hooks() {
		add_action( 'admin_init', [ $this, 'listen_submits' ], 9 ); // ещё можно слушать чуть раньше на wp_loaded
		add_action( 'admin_init', function () {
			wp_register_style( 'ip2ok-admin-css', IP2OK_PLUGIN_DIR_URL . 'css/ip2ok-style.css' );
		}, 9999 ); // Регаем стили только для страницы настроек плагина
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 10, 1 );

		// add_action('admin_notices', [ $this, 'admin_notices' ], 10, 1);

		add_action( 'ip2ok_cron_sborki', [ $this, 'do_this_seventy_sec' ], 10, 1 );
		add_action( 'ip2ok_cron_period', [ $this, 'do_this_event' ], 10, 1 );
		add_action( 'edit_form_after_title', [ $this, 'output_url_imported_product' ], 10, 1 );
		add_action( 'save_post', [ $this, 'save_post_product' ], 50, 3 );

		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ], 10, 1 );
		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		// https://wpruse.ru/woocommerce/custom-fields-in-products/
		// https://wpruse.ru/woocommerce/custom-fields-in-variations/
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'added_wc_tabs' ], 10, 1 );
		add_action( 'woocommerce_product_data_panels', [ $this, 'added_tabs_panel' ], 10, 1 );
	}

	public function added_wc_tabs( $tabs ) {
		$tabs['ip2ok_special_panel'] = [ 
			'label' => __( 'Import Products to OK.ru', 'import-products-to-ok-ru' ), // название вкладки
			'target' => 'ip2ok_added_wc_tabs', // идентификатор вкладки
			'class' => [ 'hide_if_grouped' ], // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70, // приоритет вывода
		];
		return $tabs;
	}

	public function added_tabs_panel() {
		global $post; ?>
		<div id="ip2ok_added_wc_tabs" class="panel woocommerce_options_panel">
			<?php do_action( 'ip2ok_before_options_group', $post ); ?>
			<div class="options_group">
				<h2><strong>
						<?php
						_e( 'Individual product settings for export to OK.ru', 'import-products-to-ok-ru' );
						?>
					</strong></h2>
				<?php if ( ! class_exists( 'IP2OKP' ) ) : ?>
					<p>
						<?php
						_e( 'The settings will appear after installing the PRO version', 'import-products-to-ok-ru' );
						?>
					</p>
				<?php endif;
				do_action( 'ip2ok_prepend_options_group', $post );
				do_action( 'ip2ok_append_options_group', $post );
				?>
			</div>
			<?php do_action( 'ip2ok_after_options_group', $post ); ?>
		</div>
		<?php
	}

	public function listen_submits() {
		do_action( 'ip2ok_listen_submits' );

		if ( empty( common_option_get( 'application_id', false, '1', 'ip2ok' ) )
			|| empty( common_option_get( 'access_token', false, '1', 'ip2ok' ) )
			|| empty( common_option_get( 'group_id', false, '1', 'ip2ok' ) )
			|| empty( common_option_get( 'public_key', false, '1', 'ip2ok' ) )
			|| empty( common_option_get( 'private_key', false, '1', 'ip2ok' ) )
		) {
			$message = sprintf(
				'IP2OK: %s ok.ru! <a href="/wp-admin/admin.php?page=ip2ok-import&tab=api_tab&feed_id=1">%s</a>',
				__( 'You must configure the connection to the', 'import-products-to-ok-ru' ),
				__( 'Go to settings', 'import-products-to-ok-ru' )
			);
			$class = 'notice-error';
			add_action( 'admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		if ( isset( $_REQUEST['ip2ok_submit_action'] ) ) {
			$message = __( 'Updated', 'import-products-to-ok-ru' );
			$class = 'notice-success';

			add_action( 'admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		$status_sborki = (int) common_option_get( 'status_sborki', false, '1', 'ip2ok' );
		$step_export = (int) common_option_get( 'step_export', false, '1', 'ip2ok' );

		if ( $status_sborki == 1 ) {
			$message = sprintf( 'IP2OK: %1$s. %2$s: 1. %3$s',
				__( 'Import products is running', 'import-products-to-ok-ru' ),
				__( 'Step', 'import-products-to-ok-ru' ),
				__( 'Importing a list of categories', 'import-products-to-ok-ru' )
			);
		} else if ( $status_sborki > 1 ) {
			$message = sprintf( 'IP2OK: %1$s. %2$s: 2. %3$s %4$s',
				__( 'Import products is running', 'import-products-to-ok-ru' ),
				__( 'Step', 'import-products-to-ok-ru' ),
				__( 'Processed products', 'import-products-to-ok-ru' ),
				$status_sborki * $step_export
			);
		} else {
			$message = '';
		}

		if ( ! empty( $message ) ) {
			$class = 'notice-success';
			add_action( 'admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}
	}

	// Добавляем пункты меню
	public function add_admin_menu() {
		$page_suffix = add_menu_page(
			null,
			'Import Products to OK.ru',
			'manage_woocommerce',
			'ip2ok-import',
			[ $this, 'get_plugin_settings_page' ],
			'dashicons-redo', 51
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );

		$page_suffix = add_submenu_page(
			'ip2ok-import',
			__( 'Debug', 'import-products-to-ok-ru' ),
			__( 'Debug page', 'import-products-to-ok-ru' ),
			'manage_woocommerce',
			'ip2ok-debug',
			[ $this, 'get_debug_page' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );

		$page_suffix = add_submenu_page(
			'ip2ok-import',
			__( 'Add Extensions', 'import-products-to-ok-ru' ),
			__( 'Add Extensions', 'import-products-to-ok-ru' ),
			'manage_woocommerce',
			'ip2ok-extensions',
			[ $this, 'get_extensions_page' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );
	}

	public function get_plugin_settings_page() {
		new IP2OK_Settings_Page( 'ip2ok-import' );
		return;
	}

	// вывод страницы настроек плагина
	public function get_debug_page() {
		new IP2OK_Debug_Page();
		return;
	}

	// вывод страницы настроек плагина
	public function get_extensions_page() {
		ip2ok_extensions_page();
		return;
	}

	public function get_plugin_version() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'ip2ok_version' );
		} else {
			$v = get_option( 'ip2ok_version' );
		}
		return $v;
	}

	public function admin_enqueue_style_css() {
		wp_enqueue_style( 'ip2ok-admin-css' ); // Ставим css-файл в очередь на вывод
	}

	public function do_this_seventy_sec( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id === (int) 1 || $feed_id === (float) 1 ) {
			$feed_id = (string) $feed_id;
		}
		if ( $feed_id == '' ) {
			common_option_upd( 'status_sborki', '-1', 'no', $feed_id, 'ip2ok' );
			wp_clear_scheduled_hook( 'ip2ok_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'ip2ok_cron_period', [ $feed_id ] );
			return;
		}

		new IP2OK_Error_Log( 'Cтартовала крон-задача do_this_seventy_sec' );
		$generation = new IP2OK_Generation_XML( $feed_id ); // делаем что-либо каждые 70 сек
		$generation->run();
	}

	public function do_this_event( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id === (int) 1 || $feed_id === (float) 1 ) {
			$feed_id = (string) $feed_id;
		}
		if ( $feed_id == '' ) {
			common_option_upd( 'status_sborki', '-1', 'no', $feed_id, 'ip2ok' );
			wp_clear_scheduled_hook( 'ip2ok_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'ip2ok_cron_period', [ $feed_id ] );
			return;
		}

		new IP2OK_Error_Log(
			sprintf( 'FEED № %1$s; Крон-функция do_this_event включена согласно интервала; Файл: %2$s; Строка: %3$s',
				$feed_id,
				'class-ip2ok.php',
				__LINE__
			)
		);
		$step_export = (int) common_option_get( 'step_export', false, $feed_id, 'ip2ok' );
		common_option_upd( 'status_sborki', '1', 'no', $feed_id, 'ip2ok' );
		wp_clear_scheduled_hook( 'ip2ok_cron_sborki', [ $feed_id ] );

		// Возвращает nul/false. null когда планирование завершено. false в случае неудачи.
		$res = wp_schedule_event( time(), 'seventy_sec', 'ip2ok_cron_sborki', [ $feed_id ] );
		if ( $res === false ) {
			new IP2OK_Error_Log(
				sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$feed_id,
					'ERROR: Не удалось запланировань CRON seventy_sec',
					'class-ip2ok.php',
					__LINE__
				)
			);
		} else {
			new IP2OK_Error_Log(
				sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$feed_id,
					'CRON seventy_sec успешно запланирован',
					'class-ip2ok.php',
					__LINE__
				)
			);
		}
	}

	public static function output_url_imported_product( $post ) {
		if ( $post->post_type !== 'product' ) {
			return;
		} // если это не товар вукомерц
		if ( get_post_meta( $post->ID, 'iptok_prod_id_on_ok', true ) !== '' ) {
			printf(
				'<div id="ip2ok_url"><strong>%1$s</strong>: <a href="https://ok.ru/group/%2$s/product/%3$s" 
				target="_blank">https://ok.ru/group/%2$s/product/%3$s</a></div>',
				__( 'Product on ok.ru', 'import-products-to-ok-ru' ),
				common_option_get( 'group_id', false, '1', 'ip2ok' ),
				get_post_meta( $post->ID, 'iptok_prod_id_on_ok', true )
			);
			print '<style>#ip2ok_url{line-height:24px;min-height:25px;margin:0px;padding:0 10px;color:#666;}</style>';
		}
	}

	public function save_post_product( $post_id, $post, $update ) {
		if ( $post->post_type !== 'product' ) {
			return;
		} // если это не товар вукомерц
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		} // если это ревизия
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		} // если это автосохранение ничего не делаем
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		} // проверяем права юзера

		$post_meta_arr = [];
		$post_meta_arr = apply_filters( 'ip2ok_f_post_meta_arr', $post_meta_arr );
		if ( ! empty( $post_meta_arr ) ) {
			$this->save_post_meta( $post_meta_arr, $post_id );
		}

		// если экспорт глобально запрещён
		$syncing_with_ok = common_option_get( 'syncing_with_ok', false, '1', 'ip2ok' );
		if ( $syncing_with_ok === 'disabled' ) {
			new IP2OK_Error_Log(
				sprintf( '%1$s; Файл: %2$s; Строка: %3$s',
					'NOTICE: Включён глобальный запрет на импорт',
					'class-ip2ok.php',
					__LINE__
				)
			);
			return;
		}

		$api = new IP2OK_RU_Api();
		$answer_arr = $api->product_sync( $post_id );
		if ( true == $answer_arr['status'] ) {
			new IP2OK_Error_Log(
				sprintf( 'FEED № 1; товара с $post_id = %1$s успешно импортирован; Файл: %2$s; Строка: %3$s',
					$post_id,
					'class-ip2ok.php',
					__LINE__
				)
			);
		} else {
			new IP2OK_Error_Log(
				sprintf( 'FEED № 1; ошибка добавления товара с $post_id = %1$s; Файл: %2$s; Строка: %3$s',
					$post_id,
					'class-ip2ok.php',
					__LINE__
				)
			);
			new IP2OK_Error_Log( $answer_arr );
		}
	}

	public function add_cron_intervals( $schedules ) {
		$schedules['seventy_sec'] = [ 
			'interval' => 70,
			'display' => __( '70 seconds', 'import-products-to-ok-ru' )
		];
		$schedules['five_min'] = [ 
			'interval' => 300,
			'display' => __( '5 minutes', 'import-products-to-ok-ru' )
		];
		$schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( '6 hours', 'import-products-to-ok-ru' )
		];
		$schedules['week'] = [ 
			'interval' => 604800,
			'display' => __( '1 week', 'import-products-to-ok-ru' )
		];
		return $schedules;
	}

	public function add_plugin_action_links( $actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, IP2OK_PLUGIN_BASENAME ) ) { // проверка, что у нас текущий плагин
			return $actions;
		}

		$settings_link = sprintf( '<a style="%s" href="/wp-admin/admin.php?page=%s">%s</a>',
			'color: green; font-weight: 700;',
			'ip2ok-extensions',
			__( 'More features', 'import-products-to-ok-ru' )
		);
		array_unshift( $actions, $settings_link );

		$settings_link = sprintf( '<a href="/wp-admin/admin.php?page=%s">%s</a>',
			'ip2ok-import',
			__( 'Settings', 'import-products-to-ok-ru' )
		);
		array_unshift( $actions, $settings_link );
		return $actions;
	}

	private function set_new_options() {
		// Если предыдущая версия плагина меньше текущей
		if ( version_compare( $this->get_plugin_version(), $this->plugin_version, '<' ) ) {
			// получаем список дефолтных настроек
			$ip2ok_data_arr_obj = new IP2OK_Data_Arr();
			$default_settings_obj = $ip2ok_data_arr_obj->get_opts_name_and_def_date_obj( 'all' );

			$settings_arr = common_option_get( 'ip2ok_settings_arr' );
			for ( $i = 0; $i < count( $default_settings_obj ); $i++ ) {
				$name = $default_settings_obj[ $i ]->get_name();
				$value = $default_settings_obj[ $i ]->get_value();
				if ( ! isset( $settings_arr[ $name ] ) ) {
					$settings_arr[ $name ] = $value;
				}
			}
			common_option_upd( 'ip2ok_settings_arr', $settings_arr, 'no' ); // пишем все настройки
		} else { // обновления не требуются
			return;
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'ip2ok_version', $this->plugin_version );
		} else {
			update_option( 'ip2ok_version', $this->plugin_version );
		}
	}

	private function admin_notices_func( $message, $class ) {
		$ip2ok_disable_notices = common_option_get( 'ip2ok_disable_notices' );
		if ( $ip2ok_disable_notices === 'on' ) {
			return;
		} else {
			printf( '<div class="notice %1$s"><p>%2$s</p></div>', $class, $message );
			return;
		}
	}

	private function save_post_meta( $post_meta_arr, $post_id ) {
		for ( $i = 0; $i < count( $post_meta_arr ); $i++ ) {
			$meta_name = $post_meta_arr[ $i ];
			if ( isset( $_POST[ $meta_name ] ) ) {
				update_post_meta( $post_id, $meta_name, sanitize_text_field( $_POST[ $meta_name ] ) );
			}
		}
	}
} /* end class IP2OK */