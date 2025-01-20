<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template: Hydra - Registration
 *
 */
// Hide Admin Bar 
wp_head();

// get current page and page template 

echo do_shortcode('[hydra_registration_form]');
 
wp_footer(); 
