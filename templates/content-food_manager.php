<?php
$start_date = get_food_start_date();
$start_time = get_food_start_time();
$end_date   = get_food_end_date();
$end_time   = get_food_end_time();
$food_type = get_food_type();
if (is_array($food_type) && isset($food_type[0]))
    $food_type = $food_type[0]->slug;

$thumbnail  = get_food_thumbnail();
?>

<div class="wpfm-food-box-col wpfm-col wpfm-col-12 wpfm-col-md-6 wpfm-col-lg-<?php echo apply_filters('food_manager_food_wpfm_column', '4'); ?>">
    <!----- wpfm-col-lg-4 value can be change by admin settings ------->
    <div class="wpfm-food-layout-wrapper">
        <div <?php food_listing_class('wpfm-food-layout-wrapper'); ?>>
            <a href="<?php display_food_permalink(); ?>" class="wpfm-food-action-url food-style-color <?php echo $food_type; ?>">
                <div class="wpfm-food-banner">
                    <div class="wpfm-food-banner-img" style="background-image: url('<?php echo $thumbnail ?>')">

                        <!-- Hide in list View // Show in Box View -->
                        <?php do_action('food_already_registered_title'); ?>     
                        <div class="wpfm-food-date">
                            <div class="wpfm-food-date-type">
                                <?php
                                if (!empty($start_date))
                                {
                                    ?>
                                    <div class="wpfm-from-date">
                                        <div class="wpfm-date"><?php echo date_i18n('d', strtotime($start_date)); ?></div>
                                        <div class="wpfm-month"><?php echo date_i18n('M', strtotime($start_date)); ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- Hide in list View // Show in Box View -->
                    </div>
                </div>

                <div class="wpfm-food-infomation">
                    <div class="wpfm-food-date">
                        <div class="wpfm-food-date-type">
                            <?php
                            if (!empty($start_date))
                            {
                                ?>
                                <div class="wpfm-from-date">
                                    <div class="wpfm-date"><?php echo date_i18n('d', strtotime($start_date)); ?></div>
                                    <div class="wpfm-month"><?php echo date_i18n('M', strtotime($start_date)); ?></div>
                                </div>
                            <?php } ?>

                            <?php
                            if ($start_date != $end_date && !empty($end_date))
                            {
                                ?>
                                <div class="wpfm-to-date">
                                    <div class="wpfm-date-separator">-</div>
                                    <div class="wpfm-date"><?php echo date_i18n('d', strtotime($end_date)); ?></div>
                                    <div class="wpfm-month"><?php echo date_i18n('M', strtotime($end_date)); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="wpfm-food-details">
                        <div class="wpfm-food-title"><h3 class="wpfm-heading-text"><?php echo esc_html(get_the_title()); ?></h3></div>

                        <div class="wpfm-food-date-time">
                            <span class="wpfm-food-date-time-text">
                                <?php display_food_start_date(); ?> 
                                <?php
                                if (!empty($start_time))
                                {
                                    display_date_time_separator();
                                }
                                ?>
                                <?php display_food_start_time(); ?>
                                <?php
                                if (!empty($end_date) || !empty($end_time))
                                {
                                    ?> - <?php } ?> 

                                <?php
                                if (isset($start_date) && isset($end_date) && $start_date != $end_date)
                                {
                                    display_food_end_date();
                                }
                                ?> 
                                <?php
                                if (!empty($end_date) && !empty($end_time))
                                {
                                    display_date_time_separator();
                                }
                                ?> 
                                <?php display_food_end_time(); ?>
                            </span>
                        </div>

                        <div class="wpfm-food-location">
                            <span class="wpfm-food-location-text">
                                <?php
                                if (get_food_location() == 'Online Event' || get_food_location() == ''): echo __('Online Event', 'wp-food-manager');
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

                        <!-- Show in list View // Hide in Box View -->
                        <?php
                        if (get_food_ticket_option())
                        {
                            ?>
                            <div class="wpfm-food-ticket-type" class="wpfm-food-ticket-type-text">
                                <span class="wpfm-food-ticket-type-text"><?php display_food_ticket_option(); ?></span>
                            </div>
                        <?php } ?>
                        <!-- Show in list View // Hide in Box View -->
                    </div>
                </div>   
            </a>
        </div>
    </div>
</div>