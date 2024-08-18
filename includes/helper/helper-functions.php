<?php

if ( ! function_exists( 'tfhb_print_r' ) ) {
	function tfhb_print_r( $data ) {
		echo '<pre>';
		print_r( $data );
		echo '</pre>';
		exit;
	}
}


function tfhb_character_limit_callback( $str, $limit, $dots = true ) {
	if ( strlen( $str ) > $limit ) {
		if ( $dots == true ) {
			return substr( $str, 0, $limit ) . '...';
		} else {
			return substr( $str, 0, $limit );
		}
	} else {
		return $str;
	}
}