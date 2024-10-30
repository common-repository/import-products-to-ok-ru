<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The class return the Settings page of the plugin Import products to OK.ru
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			1.0.0 (28-02-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 * 
 * @param	array	$args
 *
 * @return			
 *
 * @depends			classes:	IP2OK_Feedback
 * 								IP2OK_Error_Log 
 *					 			IP2OK_Data_Arr
 *					traits:	
 *					methods:	
 *					functions:	common_option_get
 *								common_option_upd
 *					constants:	IP2OK_PLUGIN_DIR_URL
 *					options:	
 *
 */

class IP2OK_Settings_Page {
	private $feed_id = '1';
	private $tab = 'main_tab';
	private $feedback;

	public function __construct( $slug ) {
		if ( isset( $_GET['feed_id'] ) ) {
			$this->feed_id = sanitize_text_field( $_GET['feed_id'] );
		}
		if ( isset( $_GET['tab'] ) ) {
			$this->tab = sanitize_text_field( $_GET['tab'] );
		}
		$this->feedback = new IP2OK_Feedback();

		$this->init_hooks(); // подключим хуки
		$this->listen_submit();

		$this->get_html_form();
	}

	public function get_html_form() { ?>
		<div class="wrap">
			<h1>Import products to OK.ru</h1>
			<div id="poststuff">
				<div id="post-body" class="columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_info_block(); ?>
							<?php do_action( 'ip2ok_before_support_project', $this->get_feed_id() ); ?>
							<?php $this->feedback->get_block_support_project(); ?>
							<?php do_action( 'ip2ok_between_container_1', $this->get_feed_id() ); ?>
							<?php $this->feedback->get_form(); ?>
							<?php do_action( 'ip2ok_append_container_1', $this->get_feed_id() ); ?>
						</div>
					</div><!-- /postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div class="meta-box-sortables">
							<?php echo $this->get_html_tabs( $this->get_tab() ); ?>

							<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
								enctype="multipart/form-data">
								<?php do_action( 'ip2ok_prepend_form_container_2' ); ?>
								<input type="hidden" name="ip2ok_feed_id_for_save" value="<?php echo $this->get_feed_id(); ?>">
								<?php switch ( $this->get_tab() ) :
									case 'main_tab': ?>
										<?php $this->get_html_main_tab(); ?>
										<?php break;
									case 'api_tab': ?>
										<?php $this->get_html_api_tab(); ?>
										<?php break;
									case 'instruction_tab': ?>
										<?php $this->get_html_instruction_tab(); ?>
										<?php break;
									default: ?>
										<?php do_action( 'ip2ok_switch_get_tab', [ 
											'feed_id' => $this->get_feed_id(),
											'tab' => $this->get_tab()
										]
										); ?>
									<?php endswitch; ?>

								<?php do_action( 'ip2ok_after_optional_elemet_block', $this->get_feed_id() ); ?>

								<?php switch ( $this->get_tab() ) :
									case 'instruction_tab':
										break;
									default: ?>
										<div class="postbox">
											<div class="inside">
												<table class="form-table">
													<tbody>
														<tr>
															<th scope="row"><label for="button-primary"></label></th>
															<td class="overalldesc">
																<?php wp_nonce_field( 'ip2ok_nonce_action', 'ip2ok_nonce_field' ); ?><input
																	id="button-primary" class="button-primary" type="submit"
																	name="ip2ok_submit_action" value="<?php
																	if ( $this->get_tab() === 'main_tab' ) {
																		echo __( 'Save', 'import-products-to-ok-ru' ) . ' & ' . __( 'Run Import', 'import-products-to-ok-ru' );
																	} else {
																		_e( 'Save', 'import-products-to-ok-ru' );
																	}
																	?>" /><br />
																<span class="description"><small>
																		<?php _e( 'Click to save the settings', 'import-products-to-ok-ru' ); ?>
																	</small></span>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									<?php endswitch; ?>
							</form>
						</div>
					</div><!-- /postbox-container-2 -->

				</div>
			</div><!-- /poststuff -->
			<?php $this->get_html_icp_banners(); ?>
			<?php $this->get_html_my_plugins_list(); ?>
		</div>
	<?php // end get_html_form();
	}

	public function get_html_info_block() {
		return;
	} // end get_html_info_block();

	public function get_html_tabs( $current = 'main_tab' ) {
		$tabs_arr = [ 
			'main_tab' => __( 'Main settings', 'import-products-to-ok-ru' ),
			'api_tab' => __( 'API Settings', 'import-products-to-ok-ru' ),
			'instruction_tab' => __( 'Instruction', 'import-products-to-ok-ru' )
		];
		$tabs_arr = apply_filters( 'ip2ok_f_tabs_arr', $tabs_arr );

		$html = '<div class="nav-tab-wrapper" style="margin-bottom: 10px;">';
		foreach ( $tabs_arr as $tab => $name ) {
			if ( $tab === $current ) {
				$class = ' nav-tab-active';
			} else {
				$class = '';
			}
			if ( isset( $_GET['feed_id'] ) ) {
				$nf = '&feed_id=' . sanitize_text_field( $_GET['feed_id'] );
			} else {
				$nf = '&feed_id=1';
			}
			$html .= sprintf( '<a class="nav-tab%1$s" href="?page=%2$s&tab=%3$s%4$s">%5$s</a>',
				$class,
				'ip2ok-import',
				$tab,
				$nf,
				$name
			);
		}
		$html .= '</div>';

		return $html;
	} // end get_html_tabs();

	public function get_html_main_tab() {
		$res_html = $this->get_html_fields( 'main_tab' );
		?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Main settings', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<table class="form-table" role="presentation">
					<tbody>
						<?php echo $res_html; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	} // end get_html_main_tab();

	public function get_html_api_tab() {
		$res_html = $this->get_html_fields( 'api_tab' );
		?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'API Settings', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<?php
				$token = common_option_get( 'access_token', false, $this->get_feed_id(), 'ip2ok' );
				if ( empty( $token ) ) {
					printf( '<p><span style="color: red;">%1$s</span></p>',
						__( 'You need to get a token', 'import-products-to-ok-ru' )
					);
				}
				$params = [ 
					'client_id' => common_option_get( 'application_id', false, $this->get_feed_id(), 'ip2ok' ),
					'scope' => 'VALUABLE_ACCESS;LONG_ACCESS_TOKEN;PHOTO_CONTENT;GROUP_CONTENT',
					'response_type' => 'token',
					'redirect_uri' => get_site_url( null, '/wp-admin/admin.php?page=ip2ok-import&tab=api_tab&feed_id=1' )
				];

				$url = 'https://connect.ok.ru/oauth/authorize?' . urldecode( http_build_query( $params ) );

				printf( '<p>%1$s: <a href="%2$s">%3$s</a>. %4$s (<a href="%5$s" target="_blank">%6$s</a>)</p>',
					__( 'Fill in the "Application ID", "Client secret", save them, and then get a token by clicking on this link', 'import-products-to-ok-ru' ),
					esc_attr( $url ),
					__( 'Authorization via OK.ru', 'import-products-to-ok-ru' ),
					__( 'Be sure to click "allow". You will then be redirected back', 'import-products-to-ok-ru' ),
					'https://ok.ru/settings/oauth',
					__( 'You can delete a previously issued token here', 'import-products-to-ok-ru' )
				);
				?>
				<table class="form-table" role="presentation">
					<tbody>
						<?php echo $res_html; ?>
						<tr class="ip2ok_tr">
							<th scope="row"><label for="redirect_uri">Redirect URI</label></th>
							<td class="overalldesc">
								<input type="text" name="redirect_uri" id="redirect_uri"
									value="<?php echo get_site_url( null, '/wp-admin/admin.php?page=ip2ok-import&tab=api_tab&feed_id=1' ); ?>"
									disabled><br>
								<span class="description"><small><strong>redirect_uri</strong> -
										<?php _e( 'Copy this address and specify it on the "Settings - API Settings" page in the Yandex Market personal account', 'import-products-to-ok-ru' ); ?>
									</small></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	} // end get_html_api_tab();

	public function get_html_instruction_tab() {
		?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Instruction', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<p><i>(
						<?php _e( 'The full version of the instruction can be found', 'import-products-to-ok-ru' ); ?> <a
							href="https://icopydoc.ru/nastrojka-plagina-import-products-to-ok-ru/?utm_source=import-products-to-ok-ru&utm_medium=organic&utm_campaign=in-plugin-import-products-to-ok-ru&utm_content=api-set-page&utm_term=main-instruction"
							target="_blank">
							<?php _e( 'here', 'import-products-to-ok-ru' ); ?>
						</a>)
					</i></p>
				<p>
					<?php _e( 'To access ok.ru API you need', 'import-products-to-ok-ru' ); ?> <a href="//apiok.ru/dev/app/create"
						target="_blank">
						<?php _e( 'Create application', 'import-products-to-ok-ru' ); ?>
					</a>
					<?php _e( 'and get rights for him VALUABLE_ACCESS and GROUP_CONTENT', 'import-products-to-ok-ru' ); ?>
				</p>
				<p>
					<?php _e( 'For this', 'import-products-to-ok-ru' ); ?>:
				</p>
				<ol>
					<li>
						<?php _e( 'Get developer rights', 'import-products-to-ok-ru' ); ?> <a href="//ok.ru/devaccess"
							target="_blank">
							<?php _e( 'on this page', 'import-products-to-ok-ru' ); ?>
						</a>
					</li>
					<li>
						<?php _e( 'Follow the link and click', 'import-products-to-ok-ru' ); ?> "<a
							href="//ok.ru/vitrine/myuploaded" target="_blank">
							<?php _e( 'Add application', 'import-products-to-ok-ru' ); ?>
						</a>"
					</li>
					<li>
						<?php _e( 'Application type ', 'import-products-to-ok-ru' ); ?> - "
						<?php _e( 'Game', 'import-products-to-ok-ru' ); ?>"
					</li>
					<li>
						<?php _e( 'Click to', 'import-products-to-ok-ru' ); ?> "
						<?php _e( 'Enable OAuth', 'import-products-to-ok-ru' ); ?>"
					</li>
					<li>
						<?php _e( 'Allowed list', 'import-products-to-ok-ru' ); ?> "
						<?php _e( 'Enable OAuth', 'import-products-to-ok-ru' ); ?>"
					</li>
					<li>
						<?php _e( 'Click to', 'import-products-to-ok-ru' ); ?> "
						<?php _e( 'Enable OAuth', 'import-products-to-ok-ru' ); ?>"
					</li>
					<li>
						<?php _e( 'Allowed list', 'import-products-to-ok-ru' ); ?> redirect_url -
						<code><?php echo get_site_url( null, '/wp-admin/admin.php?page=ip2ok-import&tab=api_tab&feed_id=1' ); ?></code>
					</li>
					<li>PHOTO_CONTENT -
						<?php _e( 'set point "Required', 'import-products-to-ok-ru' ); ?>"
					</li>
				</ol>
				<p><img style="max-width: 100%;" src="<?php echo IP2OK_PLUGIN_DIR_URL; ?>screenshot-3.png"
						alt="screenshot-3.png" /></p>
				<ol>
					<li value="9">
						<?php _e( 'You will receive an email. Copy from it', 'import-products-to-ok-ru' ); ?> Application ID
					</li>
					<li>
						<?php _e( 'Send an email api-support@ok.ru asking for rights', 'import-products-to-ok-ru' ); ?>
						VALUABLE_ACCESS
						<?php _e( 'and', 'import-products-to-ok-ru' ); ?> GROUP_CONTENT.
						<?php _e( 'Be sure to include your', 'import-products-to-ok-ru' ); ?> Application ID
					</li>
					<li>
						<?php _e( 'After 1-3 days, when the support service answers, go to your application and set point required opposite', 'import-products-to-ok-ru' ); ?>
						VALUABLE_ACCESS
						<?php _e( 'and', 'import-products-to-ok-ru' ); ?> GROUP_CONTENT
					</li>
				</ol>
				<p><img style="max-width: 100%;" src="<?php echo IP2OK_PLUGIN_DIR_URL; ?>screenshot-4.png"
						alt="screenshot-4.png" /></p>
			</div>
		</div>
		<?php
	} // end get_html_instruction_tab();	

	public function get_html_icp_banners() { ?>
		<div id="icp_slides" class="clear">
			<div class="icp_wrap">
				<input type="radio" name="icp_slides" id="icp_point1">
				<input type="radio" name="icp_slides" id="icp_point2">
				<input type="radio" name="icp_slides" id="icp_point3">
				<input type="radio" name="icp_slides" id="icp_point4">
				<input type="radio" name="icp_slides" id="icp_point5" checked>
				<input type="radio" name="icp_slides" id="icp_point6">
				<input type="radio" name="icp_slides" id="icp_point7">
				<div class="icp_slider">
					<div class="icp_slides icp_img1"><a href="//wordpress.org/plugins/yml-for-yandex-market/"
							target="_blank"></a></div>
					<div class="icp_slides icp_img2"><a href="//wordpress.org/plugins/import-products-to-ok-ru/"
							target="_blank"></a></div>
					<div class="icp_slides icp_img3"><a href="//wordpress.org/plugins/xml-for-google-merchant-center/"
							target="_blank"></a></div>
					<div class="icp_slides icp_img4"><a href="//wordpress.org/plugins/gift-upon-purchase-for-woocommerce/"
							target="_blank"></a></div>
					<div class="icp_slides icp_img5"><a href="//wordpress.org/plugins/xml-for-avito/" target="_blank"></a></div>
					<div class="icp_slides icp_img6"><a href="//wordpress.org/plugins/xml-for-o-yandex/" target="_blank"></a>
					</div>
					<div class="icp_slides icp_img7"><a href="//wordpress.org/plugins/import-from-yml/" target="_blank"></a>
					</div>
				</div>
				<div class="icp_control">
					<label for="icp_point1"></label>
					<label for="icp_point2"></label>
					<label for="icp_point3"></label>
					<label for="icp_point4"></label>
					<label for="icp_point5"></label>
					<label for="icp_point6"></label>
					<label for="icp_point7"></label>
				</div>
			</div>
		</div>
	<?php
	} // end get_html_icp_banners()

	public function get_html_my_plugins_list() { ?>
		<div class="metabox-holder">
			<div class="postbox">
				<h2 class="hndle">
					<?php _e( 'My plugins that may interest you', 'import-products-to-ok-ru' ); ?>
				</h2>
				<div class="inside">
					<p><span class="ip2ok_bold">XML for Google Merchant Center</span> -
						<?php _e( 'Сreates a XML-feed to upload to Google Merchant Center', 'import-products-to-ok-ru' ); ?>. <a
							href="https://wordpress.org/plugins/xml-for-google-merchant-center/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">YML for Yandex Market</span> -
						<?php _e( 'Сreates a YML-feed for importing your products to Yandex Market', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/yml-for-yandex-market/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">Import from YML</span> -
						<?php _e( 'Imports products from YML to your shop', 'import-products-to-ok-ru' ); ?>. <a
							href="https://wordpress.org/plugins/import-from-yml/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">Integrate myTarget for WooCommerce</span> -
						<?php _e( 'This plugin helps setting up myTarget counter for dynamic remarketing for WooCommerce', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/wc-mytarget/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">XML for Hotline</span> -
						<?php _e( 'Сreates a XML-feed for importing your products to Hotline', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/xml-for-hotline/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">Gift upon purchase for WooCommerce</span> -
						<?php _e( 'This plugin will add a marketing tool that will allow you to give gifts to the buyer upon purchase', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/gift-upon-purchase-for-woocommerce/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">Import products to OK.ru</span> -
						<?php _e( 'With this plugin, you can import products to your group on ok.ru', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/import-products-to-ok-ru/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">Import Products to VK</span> -
						<?php _e( 'With this plugin, you can import products to your group on VK.com', 'import-products-to-ok-ru' ); ?>.
						<a href="https://wordpress.org/plugins/import-products-to-vk/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">XML for Avito</span> -
						<?php _e( 'Сreates a XML-feed for importing your products to', 'import-products-to-ok-ru' ); ?> Avito. <a
							href="https://wordpress.org/plugins/xml-for-avito/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
					<p><span class="ip2ok_bold">XML for O.Yandex (Яндекс Объявления)</span> -
						<?php _e( 'Сreates a XML-feed for importing your products to', 'import-products-to-ok-ru' ); ?>
						Яндекс.Объявления. <a href="https://wordpress.org/plugins/xml-for-o-yandex/" target="_blank">
							<?php _e( 'Read more', 'import-products-to-ok-ru' ); ?>
						</a>.
					</p>
				</div>
			</div>
		</div>
		<?php
	} // end get_html_my_plugins_list()

	public function admin_head_css_func() {
		/* печатаем css в шапке админки */
		print '<style>/* Import products to OK.ru */
			.metabox-holder .postbox-container .empty-container {height: auto !important;}
			.icp_img1 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl1.jpg);}
			.icp_img2 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl2.jpg);}
			.icp_img3 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl3.jpg);}
			.icp_img4 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl4.jpg);}
			.icp_img5 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl5.jpg);}
			.icp_img6 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl6.jpg);}
			.icp_img7 {background-image: url(' . IP2OK_PLUGIN_DIR_URL . 'img/sl7.jpg);}
		</style>';
	}

	private function init_hooks() {
		// наш класс, вероятно, вызывается во время срабатывания хука admin_menu.
		// admin_init - следующий в очереди срабатывания, хуки раньше admin_menu нет смысла вешать
		// add_action('admin_init', array($this, 'listen_submits'), 10);
		add_action( 'admin_print_footer_scripts', [ $this, 'admin_head_css_func' ] );
	}

	private function get_html_fields( $tab, $res_html = '' ) {
		$ip2ok_data_arr_obj = new IP2OK_Data_Arr();
		$data_for_tab_arr = $ip2ok_data_arr_obj->get_data_for_tabs( $tab ); // список дефолтных настроек

		for ( $i = 0; $i < count( $data_for_tab_arr ); $i++ ) {
			switch ( $data_for_tab_arr[ $i ]['type'] ) {
				case 'text':
					$res_html .= $this->get_field_input( $data_for_tab_arr[ $i ] );
					break;
				case 'number':
					$res_html .= $this->get_field_number( $data_for_tab_arr[ $i ] );
					break;
				case 'select':
					$res_html .= $this->get_field_select( $data_for_tab_arr[ $i ] );
					break;
				default:
					$res_html = apply_filters( 'ip2ok_f_get_html_fields', $res_html, $data_for_tab_arr[ $i ] );
			}
		}

		return $res_html;
	}

	private function get_field_input( $data_arr ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		return sprintf( '<tr class="%1$s">
							<th scope="row"><label for="%2$s">%3$s</label></th>
							<td class="overalldesc">
								<input 
									type="text" 
									name="%2$s" 
									id="%2$s" 
									value="%4$s"
									placeholder="%5$s" /><br />
								<span class="description"><small>%6$s</small></span>
							</td>
						</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			esc_html( $data_arr['label'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $this->get_feed_id(), 'ip2ok' ) ),
			esc_html( $data_arr['placeholder'] ),
			$data_arr['desc']
		);
	}

	private function get_field_number( $data_arr ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['min'] ) ) {
			$min = $data_arr['min'];
		} else {
			$min = '';
		}
		if ( isset( $data_arr['max'] ) ) {
			$max = $data_arr['max'];
		} else {
			$max = '';
		}
		if ( isset( $data_arr['step'] ) ) {
			$step = $data_arr['step'];
		} else {
			$step = '';
		}

		return sprintf( '<tr class="%1$s">
							<th scope="row"><label for="%2$s">%3$s</label></th>
							<td class="overalldesc">
								<input 
									type="number" 
									name="%2$s" 
									id="%2$s" 
									value="%4$s"
									placeholder="%5$s" 
									min="%6$s"
									max="%7$s"
									step="%8$s"
									/><br />
								<span class="description"><small>%9$s</small></span>
							</td>
						</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			esc_html( $data_arr['label'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $this->get_feed_id(), 'ip2ok' ) ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( $min ),
			esc_attr( $max ),
			esc_attr( $step ),
			$data_arr['desc']
		);
	}

	private function get_field_select( $data_arr ) {
		if ( isset( $data_arr['key_value_arr'] ) ) {
			$key_value_arr = $data_arr['key_value_arr'];
		} else {
			$key_value_arr = [];
		}
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		return sprintf( '<tr class="%1$s">
						<th scope="row"><label for="%2$s">%3$s</label></th>
						<td class="overalldesc">
							<select name="%2$s" id="%2$s" />%4$s</select><br />
							<span class="description"><small>%5$s</small></span>
						</td>
					</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			esc_html( $data_arr['label'] ),
			$this->get_option_for_select_html( common_option_get( $data_arr['opt_name'], false, $this->get_feed_id(), 'ip2ok' ), false, [ 'woo_attr' => $data_arr['woo_attr'], 'key_value_arr' => $key_value_arr ] ),
			$data_arr['desc']
		);
	}

	private function get_option_for_select_html( $opt_value, $opt_name = false, $params_arr = [], $res = '' ) {
		if ( ! empty( $params_arr['key_value_arr'] ) ) {
			for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
				$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
					esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
					esc_attr( selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false ) ),
					esc_attr( $params_arr['key_value_arr'][ $i ]['text'] )
				);
			}
		}

		if ( ! empty( $params_arr['woo_attr'] ) ) {
			$woo_attributes_arr = get_woo_attributes();
			for ( $i = 0; $i < count( $woo_attributes_arr ); $i++ ) {
				$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
					esc_attr( $woo_attributes_arr[ $i ]['id'] ),
					esc_attr( selected( $opt_value, $woo_attributes_arr[ $i ]['id'], false ) ),
					esc_attr( $woo_attributes_arr[ $i ]['name'] )
				);
			}
			unset( $woo_attributes_arr );
		}
		return $res;
	}

	private function get_feed_id() {
		return $this->feed_id;
	}

	private function get_tab() {
		return $this->tab;
	}

	private function save_plugin_set( $opt_name, $feed_id = '1', $save_if_empty = false ) {
		if ( isset( $_POST[ $opt_name ] ) ) {
			$value = sanitize_text_field( $_POST[ $opt_name ] );
			common_option_upd( $opt_name, $value, 'no', $feed_id, 'ip2ok' );
		} else {
			if ( $save_if_empty === true ) {
				common_option_upd( $opt_name, '', 'no', $feed_id, 'ip2ok' );
			}
		}
		return;
	}

	private function listen_submit() {
		if ( isset( $_REQUEST['ip2ok_submit_action'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'ip2ok_nonce_action', 'ip2ok_nonce_field' ) ) {
				do_action( 'ip2ok_prepend_submit_action', $this->get_feed_id() );
				$feed_id = sanitize_text_field( $_POST['ip2ok_feed_id_for_save'] );

				$unixtime = current_time( 'timestamp', 1 ); // 1335808087 - временная зона GMT (Unix формат)
				common_option_upd( 'date_save_set', $unixtime, 'no', $feed_id, 'ip2ok' );

				if ( isset( $_POST['run_cron'] ) ) {
					$arr_maybe = [ 'disabled', 'five_min', 'hourly', 'six_hours', 'twicedaily', 'daily', 'week' ];
					$run_cron = sanitize_text_field( $_POST['run_cron'] );

					if ( in_array( $run_cron, $arr_maybe ) ) {
						common_option_upd( 'date_save_set', $run_cron, 'no', $feed_id, 'ip2ok' );

						if ( $run_cron === 'disabled' ) {
							// отключаем крон
							wp_clear_scheduled_hook( 'ip2ok_cron_period', [ $feed_id ] );
							common_option_upd( 'status_cron', 'disabled', 'no', $feed_id, 'ip2ok' );

							wp_clear_scheduled_hook( 'cron_sborki', [ $feed_id ] );
							common_option_upd( 'status_sborki', '-1', 'no', $feed_id, 'ip2ok' );
						} else {
							wp_clear_scheduled_hook( 'ip2ok_cron_period', [ $feed_id ] );
							wp_schedule_event( time(), $run_cron, 'ip2ok_cron_period', [ $feed_id ] );
							new IP2OK_Error_Log( 'FEED № ' . $feed_id . '; ip2ok_cron_period внесен в список заданий; Файл: class-ip2ok-settings-page.php; Строка: ' . __LINE__ );
						}
					} else {
						new IP2OK_Error_Log( 'Крон-интервал ' . $run_cron . ' не зарегистрирован. Файл: class-ip2ok-settings-page.php; Строка: ' . __LINE__ );
					}
				}

				$def_plugin_date_arr = new IP2OK_Data_Arr();
				$opts_name_and_def_date_arr = $def_plugin_date_arr->get_opts_name_and_def_date( 'public' );
				foreach ( $opts_name_and_def_date_arr as $opt_name => $value ) {
					$save_if_empty = false;
					$this->save_plugin_set( $opt_name, $feed_id, $save_if_empty );
				}
				do_action( 'ip2ok_settings_page_listen_submit', $feed_id );
				$this->feed_id = $feed_id;
			}
		}

		return;
	}
}