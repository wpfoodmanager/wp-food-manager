<?php
if ($field['type'] == 'url') {
    echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-textarea-text">';
    if (isset($field_value) && !empty($field_value) && wpfm_begnWith($field_value, "http")) {
        echo '<a target="_blank" href="' . esc_url($field_value, 'wp-food-manager') . '">' . sanitize_title(esc_html($field['label'], 'wp-food-manager')) . '</a>';
    } else {
        printf(__('%s', 'wp-food-manager'), sanitize_title(esc_html($field['label'])));
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'text') {
    if (is_array($field_value)) {
        $field_value = '';
    }
    echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($field_value) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'textarea' || $field['type'] == 'wp-editor') {
    if (wpfm_begnWith($field_value, "http") || is_array($field_value)) {
        $field_value = '';
    }
    echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_html(sanitize_title($field['label'])) . '</strong></p>';
    echo '<p class="wpfm-additional-info-block-textarea-text">' . esc_html(sanitize_title($field_value)) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'multiselect') {
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    $my_value_arr = [];
    if (is_array($field_value)) {
        foreach ($field_value as $key => $my_value) {
            if (in_array(ucfirst($my_value), $field['options'])) {
                $my_value_arr[] = esc_attr($field['options'][$my_value]);
            } else {
                $my_value_arr[] = '';
            }
        }
    }
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_html(sanitize_title($field['label'])) . ' -</strong> ' . implode(', ', $my_value_arr) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif (isset($field['type']) && $field['type'] == 'date') {
    if (is_array($field_value)) {
        $field_value = esc_attr($field_value['0']);
    }
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong> ' . date_i18n(esc_attr($date_format), absint(strtotime($field_value))) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif (isset($field['type']) && $field['type'] == 'time') {
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . printf(__('%s', 'wp-food-manager'), esc_attr(sanitize_title($field['label']))) . ' - </strong> ' . date(esc_attr($time_format), absint(strtotime($field_value))) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'file') {
    echo '<div class="wpfm-col-md-12 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left" style="margin-bottom: 20px;">';
    echo '<div class="wpfm-additional-info-block-details-content-items wpfm-additional-file-slider">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong></p>';
    if (is_array($field_value)) :
        echo '<div class="wpfm-img-multi-container">';
        foreach ($field_value as $file) :
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) :
                echo '<div class="wpfm-img-multiple"><img src="' . esc_attr($file) . '"></div>';
            else :
                if (!empty($file)) {
                    echo '<div><div class="wpfm-icon">';
                    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(wp_basename($file)) . '</strong></p>';
                    echo '<a target="_blank" href="' . esc_attr($file) . '"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>';
                    echo '</div></div>';
                }
            endif;
        endforeach;
        echo '</div>';
    else :
        if (in_array(pathinfo($field_value, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) :
            echo '<div class="wpfm-img-single"><img src="' . esc_attr($field_value) . '"></div>';
        else :
            if (wpfm_begnWith($field_value, "http")) {
                echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(wp_basename($field_value)) . '</strong></p>';
                echo '<a target="_blank" href="' . esc_attr($field_value) . '"><i class="wpfm-icon-download3" style="margin-right: 3px;"></i>Download</a>';
            }
        endif;
    endif;
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'radio' && array_key_exists('options', $field)) {
    $fields_val = isset($field['options'][$field_value]) ? esc_attr($field['options'][$field_value]) : '';
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($fields_val) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'term-checklist' && array_key_exists('taxonomy', $field)) {
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong>' . esc_attr(sanitize_title($field['label'])) . ' - </strong>';
    if (!empty($field_value)) {
        if (is_array($field_value)) {
            $my_checks_value_arr = [];
            if (isset($field_value[$field['taxonomy']])) {
                foreach ($field_value[$field['taxonomy']] as $key => $my_value) {
                    $term_name = esc_attr(sanitize_title(get_term($my_value)->name));
                    $my_checks_value_arr[] = esc_attr(sanitize_title($term_name));
                }
            }
            printf(__('%s', 'wp-food-manager'),  implode(', ', $my_checks_value_arr));
        } else {
            echo !empty(get_term(ucfirst($field_value))) ? esc_attr(sanitize_title(get_term(ucfirst($field_value))->name)) : '';
        }
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'checkbox' && array_key_exists('options', $field)) {
    echo '<div class="wpfm-col-12 wpfm-additional-info-block-textarea">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-textarea-text">';
    echo '<strong>' . esc_attr($field['label']) . '</strong> - ';
    if (is_array($field_value)) {
        $my_check_value_arr = [];
        foreach ($field_value as $key => $my_value) {
            $my_check_value_arr[] = $field['options'][$my_value];
        }
        printf(__('%s', 'wp-food-manager'),  implode(', ', $my_check_value_arr));
    } else {
        if ($field_value == 1) {
            echo esc_attr("Yes");
        } else {
            echo esc_attr("No");
        }
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'term-select') {
    $term_name = esc_html(sanitize_title(get_term($field_value)->name));
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($term_name) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'number') {
    if (!is_array($field_value)) {
        $field_value_count = preg_match('/^[1-9][0-9]*$/', $field_value);
        if ($field_value_count == 0) {
            $field_value = '';
        }
    } else {
        $field_value = '';
    }
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr($field_value) . '</p>';
    echo '</div>';
    echo '</div>';
} elseif ($field['type'] == 'term-multiselect') {
    echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
    echo '<div class="wpfm-additional-info-block-details-content-items">';
    echo '<p class="wpfm-additional-info-block-title">';
    echo '<strong>' . esc_attr($field['label']) . '</strong> - ';
    if (!empty($field_value)) {
        if (is_array($field_value)) {
            $my_select_value_arr = [];
            foreach ($field_value as $key => $my_value) {
                $term_name = get_term($my_value)->name;
                $my_select_value_arr[] = $term_name;
            }
            printf(__('%s', 'wp-food-manager'),  implode(', ', $my_select_value_arr));
        } else {
            echo esc_attr(sanitize_title(get_term(ucfirst($field_value))->name));
        }
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
} else {
    if (is_array($field_value)) :
        echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
        echo '<div class="wpfm-additional-info-block-details-content-items">';
        echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr(implode(', ', $field_value)) . '</p>';
        echo '</div>';
        echo '</div>';
    else :
        echo '<div class="wpfm-col-md-6 wpfm-col-sm-12 wpfm-additional-info-block-details-content-left">';
        echo '<div class="wpfm-additional-info-block-details-content-items">';
        echo '<p class="wpfm-additional-info-block-title"><strong> ' . esc_attr($field['label']) . ' -</strong> ' . esc_attr(ucfirst($field_value)) . '</p>';
        echo '</div>';
        echo '</div>';
    endif;
}