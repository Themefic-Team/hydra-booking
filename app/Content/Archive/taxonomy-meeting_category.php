 
<?php get_header(); ?>

<div class="tfhb-meeting-archive-container">
    <h1>Category: <?php single_cat_title(); ?></h1>

    <?php if (have_posts()) : ?>
        <div class="tfhb-meeting-list">
            <?php while (have_posts()) : the_post(); ?>
                <article class="tfhb-meeting-item">
                    <a href="<?php the_permalink(); ?>">
                        <h2><?php the_title(); ?></h2>
                    </a>
                    <p><?php the_excerpt(); ?></p>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>No meetings found in this category.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
