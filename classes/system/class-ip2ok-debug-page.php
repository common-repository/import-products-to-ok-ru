<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Debug Page
 *
 * @package			Import Products to OK.ru
 * @subpackage		
 * @since			0.1.0
 * 
 * @version			0.1.0 (02-03-2023)
 * @author			Maxim Glazunov
 * @link			https://icopydoc.ru/
 * @see				
 * 
 * @param
 *
 * @return			
 *
 * @depends			classes:	IP2OK_Feedback
 *								IP2OK_Get_Unit
 *					traits:	
 *					methods:	
 *					functions:	common_option_get
 *								
 *					constants:	IP2OK_PLUGIN_DIR_PATH
 *
 */

class IP2OK_Debug_Page {
	private $pref = 'ip2ok';
	private $feedback;

	public function __construct( $pref = null ) {
		if ( $pref ) {
			$this->pref = $pref;
		}
		$this->feedback = new IP2OK_Feedback();

		$this->listen_submit();
		$this->get_html_form();
	}

	public function get_html_form() { ?>
		<div class="wrap">
			<h1>
				<?php _e( 'Debug page', 'import-products-to-ok-ru' ); ?> v.
				<?php echo common_option_get( 'ip2ok_version' ); ?>
			</h1>
			<?php do_action( 'my_admin_notices' ); ?>
			<div id="dashboard-widgets-wrap">
				<div id="dashboard-widgets" class="metabox-holder">
					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_logs(); ?>
						</div>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_simulation(); ?>
						</div>
					</div>
					<div id="postbox-container-3" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_possible_problems(); ?>
							<?php $this->get_html_block_sandbox(); ?>
						</div>
					</div>
					<div id="postbox-container-4" class="postbox-container">
						<div class="meta-box-sortables">
							<?php do_action( 'ip2ok_before_support_project' ); ?>
							<?php $this->feedback->get_form(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php // end get_html_form();
	}

	public function get_html_block_logs() {
		$ip2ok_keeplogs = common_option_get( $this->get_input_name_keeplogs() );
		$ip2ok_disable_notices = common_option_get( $this->get_input_name_disable_notices() );
		$ip2ok_enable_five_min = common_option_get( $this->get_input_name_enable_five_min() ); ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Logs', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<p>
					<?php if ( $ip2ok_keeplogs === 'on' ) {
						$upload_dir = wp_get_upload_dir();
						echo '<strong>' . __( "Log-file here", 'import-products-to-ok-ru' ) . ':</strong><br /><a href="' . $upload_dir['baseurl'] . '/import-products-to-ok-ru/plugin.log" target="_blank">' . $upload_dir['basedir'] . '/import-products-to-ok-ru/plugin.log</a>';
					} ?>
				</p>
				<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="<?php echo $this->get_input_name_keeplogs(); ?>"><?php _e( 'Keep logs', 'import-products-to-ok-ru' ); ?></label><br />
									<input class="button" id="<?php echo $this->get_submit_name_clear_logs(); ?>" type="submit"
										name="<?php echo $this->get_submit_name_clear_logs(); ?>"
										value="<?php _e( 'Clear logs', 'import-products-to-ok-ru' ); ?>" />
								</th>
								<td class="overalldesc">
									<input type="checkbox" name="<?php echo $this->get_input_name_keeplogs(); ?>"
										id="<?php echo $this->get_input_name_keeplogs(); ?>" <?php checked( $ip2ok_keeplogs, 'on' ); ?> /><br />
									<span class="description">
										<?php _e( 'Do not check this box if you are not a developer', 'import-products-to-ok-ru' ); ?>!
									</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo $this->get_input_name_disable_notices(); ?>"><?php _e( 'Disable notices', 'import-products-to-ok-ru' ); ?></label></th>
								<td class="overalldesc">
									<input type="checkbox" name="<?php echo $this->get_input_name_disable_notices(); ?>"
										id="<?php echo $this->get_input_name_disable_notices(); ?>" <?php checked( $ip2ok_disable_notices, 'on' ); ?> /><br />
									<span class="description">
										<?php _e( 'Disable notices about the import of products', 'import-products-to-ok-ru' ); ?>!
									</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo $this->get_input_name_enable_five_min(); ?>"><?php _e( 'Enable', 'import-products-to-ok-ru' ); ?> five_min</label></th>
								<td class="overalldesc">
									<input type="checkbox" name="<?php echo $this->get_input_name_enable_five_min(); ?>"
										id="<?php echo $this->get_input_name_enable_five_min(); ?>" <?php checked( $ip2ok_enable_five_min, 'on' ); ?> /><br />
									<span class="description">
										<?php _e( 'Enable the five minute interval for CRON', 'import-products-to-ok-ru' ); ?>
									</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="button-primary"></label></th>
								<td class="overalldesc"></td>
							</tr>
							<tr>
								<th scope="row"><label for="button-primary"></label></th>
								<td class="overalldesc">
									<?php wp_nonce_field( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ); ?><input
										id="button-primary" class="button-primary" type="submit"
										name="<?php echo $this->get_submit_name(); ?>"
										value="<?php _e( 'Save', 'import-products-to-ok-ru' ); ?>" /><br />
									<span class="description">
										<?php _e( 'Click to save the settings', 'import-products-to-ok-ru' ); ?>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<?php
	} // end get_html_block_logs();

	public function get_html_block_simulation() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Request simulation', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<form action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<?php $resust_simulated = '';
					$resust_report = '';
					if ( isset( $_POST['ip2ok_num_feed'] ) ) {
						$ip2ok_num_feed = sanitize_text_field( $_POST['ip2ok_num_feed'] );
					} else {
						$ip2ok_num_feed = '1';
					}
					if ( isset( $_POST['ip2ok_simulated_post_id'] ) ) {
						$ip2ok_simulated_post_id = sanitize_text_field( $_POST['ip2ok_simulated_post_id'] );
					} else {
						$ip2ok_simulated_post_id = '';
					}
					if ( isset( $_POST['ip2ok_textarea_info'] ) ) {
						$ip2ok_textarea_info = sanitize_text_field( $_POST['ip2ok_textarea_info'] );
					} else {
						$ip2ok_textarea_info = '';
					}
					if ( isset( $_POST['ip2ok_textarea_res'] ) ) {
						$ip2ok_textarea_res = sanitize_text_field( $_POST['ip2ok_textarea_res'] );
					} else {
						$ip2ok_textarea_res = '';
					}
					if ( $ip2ok_textarea_res == 'calibration' ) {
						$resust_report .= ip2ok_calibration( $ip2ok_textarea_info );
					}
					if ( isset( $_REQUEST['ip2ok_submit_simulated'] ) ) {
						if ( ! empty( $_POST ) && check_admin_referer( 'ip2ok_nonce_action_simulated', 'ip2ok_nonce_field_simulated' ) ) {
							$product_id = (int) $ip2ok_simulated_post_id;
							$api = new IP2OK_RU_Api();
							$answer_arr = $api->product_sync( $product_id );
							if ( $answer_arr['status'] == true ) {
								$resust_report = 'Всё штатно';
								$resust_simulated = get_array_as_string( $answer_arr );
							} else {
								$resust_report = 'Есть ошибки';
								$resust_simulated = get_array_as_string( $answer_arr );
							}
						}
					} ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="ip2ok_simulated_post_id">postId</label></th>
								<td class="overalldesc">
									<input type="number" min="1" name="ip2ok_simulated_post_id"
										value="<?php echo $ip2ok_simulated_post_id; ?>">
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="ip2ok_enable_five_min">numFeed</label></th>
								<td class="overalldesc">
									<select style="width: 100%" name="ip2ok_num_feed" id="ip2ok_num_feed">
										<?php if ( is_multisite() ) {
											$cur_blog_id = get_current_blog_id();
										} else {
											$cur_blog_id = '0';
										}
										$ip2ok_settings_arr = common_option_get( 'ip2ok_settings_arr' );
										$ip2ok_settings_arr_keys_arr = array_keys( $ip2ok_settings_arr );
										for ( $i = 0; $i < count( $ip2ok_settings_arr_keys_arr ); $i++ ) :
											$numFeed = (string) $ip2ok_settings_arr_keys_arr[ $i ];
											if ( empty( $ip2ok_settings_arr[ $numFeed ]['ip2ok_feed_assignment'] ) ) {
												$feed_assignment = '';
											} else {
												$feed_assignment = ' (' . $ip2ok_settings_arr[ $numFeed ]['ip2ok_feed_assignment'] . ')';
											} ?>
											<option value="<?php echo $numFeed; ?>" <?php selected( $ip2ok_num_feed, $numFeed ); ?>>
												<?php _e( 'Feed', 'import-products-to-ok-ru' ); ?> 			<?php echo $numFeed; ?>:
												feed-yml-<?php echo $cur_blog_id; ?>.xml<?php echo $feed_assignment; ?></option>
										<?php endfor; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><textarea rows="4" name="ip2ok_textarea_info"
										style="width: 100%;"><?php echo htmlspecialchars( $resust_report ); ?></textarea></th>
							</tr>
							<tr>
								<th scope="row" colspan="2"><textarea rows="16" name="ip2ok_textarea_res"
										style="width: 100%;"><?php echo htmlspecialchars( $resust_simulated ); ?></textarea></th>
							</tr>
						</tbody>
					</table>
					<?php wp_nonce_field( 'ip2ok_nonce_action_simulated', 'ip2ok_nonce_field_simulated' ); ?><input
						class="button-primary" type="submit" name="ip2ok_submit_simulated"
						value="<?php _e( 'Simulated', 'import-products-to-ok-ru' ); ?>" />
				</form>
			</div>
		</div>
	<?php // end get_html_feeds_list();
	} // end get_html_block_simulation();

	public function get_html_block_possible_problems() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Possible problems', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<?php
				$possible_problems_arr = $this->get_possible_problems_list();
				if ( $possible_problems_arr[1] > 0 ) { // $possibleProblemsCount > 0) {
					echo '<ol>' . $possible_problems_arr[0] . '</ol>';
				} else {
					echo '<p>' . __( 'Self-diagnosis functions did not reveal potential problems', 'import-products-to-ok-ru' ) . '.</p>';
				}
				?>
			</div>
		</div>
		<?php
	} // end get_html_block_sandbox();

	public function get_html_block_sandbox() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php _e( 'Sandbox', 'import-products-to-ok-ru' ); ?>
			</h2>
			<div class="inside">
				<?php
				require_once IP2OK_PLUGIN_DIR_PATH . '/sandbox.php';
				try {
					ip2ok_run_sandbox();
				} catch (Exception $e) {
					echo 'Exception: ', $e->getMessage(), "\n";
				}
				?>
			</div>
		</div>
		<?php
	} // end get_html_block_sandbox();

	public static function get_possible_problems_list() {
		$possibleProblems = '';
		$possibleProblemsCount = 0;
		$conflictWithPlugins = 0;
		$conflictWithPluginsList = '';
		$check_global_attr_count = wc_get_attribute_taxonomies();
		if ( count( $check_global_attr_count ) < 1 ) {
			$possibleProblemsCount++;
			$possibleProblems .= '<li>' . __( 'Your site has no global attributes! This may affect the quality of the import to ok.ru. This can also cause difficulties when setting up the plugin', 'import-products-to-ok-ru' ) . '. <a href="https://icopydoc.ru/globalnyj-i-lokalnyj-atributy-v-woocommerce/?utm_source=import-products-to-ok-ru&utm_medium=organic&utm_campaign=in-plugin-import-products-to-ok-ru&utm_content=debug-page&utm_term=possible-problems">' . __( 'Please read the recommendations', 'import-products-to-ok-ru' ) . '</a>.</li>';
		}
		if ( is_plugin_active( 'snow-storm/snow-storm.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Snow Storm<br/>';
		}
		if ( is_plugin_active( 'ilab-media-tools/ilab-media-tools.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Media Cloud (Media Cloud for Amazon S3...)<br/>';
		}
		if ( is_plugin_active( 'email-subscribers/email-subscribers.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'saphali-search-castom-filds/saphali-search-castom-filds.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'W3 Total Cache<br/>';
		}
		if ( is_plugin_active( 'docket-cache/docket-cache.php' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Docket Cache<br/>';
		}
		if ( class_exists( 'MPSUM_Updates_Manager' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Easy Updates Manager<br/>';
		}
		if ( class_exists( 'OS_Disable_WordPress_Updates' ) ) {
			$possibleProblemsCount++;
			$conflictWithPlugins++;
			$conflictWithPluginsList .= 'Disable All WordPress Updates<br/>';
		}
		if ( $conflictWithPlugins > 0 ) {
			$possibleProblemsCount++;
			$possibleProblems .= '<li><p>' . __( 'Most likely, these plugins negatively affect the operation of', 'import-products-to-ok-ru' ) . ' Import Products to OK.ru:</p>' . $conflictWithPluginsList . '<p>' . __( 'If you are a developer of one of the plugins from the list above, please contact me', 'import-products-to-ok-ru' ) . ': <a href="mailto:support@icopydoc.ru">support@icopydoc.ru</a>.</p></li>';
		}
		return [ $possibleProblems, $possibleProblemsCount, $conflictWithPlugins, $conflictWithPluginsList ];
	}

	private function get_pref() {
		return $this->pref;
	}

	private function get_input_name_keeplogs() {
		return $this->get_pref() . '_keeplogs';
	}

	private function get_input_name_disable_notices() {
		return $this->get_pref() . '_disable_notices';
	}

	private function get_input_name_enable_five_min() {
		return $this->get_pref() . '_enable_five_min';
	}

	private function get_submit_name() {
		return $this->get_pref() . '_submit_debug_page';
	}

	private function get_nonce_action_debug_page() {
		return $this->get_pref() . '_nonce_action_debug_page';
	}

	private function get_nonce_field_debug_page() {
		return $this->get_pref() . '_nonce_field_debug_page';
	}

	private function get_submit_name_clear_logs() {
		return $this->get_pref() . '_submit_clear_logs';
	}

	private function listen_submit() {
		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			$this->save_data();
			$message = __( 'Updated', 'import-products-to-ok-ru' );
			$class = 'notice-success';

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		if ( isset( $_REQUEST[ $this->get_submit_name_clear_logs()] ) ) {
			$filename = IP2OK_PLUGIN_UPLOADS_DIR_PATH . '/plugin.log';
			if ( file_exists( $filename ) ) {
				$res = unlink( $filename );
			} else {
				$res = false;
			}
			if ( $res == true ) {
				$message = __( 'Logs were cleared', 'import-products-to-ok-ru' );
				$class = 'notice-success';
			} else {
				$message = __( 'Error accessing log file. The log file may have been deleted previously', 'import-products-to-ok-ru' );
				$class = 'notice-warning';
			}

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}
		return;
	}

	private function save_data() {
		if ( ! empty( $_POST ) && check_admin_referer( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ) ) {
			if ( isset( $_POST[ $this->get_input_name_keeplogs()] ) ) {
				$keeplogs = sanitize_text_field( $_POST[ $this->get_input_name_keeplogs()] );
			} else {
				$keeplogs = '';
			}
			if ( isset( $_POST[ $this->get_input_name_disable_notices()] ) ) {
				$disable_notices = sanitize_text_field( $_POST[ $this->get_input_name_disable_notices()] );
			} else {
				$disable_notices = '';
			}
			if ( isset( $_POST[ $this->get_input_name_enable_five_min()] ) ) {
				$enable_five_min = sanitize_text_field( $_POST[ $this->get_input_name_enable_five_min()] );
			} else {
				$enable_five_min = '';
			}
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'ip2ok_keeplogs', $keeplogs );
				update_blog_option( get_current_blog_id(), 'ip2ok_disable_notices', $disable_notices );
				update_blog_option( get_current_blog_id(), 'ip2ok_enable_five_min', $enable_five_min );
			} else {
				update_option( 'ip2ok_keeplogs', $keeplogs );
				update_option( 'ip2ok_disable_notices', $disable_notices );
				update_option( 'ip2ok_enable_five_min', $enable_five_min );
			}
		}
		return;
	}

	private function admin_notices_func( $message, $class ) {
		printf( '<div class="notice %1$s"><p>%2$s</p></div>', $class, $message );
	}
}