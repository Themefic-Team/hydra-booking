<?php
/**
 * Custom Archive Template for TFHB Meeting
 */

get_header(); ?>

<div class="tfhb-meeting-list">
        <div class="tfhb-meeting-list__heading">
        <h1 class="tfhb-meeting-archive-title">Meetings Archive</h1>
        </div>
   
    <div class="tfhb-meeting-list__wrap">
    <?php if (have_posts()) : ?> 
        <?php while (have_posts()) : the_post(); ?>
            <div class="tfhb-meeting-list__wrap__items">
                <div class="tfhb-meeting-list__wrap__items__content">
                    <a href="<?php the_permalink(); ?>" class="tfhb-meeting-link">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="tfhb-meeting-thumbnail">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="tfhb-meeting-title"><?php the_title(); ?></h3>
                    </a>

                    <div class="tfhb-meeting-meta">
                        <span><?php the_time('F j, Y'); ?></span> | <span><?php the_author(); ?></span>
                    </div>

                    <div class="tfhb-meeting-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </div>
                <!-- <div class="tfhb-meeting-list__wrap__items__tags"> 
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php echo esc_html($meeting->host_first_name) ?> <?php echo esc_html($meeting->host_last_name) ?>
                    </span> 
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tags"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/><path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a1 1 0 0 0-1 1v5.172a2 2 0 0 0 .586 1.414L8.29 18.29a2.426 2.426 0 0 0 3.42 0l3.58-3.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="6.5" cy="9.5" r=".5" fill="currentColor"/></svg>
                        <?php echo esc_html($terms->name) ?>    
                    </span> 
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>    
                        <?php echo esc_html($meeting->duration) ?> minutes
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation"><path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/></svg> 
                        <?php echo esc_html($meeting->meeting_type) ?>
                    </span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                        <?php echo esc_html($price) ?>
                    </span>
                </div> -->
                <!-- <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                    <a href="#" class="tfhb-btn boxed-btn">Book Now</a>
                </div> -->
            </div>
        <?php endwhile; ?> 

        <div class="tfhb-pagination">
            <?php the_posts_pagination(); ?>
        </div>

    <?php else : ?>
        <p>No meetings found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
