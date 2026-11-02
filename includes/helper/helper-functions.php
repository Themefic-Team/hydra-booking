<?php
if (! defined('ABSPATH')) {
	exit;
}

if (! function_exists('tfhb_print_r')) {
	function tfhb_print_r($data)
	{
		echo '<pre>';
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		print_r($data);
		echo '</pre>';
		// exit;
	}
}


function tfhb_character_limit_callback($str, $limit, $dots = true)
{
	$str = wp_strip_all_tags((string) $str);
	if ( mb_strlen( $str ) > $limit ) {
		$sub        = mb_substr( $str, 0, $limit );
		$last_space = mb_strrpos( $sub, ' ' );
		if ( false !== $last_space && $last_space > ( $limit * 0.4 ) ) {
			$sub = mb_substr( $sub, 0, $last_space );
		}
		$sub = rtrim( $sub, " \t\n\r\0\x0B.,;:-" );
		if ( true === $dots ) {
			return $sub . '...';
		} else {
			return $sub;
		}
	} else {
		return $str;
	}
}

/**
 * checked pro plugins is active or not
 *
 * @return string
 */

function tfhb_is_hydra_booking_pro_active()
{
	if (class_exists('TFHB_INIT_PRO')) {
		return true;
	} else {
		return false;
	}
} 

/*
 * Load Template
 * 
 * @param string $template_path
 * @param array $data
 * @return string
 */


/**
 * Safely load header template without triggering deprecated notices on block themes (FSE).
 *
 * @param string|null $name The name of the specialized header.
 */
if ( ! function_exists( 'tfhb_get_header' ) ) {
	function tfhb_get_header( $name = null ) {
		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "header-{$name}.php";
		}
		$templates[] = 'header.php';

		if ( locate_template( $templates ) ) {
			get_header( $name );
		} else {
			?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
			<?php
		}
	}
}

/**
 * Safely load footer template without triggering deprecated notices on block themes (FSE).
 *
 * @param string|null $name The name of the specialized footer.
 */
if ( ! function_exists( 'tfhb_get_footer' ) ) {
	function tfhb_get_footer( $name = null ) {
		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "footer-{$name}.php";
		}
		$templates[] = 'footer.php';

		if ( locate_template( $templates ) ) {
			get_footer( $name );
		} else {
			wp_footer();
			?>
</body>
</html>
			<?php
		}
	}
}
