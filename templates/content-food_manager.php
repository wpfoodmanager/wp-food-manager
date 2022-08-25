<?php
$food_type = get_food_type();
$food_price = get_post_meta(get_the_ID(), '_food_price', true);
if (is_array($food_type) && isset($food_type[0]))
    $food_type = $food_type[0]->slug;

$thumbnail  = get_food_banner();
?>

<div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo apply_filters('food_manager_food_wpfm_column', '4'); ?>">
    <!----- wpfm-col-lg-4 value can be change by admin settings ------->
    <div class="wpfm-food-layout-wrapper">
        <div <?php food_manager_class(); ?>>
            <a href="<?php display_food_permalink(); ?>" class="wpfm-food-action-url food-style-color <?php echo $food_type; ?>">
                <div class="wpfm-food-banner">
                    <div class="wpfm-food-banner-img" style="background-image: url('<?php echo $thumbnail ?>')">
                    </div>
                </div>

                <div class="wpfm-food-infomation">
                    <div class="wpfm-food-details">
                        <div class="wpfm-food-title"><h3 class="wpfm-heading-text"><?php echo esc_html(get_the_title()); ?> - <?php display_food_price_tag(); ?></h3></div>
                        <?php
                        if (get_option('food_manager_enable_food_types') && get_food_type())
                        {
                            ?>
                            <div class="wpfm-food-type"><?php display_food_type(); ?></div>
                        <?php } ?>
                    </div>
                </div>   
            </a>
        </div>
    </div>
</div>