<?php

/**
 * The template for displaying archive.
 */
get_header();
global $wp_query, $post;
$term = get_queried_object();
$image_id = !empty($term) ? get_term_meta(get_queried_object()->term_id, 'image_id', true) : '';
$image_url = wp_get_attachment_image_src($image_id, 'full');
?>
<div class="wpfm-container">
    <div class="wpfm-main wpfm-food-listing-type-page">
        <div class="wpfm-row">
            <div class="wpfm-col-12 wpfm-food-listing-type-page-wrapper">
                <?php if (!empty($image_url) && is_array($image_url)) { ?>
                    <div class="wpfm-with-bg-image-row">
                        <div class="wpfm-my-5 wpfm-food-listing-type-page-title wpfm-with-bg-image" style="background-image: url('<?php echo esc_url($image_url[0]); ?>'); margin-bottom: 0 !important;">
                            <h1 class="wpfm-heading-text"><?php echo wp_kses_post(get_the_archive_title()); ?></h1>
                        </div>
                        <?php echo wp_kses_post(get_the_archive_description()); ?>
                    </div>
                <?php } else { ?>
                    <div class="wpfm-my-5 wpfm-food-listing-type-page-title">
                        <h1 class="wpfm-heading-text"><?php echo wp_kses_post(get_the_archive_title()); ?></h1>
                        <?php echo wp_kses_post(get_the_archive_description()); ?>
                    </div>
                <?php } ?>
                <div class="food_listings">
                    <?php if (have_posts()) : ?>
                        <?php get_food_manager_template('food-listings-start.php', array('layout_type' => 'all')); ?>
                        <?php while (have_posts()) : the_post(); ?>
                            <?php get_food_manager_template_part('content', 'food_manager'); ?>
                        <?php endwhile; ?>
                        <?php get_food_manager_template('food-listings-end.php'); ?>
                        <?php get_food_manager_template('pagination.php', array('max_num_pages' => $wp_query->max_num_pages)); ?>
                    <?php else :
                        do_action('food_manager_output_foods_no_results');
                    endif;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>