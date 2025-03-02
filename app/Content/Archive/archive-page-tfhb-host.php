<?php
/* Template Name: Host Profile */

get_header();

// Get the "username" query variable from the URL
$username = get_query_var('username');

// Fetch user data based on username (assuming you're using WP User Slug or similar)
$user = get_user_by('slug', $username);

if ($user) {
    echo '<h1>' . esc_html($user->display_name) . '</h1>';

    // Query the bookings associated with this host
    // Assuming bookings are stored as a custom post type or custom fields
    $args = array(
        'post_type' => 'booking', // Assuming bookings are a custom post type
        'posts_per_page' => -1, // Fetch all bookings
        'meta_query' => array(
            array(
                'key' => 'host_username', // Assuming the host username is saved in a custom field
                'value' => $user->user_login, // Match the username
                'compare' => '='
            )
        )
    );

    $bookings = new WP_Query($args);

    if ($bookings->have_posts()) :
        echo '<ul>';
        while ($bookings->have_posts()) : $bookings->the_post();
            echo '<li>';
            echo '<strong>' . get_the_title() . '</strong>'; // Booking title
            echo '<p>' . get_the_content() . '</p>'; // Booking details (you can customize this)
            echo '</li>';
        endwhile;
        echo '</ul>';
    else :
        echo '<p>No bookings found for this host.</p>';
    endif;
    wp_reset_postdata();
} else {
    echo '<p>User not found.</p>';
}

get_footer();
