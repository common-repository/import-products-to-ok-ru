<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This class is responsible for the feedback form inside the plugin
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
 * @depends			classes:	IP2OK_Debug_Page
 *					traits:	
 *					methods:	
 *					functions:	univ_option_get
 *								univ_option_upd
 *								
 *					constants:	IP2OK_PLUGIN_VERSION
 *								IP2OK_SITE_UPLOADS_URL
 *								IP2OK_SITE_UPLOADS_DIR_PATH
 *
 */

final class IP2OK_Feedback {
	private $pref = 'ip2ok';

	public function __construct( $pref = null ) {
		if ( $pref ) {
			$this->pref = $pref;
		}

		$this->listen_submits_func();
	}

	public function get_form() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Send data about the work of the plugin', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<p>
					<?php _e( 'Sending statistics you help make the plugin even better', 'import-products-to-ok-ru' ); ?>!
					<?php _e( 'The following data will be transferred', 'import-products-to-ok-ru' ); ?>:
				</p>
				<ul class="ip2ok_ul">
					<li>
						<?php _e( 'PHP version information', 'import-products-to-ok-ru' ); ?>
					</li>
					<li>
						<?php _e( 'Multisite mode status', 'import-products-to-ok-ru' ); ?>
					</li>
					<li>
						<?php _e( 'Technical information and plugin logs', 'import-products-to-ok-ru' ); ?> Import products to
						OK.ru
					</li>
				</ul>
				<p>
					<?php _e( 'Did my plugin help you upload your products to the', 'import-products-to-ok-ru' ); ?> Import
					products to OK.ru?
				</p>
				<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<p>
						<input type="radio" name="<?php echo $this->get_radio_name(); ?>" value="yes"><?php _e( 'Yes', 'import-products-to-ok-ru' ); ?><br />
						<input type="radio" name="<?php echo $this->get_radio_name(); ?>" value="no"><?php _e( 'No', 'import-products-to-ok-ru' ); ?><br />
					</p>
					<p>
						<?php _e( "If you don't mind to be contacted in case of problems, please enter your email address", "import-products-to-ok-ru" ); ?>.
					</p>
					<p><input type="email" name="<?php echo $this->get_input_name(); ?>" placeholder="your@email.com"></p>
					<p>
						<?php _e( 'Your message', 'import-products-to-ok-ru' ); ?>:
					</p>
					<p><textarea rows="6" cols="32" name="<?php echo $this->get_textarea_name(); ?>"
							placeholder="<?php _e( 'Enter your text to send me a message (You can write me in Russian or English). I check my email several times a day', 'import-products-to-ok-ru' ); ?>"></textarea>
					</p>
					<?php wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_field() ); ?>
					<input class="button-primary" type="submit" name="<?php echo $this->get_submit_name(); ?>"
						value="<?php _e( 'Send data', 'import-products-to-ok-ru' ); ?>" />
				</form>
			</div>
		</div>
		<?php
	}

	public function get_block_support_project() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Please support the project', 'import-products-to-ok-ru' ); ?>!
			</h2>
			<div class="inside">
				<p>
					<?php _e( 'Thank you for using the plugin', 'import-products-to-ok-ru' ); ?> <strong>Import products to
						OK.ru</strong>
				</p>
				<p>
					<?php _e( 'Please help make the plugin better', 'import-products-to-ok-ru' ); ?> <a
						href="https://forms.gle/qn1QPRuLQKmXkJsP6" target="_blank">
						<?php _e( 'answering 6 questions', 'import-products-to-ok-ru' ); ?>!
					</a>
				</p>
				<p>
					<?php _e( 'If this plugin useful to you, please support the project one way', 'import-products-to-ok-ru' ); ?>:
				</p>
				<ul class="ip2ok_ul">
					<li><a href="//wordpress.org/support/plugin/import-products-to-ok-ru/reviews/" target="_blank">
							<?php _e( 'Leave a comment on the plugin page', 'import-products-to-ok-ru' ); ?>
						</a>.</li>
					<li>
						<?php _e( 'Support the project financially', 'import-products-to-ok-ru' ); ?>. <a
							href="//pay.cloudtips.ru/p/45d8ff3f" target="_blank">
							<?php _e( 'Donate now', 'import-products-to-ok-ru' ); ?>
						</a>.
					</li>
					<li>
						<?php _e( 'Noticed a bug or have an idea how to improve the quality of the plugin', 'import-products-to-ok-ru' ); ?>?
						<a href="mailto:support@icopydoc.ru">
							<?php _e( 'Let me know', 'import-products-to-ok-ru' ); ?>
						</a>.
					</li>
				</ul>
				<p>
					<?php _e( 'The author of the plugin Maxim Glazunov', 'import-products-to-ok-ru' ); ?>.
				</p>
				<p><span style="color: red;">
						<?php _e( 'Accept orders for individual revision of the plugin', 'import-products-to-ok-ru' ); ?>
					</span>:<br /><a href="mailto:support@icopydoc.ru">
						<?php _e( 'Leave a request', 'import-products-to-ok-ru' ); ?>
					</a>.</p>
			</div>
		</div>
		<?php
	}

	private function get_pref() {
		return $this->pref;
	}

	private function get_radio_name() {
		return $this->get_pref() . '_its_ok';
	}

	private function get_input_name() {
		return $this->get_pref() . '_email';
	}

	private function get_textarea_name() {
		return $this->get_pref() . '_message';
	}

	private function get_submit_name() {
		return $this->get_pref() . '_submit_send_stat';
	}

	private function get_nonce_action() {
		return $this->get_pref() . '_nonce_action_send_stat';
	}

	private function get_nonce_field() {
		return $this->get_pref() . '_nonce_field_send_stat';
	}

	private function listen_submits_func() {
		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			$this->send_data();
			add_action( 'admin_notices', function () {
				$class = 'notice notice-success is-dismissible';
				$message = __( 'The data has been sent. Thank you', 'import-products-to-ok-ru' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}, 9999 );
		}
	}

	private function send_data() {
		if ( ! empty( $_POST ) && check_admin_referer( $this->get_nonce_action(), $this->get_nonce_field() ) ) {
			if ( is_multisite() ) {
				$ip2ok_is_multisite = 'включен';
				$ip2ok_keeplogs = get_blog_option( get_current_blog_id(), 'ip2ok_keeplogs' );
			} else {
				$ip2ok_is_multisite = 'отключен';
				$ip2ok_keeplogs = get_option( 'ip2ok_keeplogs' );
			}
			$feed_id = '1'; // (string)
			$unixtime = current_time( 'Y-m-d H:i' );
			$mail_content = '<h1>Заявка (#' . $unixtime . ')</h1>';
			$mail_content .= "Версия плагина: " . IP2OK_PLUGIN_VERSION . "<br />";
			$mail_content .= "Версия WP: " . get_bloginfo( 'version' ) . "<br />";
			$woo_version = get_woo_version_number();
			$mail_content .= "Версия WC: " . $woo_version . "<br />";
			$mail_content .= "Версия PHP: " . phpversion() . "<br />";
			$mail_content .= "Режим мультисайта: " . $ip2ok_is_multisite . "<br />";
			$mail_content .= "Вести логи: " . $ip2ok_keeplogs . "<br />";
			$mail_content .= 'Расположение логов: <a href="' . IP2OK_SITE_UPLOADS_URL . '/import-products-to-ok-ru/plugins.log" target="_blank">' . IP2OK_SITE_UPLOADS_DIR_PATH . '/import-products-to-ok-ru/plugins.log</a><br />';
			$possible_problems_arr = IP2OK_Debug_Page::get_possible_problems_list();
			if ( $possible_problems_arr[1] > 0 ) {
				$possible_problems_arr[3] = str_replace( '<br/>', PHP_EOL, $possible_problems_arr[3] );
				$mail_content .= "Самодиагностика: " . PHP_EOL . $possible_problems_arr[3];
			} else {
				$mail_content .= "Самодиагностика: Функции самодиагностики не выявили потенциальных проблем" . "<br />";
			}
			if ( isset( $_POST[ $this->get_radio_name()] ) ) {
				$mail_content .= PHP_EOL . "Помог ли плагин: " . sanitize_text_field( $_POST[ $this->get_radio_name()] );
			}
			if ( isset( $_POST[ $this->get_input_name()] ) ) {
				$mail_content .= '<br />Почта: <a href="mailto:' . sanitize_email( $_POST[ $this->get_input_name()] ) . '?subject=Ответ разработчика Import products to OK.ru (#' . $unixtime . ')" target="_blank" rel="nofollow noreferer" title="' . sanitize_email( $_POST['ip2ok_email'] ) . '">' . sanitize_email( $_POST['ip2ok_email'] ) . '</a>';
			}
			if ( isset( $_POST[ $this->get_textarea_name()] ) ) {
				$mail_content .= "<br />Сообщение: " . sanitize_text_field( $_POST[ $this->get_textarea_name()] );
			}
			$argsp = [ 
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => -1
			];
			$products = new WP_Query( $argsp );
			$vsegotovarov = $products->found_posts;

			add_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
			wp_mail( 'support@icopydoc.ru', 'Отчёт Import products to OK.ru', $mail_content );
			// Сбросим content-type, чтобы избежать возможного конфликта
			remove_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
		}
	}

	public static function set_html_content_type() {
		return 'text/html';
	}
}