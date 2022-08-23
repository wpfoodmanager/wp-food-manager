<?php
global $post;
?>
<div class="single_food_listing">
    <div class="wpfm-main wpfm-single-food-page">
        <div class="wpfm-single-food-wrapper">
            <div class="wpfm-single-food-body">
                <div class="wpfm-row">
                    <div class="wpfm-col-xs-12 wpfm-col-sm-12 wpfm-col-md-12 wpfm-single-food-left-content">
                        <div class="wpfm-single-food-short-info">
                            <div class="wpfm-food-details">
                                <div class="wpfm-food-title">
                                    <h3 class="wpfm-heading-text"><?php the_title(); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="wpfm-single-food-body-content">
                            <?php the_content(); ?>
                            
                            <h3>
                                <?php _e('Food Lists');?>
                            </h3>
                            <?php
                            $po_ids = get_post_meta($post->ID, '_food_item_ids', true);
                            if(!empty($po_ids)){
                                $food_listings = get_posts( array(
                                    'include'   => implode(",", $po_ids),
                                    'post_type' => 'food_manager',
                                    'orderby'   => 'post__in',
                                ) );
                                
                                foreach ($food_listings as $food_listing) {    
                                    echo wp_kses_post("<a href='".get_permalink($food_listing->ID)."' class='food-list-box'>".esc_html($food_listing->post_title)."</a>");
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>