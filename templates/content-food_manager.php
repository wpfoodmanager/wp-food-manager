<?php
$food_type = get_food_type();
if (is_array($food_type) && isset($food_type[0]))
    $food_type = $food_type[0]->slug;

$thumbnail  = get_food_thumbnail();
?>

<div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo apply_filters('food_manager_food_wpfm_column', '4'); ?>">
    <!----- wpfm-col-lg-4 value can be change by admin settings ------->
    <div class="wpfm-food-layout-wrapper">
        <div <?php food_manager_class('wpfm-food-layout-wrapper'); ?>>
            <a href="<?php display_food_permalink(); ?>" class="wpfm-food-action-url food-style-color <?php echo $food_type; ?>">
                <div class="wpfm-food-banner">
                    <div class="wpfm-food-banner-img" style="background-image: url('<?php echo $thumbnail ?>')">

                        <!-- Hide in list View // Show in Box View -->
                        <?php do_action('food_already_registered_title'); ?>     
                        <div class="wpfm-food-date">
                          
                        </div>
                        <!-- Hide in list View // Show in Box View -->
                    </div>
                </div>

                <div class="wpfm-food-infomation">
                    <div class="wpfm-food-date">
                        <div class="wpfm-food-date-type">
                        </div>
                    </div>

                    <div class="wpfm-food-details">
                        <div class="wpfm-food-title"><h3 class="wpfm-heading-text"><?php echo esc_html(get_the_title()); ?></h3></div>

                       

                        <div class="wpfm-food-location">
                            <span class="wpfm-food-location-text">
                                <?php
                                if (get_food_location() == 'Order Online' || get_food_location() == ''): echo __('Order Online', 'wp-food-manager');
                                else: display_food_location(false);
                                endif;
                                ?>
                            </span>
                        </div>

                        <?php
                        if (get_option('food_manager_enable_food_types') && get_food_type())
                        {
                            ?>
                            <div class="wpfm-food-type"><?php display_food_type(); ?></div>
                        <?php } ?>

                        <?php do_action('food_already_registered_title'); ?>
                    </div>
                </div>   
            </a>
        </div>
    </div>
</div>