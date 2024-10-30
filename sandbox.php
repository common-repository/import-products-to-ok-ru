<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function ip2ok_run_sandbox() {
	$x = false; // set to true to use the sandbox
	if ( true === $x ) {
		printf( '%s<br/>',
			__( 'The sandbox is working. The result will appear below', 'import-products-to-ok-ru' )
		);
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price();

		/* дальше не редактируем */
		printf( '<br/>%s',
			__( 'The sandbox is working correctly', 'import-products-to-ok-ru' )
		);
	}
}