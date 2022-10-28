<?php
/**
 * The template for displaying archive.
 */

get_header();

global $wp_query;
?>
<div class="wpfm-container">
    <div class="wpfm-main wpfm-food-listing-tag-page">
        <div class="wpfm-row">
            <div class="wpfm-col-12 wpfm-food-listing-tag-page-wrapper">
                <div class="wpfm-my-5 wpfm-food-listing-tag-page-title">
                    <h1 class="wpfm-heading-text"><?php echo wp_kses_post(get_the_archive_title()); ?></h1>
                    <?php echo get_the_archive_description(); ?>
                </div>
                <div class="food_listings">
                    <?php if ( have_posts() ) : ?>

                        <?php get_food_manager_template( 'food-listings-start.php' ,array('layout_type'=>'all')); ?>           

                        <?php while ( have_posts() ) : the_post(); ?>

                            <?php  get_food_manager_template_part( 'content', 'food_manager' ); ?>
                            
                        <?php endwhile; ?>

                        <?php get_food_manager_template( 'food-listings-end.php' ); ?>

                        <?php get_food_manager_template( 'pagination.php', array( 'max_num_pages' => $wp_query->max_num_pages ) ); ?>

                    <?php else :
                        do_action( 'food_manager_output_foods_no_results' );
                    endif;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>