<?php
global $post;
$food_type = get_food_type();
$food_price = get_post_meta(get_the_ID(), '_food_price', true);
if (is_array($food_type) && isset($food_type[0]))
    $food_type = $food_type[0]->slug;
$food_thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
if (isset($food_thumbnail) && empty($food_thumbnail)) {
    $food_thumbnail = apply_filters('wpfm_default_food_banner', esc_url(WPFM_PLUGIN_URL . '/assets/images/wpfm-placeholder.jpg'));
}
if (get_option('food_manager_food_item_show_hide') == 0 && get_stock_status() !== 'food_outofstock') { ?>
    <div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo esc_attr(apply_filters('food_manager_food_wpfm_column', '4')); ?>">
        <!----- wpfm-col-lg-4 value can be change by admin settings ------->
        <div class="wpfm-food-layout-wrapper">
            <div <?php food_manager_class(); ?>>
                <a href="<?php display_food_permalink(); ?>" class="wpfm-food-action-url food-style-color <?php echo esc_attr($food_type); ?>">
                    <div class="wpfm-food-banner">
                        <div class="wpfm-food-banner-img" style="background-image: url('<?php echo esc_url($food_thumbnail) ?>')">
                        </div>
                    </div>
                    <div class="wpfm-food-infomation">
                        <div class="wpfm-food-details">
                            <div class="wpfm-food-title">
                                <h3 class="wpfm-heading-text"><?php echo esc_html(get_the_title()); ?> <?php display_food_veg_nonveg_icon_tag(); ?></h3>
                            </div>
                            <?php do_action('food_list_overview_after', get_the_ID()); ?>
                            <div class="wpfm-food-price"><?php display_food_price_tag(); ?></div>
                            
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
<?php
} elseif (get_option('food_manager_food_item_show_hide') == 1 && get_stock_status()) { ?>
    <div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo esc_attr(apply_filters('food_manager_food_wpfm_column', '4')); ?>">
        <!----- wpfm-col-lg-4 value can be change by admin settings ------->
        <div class="wpfm-food-layout-wrapper">
            <div <?php food_manager_class(''); ?>>
                <a href="<?php display_food_permalink(); ?>" class="wpfm-food-action-url food-style-color <?php echo esc_attr($food_type); ?>"> 
                    <div class="wpfm-food-banner">
                        <div class="wpfm-food-banner-img" style="background-image: url('<?php echo esc_url($food_thumbnail) ?>')"></div>
                    </div>
                </a>
                <div class="wpfm-food-infomation">
                    <div class="wpfm-food-details">
                        <a href="<?php display_food_permalink(); ?>"
                            class="wpfm-food-action-url food-style-color <?php echo esc_attr($food_type); ?>">
                            <div class="wpfm-food-title">
                                <?php do_action('food_list_title_after', get_the_ID()); ?>
                                <h3 class="wpfm-heading-text">
                                    <?php
                                    $out = strlen(get_the_title()) > 50 ? substr(get_the_title(), 0, 50) . "..." : get_the_title();
                                    echo esc_html($out); ?>
                                    <?php display_food_veg_nonveg_icon_tag(); ?>
                                </h3>
                            </div>
                        </a>
                        <div class="wpfm-food-pricing-box">
                            <div class="wpfm-food-price">
                                <?php display_food_price_tag(); ?>
                            </div>
                            <?php do_action('food_list_overview_after', get_the_ID()); ?>
                        </div>
                        <?php if (get_stock_status() == 'food_outofstock') { ?>
                            <div class="food-stock-status">
                                <?php display_stock_status(); ?>
                            </div>
                        <?php } ?> 
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
}
do_action('food_list_overview_end');