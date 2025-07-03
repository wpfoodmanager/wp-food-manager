<?php
if ($field['type'] == 'url') { ?>
    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-textarea-text">
                <?php if (isset($field_value) && !empty($field_value) && wpfm_begin_with($field_value, "http")) {
                    echo '<a target="_blank" href="' . esc_url($field_value, 'wp-food-manager') . '">' . esc_html($field['label'], 'wp-food-manager') . '</a>';
                } else {
                    // Translators: %s represents the sanitized field label
                    printf(esc_html__('%s', 'wp-food-manager'), esc_html($field['label']));
                } ?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'text') {
    if (is_array($field_value)) {
        $field_value = '';
    } ?>
    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e($field['label']);?> -</strong>
                <?php esc_attr_e($field_value);?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'textarea' || $field['type'] == 'wp-editor') {
    if (wpfm_begin_with($field_value, "http") || is_array($field_value)) {
        $field_value = '';
    } ?>
    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_html_e(sanitize_title($field['label']));?></strong>
            </p>
            <p class="wpfm-additional-info-block-textarea-text">
                <?php esc_html_e(sanitize_title($field_value));?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'multiselect') { ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <?php $multiselect_array = [];
            if (is_array($field_value)) {
                foreach ($field_value as $key => $my_value) {
                    if (in_array(ucfirst($my_value), $field['options'])) {
                        $multiselect_array[] = esc_attr($field['options'][$my_value]);
                    } else {
                        $multiselect_array[] = '';
                    }
                }
            } ?>
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_html_e(sanitize_title($field['label']));?>-</strong>
                <?php esc_html_e(implode(', ', array_map('esc_html', $multiselect_array)));?>
            </p>
        </div>
    </div>
<?php } elseif (isset($field['type']) && $field['type'] == 'date') {
    if (is_array($field_value)) {
        $field_value = esc_attr($field_value['0']);
    } ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_html_e(sanitize_title($field['label']));?>- </strong>
                <?php esc_html_e(date_i18n($date_format, absint(strtotime($field_value))));?>
            </p>
        </div>
    </div>
<?php } elseif (isset($field['type']) && $field['type'] == 'time') { ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title"> 
                <strong>
                    <?php // Translators: %s represents the sanitized field label 
                    Sprintf(esc_html__('%s -', 'wp-food-manager'), esc_html(sanitize_title($field['label']))); ?>
                </strong>
                <?php esc_html_e(date(esc_attr($time_format), absint(strtotime($field_value))));?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'file') { ?>
    <div class="wpfm-col-md-12 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left" style="margin-bottom: 20px;">
        <div class="wpfm-additional-info-block-details-content-items wpfm-additional-file-slider">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e(sanitize_title($field['label']));?> - </strong>
            </p>
            <?php if (is_array($field_value)) : ?>
                <div class="wpfm-img-multi-container">
                    <?php foreach ($field_value as $file) :
                        if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) :?>
                            <div class="wpfm-img-multiple"><img src="<?php esc_attr_e($file);?>"></div>
                        <?php else :
                            if (!empty($file)) { ?>
                                <div>
                                    <div class="wpfm-icon">
                                        <p class="wpfm-additional-info-block-title">
                                            <strong><?php esc_attr_e(wp_basename($file));?></strong>
                                        </p>
                                        <a target="_blank" href="<?php esc_attr_e($file);?>">
                                            <i class="wpfm-icon-download3" style="margin-right: 3px;"></i>
                                            <?php _e('Download', 'wp-food-manager'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php }
                        endif;
                    endforeach; ?>
                </div>
            <?php else :
                if (in_array(pathinfo($field_value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) : ?>
                    <div class="wpfm-img-single">
                        <img src="<?php esc_attr_e($field_value);?>" />
                    </div>
                <?php else :
                    if (wpfm_begin_with($field_value, "http")) { ?>
                        <p class="wpfm-additional-info-block-title">
                            <strong><?php esc_attr_e(wp_basename($field_value));?></strong>
                        </p>
                        <a target="_blank" href="<?php esc_attr_e($field_value);?>">
                            <i class="wpfm-icon-download3" style="margin-right: 3px;"></i>
                            <?php _e('Download', 'wp-food-manager'); ?>
                        </a>
                    <?php }
                endif;
            endif; ?>
        </div>
    </div>
<?php } elseif ($field['type'] == 'radio' && array_key_exists('options', $field)) {
    $fields_val = isset($field['options'][$field_value]) ? esc_attr($field['options'][$field_value]) : ''; ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e($field['label']);?> -</strong>
                <?php esc_attr_e($fields_val);?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'term-checklist' && array_key_exists('taxonomy', $field)) { ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e(sanitize_title($field['label']));?> - </strong>
                <?php if (!empty($field_value)) {
                    if (is_array($field_value)) {
                        $my_checks_value_arr = [];
                        if (isset($field_value[$field['taxonomy']])) {
                            foreach ($field_value[$field['taxonomy']] as $key => $my_value) {
                                $term_name = esc_attr(sanitize_title(get_term($my_value)->name));
                                $my_checks_value_arr[] = esc_attr(sanitize_title($term_name));
                            }
                        }
                        // Translators: %s represents a comma-separated list of checked values
                        printf(esc_html__('%s', 'wp-food-manager'), esc_html( implode(', ', $my_checks_value_arr)));
                    } else {
                        echo !empty(get_term(ucfirst($field_value))) ? esc_attr(sanitize_title(get_term(ucfirst($field_value))->name)) : '';
                    }
                } ?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'checkbox' && array_key_exists('options', $field)) { ?>
    <div class="wpfm-col-12 wpfm-additional-info-block-textarea">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-textarea-text">
                <strong><?php esc_attr_e($field['label']);?></strong> - 
                <?php if (is_array($field_value)) {
                    $my_check_value_arr = [];
                    foreach ($field_value as $key => $my_value) {
                        $my_check_value_arr[] = $field['options'][$my_value];
                    }
                    // Translators: %s represents a list of values joined by commas
                    printf(esc_html__('%s', 'wp-food-manager'), esc_html( implode(', ', $my_check_value_arr)));
                } else {
                    if ($field_value == 1) {
                        echo esc_attr("Yes");
                    } else {
                        echo esc_attr("No");
                    }
                } ?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'term-select') {
    $term_name = esc_html(sanitize_title(get_term($field_value)->name)); ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e($field['label']);?> -</strong>
                <?php esc_attr_e($term_name);?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'number') {
    if (!is_array($field_value)) {
        $field_value_count = preg_match('/^[1-9][0-9]*$/', $field_value);
        if ($field_value_count == 0) {
            $field_value = '';
        }
    } else {
        $field_value = '';
    } ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e($field['label']);?> -</strong>
                <?php esc_attr_e($field_value);?>
            </p>
        </div>
    </div>
<?php } elseif ($field['type'] == 'term-multiselect') { ?>
    <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
        <div class="wpfm-additional-info-block-details-content-items">
            <p class="wpfm-additional-info-block-title">
                <strong><?php esc_attr_e($field['label']);?></strong> -
                <?php if (!empty($field_value)) {
                    if (is_array($field_value)) {
                        $my_select_value_arr = [];
                        foreach ($field_value as $key => $my_value) {
                            $term_name = get_term($my_value)->name;
                            $my_select_value_arr[] = $term_name;
                        }
                        // Translators: %s represents a comma-separated list of selected values
                        printf(esc_html__('%s', 'wp-food-manager'),  esc_html(implode(', ', $my_select_value_arr)));
                    } else {
                        echo esc_attr(sanitize_title(get_term(ucfirst($field_value))->name));
                    }
                } ?>
            </p>
        </div>
    </div>
<?php } else {
    if (is_array($field_value)) : ?>
        <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
            <div class="wpfm-additional-info-block-details-content-items">
                <p class="wpfm-additional-info-block-title">
                    <strong><?php esc_attr_e($field['label']);?> -</strong>
                    <?php esc_attr_e(implode(', ', $field_value));?>
                </p>
            </div>
        </div>
    <?php else : ?>
        <div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">
            <div class="wpfm-additional-info-block-details-content-items">
                <p class="wpfm-additional-info-block-title">
                    <strong><?php esc_attr_e($field['label']);?> -</strong>
                    <?php esc_attr_e(implode(', ', $field_value));?>
                </p>
            </div>
        </div>
    <?php endif;
}