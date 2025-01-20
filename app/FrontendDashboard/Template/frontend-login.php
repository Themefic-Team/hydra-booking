<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template: Hydra - Login
 *
 */
// Hide Admin Bar 
wp_head();

// get current page and page template 

echo do_shortcode('[hydra_login_form]');
 
wp_footer(); 
