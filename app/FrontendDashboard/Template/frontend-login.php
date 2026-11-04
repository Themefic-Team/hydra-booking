<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

/**
 * Template: Hydra - Login
 *
 */

tfhb_get_header();
while ( have_posts() ) :
	the_post();  
    echo do_shortcode('[hydra_login_form]');
	the_content();
endwhile;
tfhb_get_footer(); 
