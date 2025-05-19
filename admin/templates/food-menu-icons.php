<div class="wpfm-parent-icons">
    <input type="text" id="wpfm_icon_search" name="wpfm_icon_search" placeholder="Icon Search">
    <span class="wpfm-searh-clear">
        <i class="fa fa-times"></i>
    </span>
</div>
<div class="no-radio-icons">
    <strong><?php _e('No icons found!', 'wp-food-manager' ); ?></strong>
</div>
<div class='wpfm-food-icon-class'>
    <?php foreach ($food_menu_icon_list as $key => $icon) {
        $radio_checked = (get_post_meta($food_menu_id, 'wpfm_radio_icons', true) === $key) ? "checked" : ""; ?>
        <div class="sub-font-icon">
            <input type="radio" id="<?php echo $key;?>" name="radio_icons" value="<?php echo $key;?>" <?php echo $radio_checked;?> />
            <label for="<?php echo $key;?>">
                <span class="wpfm-key-name"><?php echo $key;?></span>
                <i class="dashicons <?php echo $key;?>"></i>
            </label>
        </div>
    <?php }

    foreach ($food_icon_list as $key => $icon) {
        $radio_checked = (get_post_meta($food_menu_id, 'wpfm_radio_icons', true) === $key) ? "checked" : "";
        $key_name = str_replace("wpfm-menu-", "", $key); ?>
        <div class="sub-font-icon">
            <input type="radio" id="<?php echo $key;?>" name="radio_icons" value="<?php echo $key;?>" <?php echo $radio_checked;?> />
            <label for="' . $key . '">
                <span class="wpfm-key-name"><?php echo $key_name;?></span>
                    <?php if ($key_name == 'fast-cart') { ?>
                        <span class="wpfm-menu wpfm-menu-fast-cart">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </span>
                    <?php } elseif ($key_name == 'rice-bowl') { ?>
                        <span class="wpfm-menu wpfm-menu-rice-bowl">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </span>
                    <?php } else { ?>
                        <span class="wpfm-menu <?php echo esc_attr($key);?>"></span>
                    <?php } ?>
                </span>
            </label>
        </div>
    <?php } ;?>
</div>