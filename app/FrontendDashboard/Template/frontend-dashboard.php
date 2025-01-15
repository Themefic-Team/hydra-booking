<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template: Hydra - Dashbaord
 *
 */
// Hide Admin Bar
add_filter( 'show_admin_bar', '__return_false' );
wp_head();

// get current page and page template 


 
?>
<div id="tfhb-admin-app"></div>
<?php
wp_footer(); 
