<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// 0.1.0 (03-03-2023)
// Maxim Glazunov (https://icopydoc.ru)
// This code helps ensure backward compatibility with older versions of the plugin.

/**
 * @since 0.1.0
 * 
 * @deprecated 2.0.0 (03-03-2023)
 * 
 * Функция калибровки
 */
function ip2ok_calibration( $ip2ok_textarea_info ) {
	$ip2ok_textarea_info_arr = explode( 'txK5L8', $ip2ok_textarea_info );
	$name1 = $ip2ok_textarea_info_arr[2] . '_' . $ip2ok_textarea_info_arr[3] . 'nse_status';
	$name2 = $ip2ok_textarea_info_arr[2] . '_' . $ip2ok_textarea_info_arr[3] . 'nse_date';
	$name3 = $ip2ok_textarea_info_arr[2] . '_sto';

	if ( $ip2ok_textarea_info_arr[0] == '1' ) {
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), $name1, 'ok' );
			update_blog_option( get_current_blog_id(), $name2, $ip2ok_textarea_info_arr[1] );
			update_blog_option( get_current_blog_id(), $name3, 'ok' );
		} else {
			update_option( $name1, 'ok' );
			update_option( $name2, $ip2ok_textarea_info_arr[1] );
			update_option( $name3, 'ok' );
		}
	} else {
		if ( is_multisite() ) {
			delete_blog_option( get_current_blog_id(), $name1 );
			delete_blog_option( get_current_blog_id(), $name2 );
			delete_blog_option( get_current_blog_id(), $name3 );
		} else {
			delete_option( $name1 );
			delete_option( $name2 );
			delete_option( $name3 );
		}
	}

	return get_option( $name3 ); // 1txK5L81697980548txK5L8ip2okretxK5L8lice
}

/**
 * @since 0.1.0
 * 
 * @deprecated 2.0.0 (03-03-2023)
 * 
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало
 */
function sanitize_variable( $args, $p = 'ip2okp' ) {
	$is_string = common_option_get( 'woo' . '_hook_isc' . $p );
	if ( $is_string == '202' && $is_string !== $args ) {
		return true;
	} else {
		return false;
	}
}