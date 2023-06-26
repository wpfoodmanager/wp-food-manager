<?php
global $wp_scripts;
// Get selected value
if (isset($field['value'])) {
    $selected = $field['value'];
} elseif (!empty($field['default']) && is_int($field['default'])) {
    $selected = $field['default'];
} elseif (!empty($field['default']) && ($term = get_term_by('slug', $field['default'], $field['taxonomy']))) {
    $selected = $term->term_id;
} else {
    $selected = '';
}
$unit = get_terms('food_manager_unit', array(
    'orderby'    => 'count',
    'hide_empty' => 0
));
wp_enqueue_script('wp-food-manager-term-multiselect');
wp_enqueue_script('wp-food-manager-term-select-multi-appearance');
wp_localize_script(
    'wp-food-manager-term-select-multi-appearance',
    'appearance_params',
    array(
        'unit_terms' => json_encode($unit),
    )
);
/* Check if localize script is running or not to run globally localize script */
$data = $wp_scripts->get_data('wp-food-manager-term-select-multi-appearance', 'data');
if (empty($data)) {
    wp_localize_script(
        'jquery',
        'appearance_params',
        array(
            'unit_terms' => json_encode($unit),
        )
    );
}
$args = array(
    'taxonomy'     => esc_attr($field['taxonomy']),
    'hierarchical' => 1,
    'name'         => isset($field['name']) ? esc_attr($field['name']) : $key,
    'orderby'      => 'name',
    'selected'     => $selected,
    'hide_empty'   => false,
    'name_attr'    => false,
);
// For Edit screen of food
$preview_htm = '';
$style = 'display:none;';
if (isset($_GET['food_id']) && !empty($_GET['food_id']) || $food_id) {
    if (isset($_GET['food_id']) && !empty($_GET['food_id'])) {
        $food_id = $_GET['food_id'];
    } else if ($food_id) {
        $food_id = $food_id;
    } else {
        $food_id = '';
    }
    $meta_key = ($field['taxonomy'] == 'food_manager_nutrition') ? 'food_nutritions' : ($field['taxonomy'] == 'food_manager_ingredient' ? 'food_ingredients' : '');
    $term_name = ($meta_key == 'food_nutritions') ? 'nutrition_term_name' : ($meta_key == 'food_ingredients' ? 'ingredient_term_name' : '');
    if ($meta_key) {
        $tax_values = get_post_meta($food_id, '_' . $meta_key, true);
        $unit_terms = get_terms(array(
            'taxonomy' => 'food_manager_unit',
            'hide_empty' => false,
        ));
        if ($tax_values) {
            foreach ($tax_values as $tax_value) {
                $unit_option = '<option value="">Unit</option>';
                if ($unit_terms) {
                    foreach ($unit_terms as $unit) {
                        $unit_option .= '<option value="' . $unit->term_id . '" ' . ($unit->term_id == $tax_value['unit_id'] ? 'selected' : '') . '>' . esc_html($unit->name) . '</option>';
                    }
                }
                $preview_htm .= '<li class="term-item" data-id="' . $tax_value['id'] . '">';
                $preview_htm .= '<label>' . esc_html($tax_value[$term_name]) . '</label>';
                $preview_htm .= '<div class="term-item-flex">';
                $preview_htm .= '<input type="number" min="0" step="0.1" value="' . esc_attr($tax_value['value']) . '" name="' . esc_attr($meta_key) . '[' . esc_attr($tax_value['id']) . '][value]">';
                $preview_htm .= '<select name="' . esc_attr($meta_key) . '[' . esc_attr($tax_value['id']) . '][unit_id]">' . $unit_option . '</select>';
                $preview_htm .= '</div>';
                $preview_htm .= '</li>';
                $style = '';
            }
        }
    }
}
if (isset($field['placeholder']) && !empty($field['placeholder'])) $args['placeholder'] = esc_attr($field['placeholder']);
echo '<div class="multiselect_appearance">';
food_manager_dropdown_selection(apply_filters('food_manager_term_select_multi_appearance_field_args', $args));
echo '</div>';
if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>
<div class="<?php echo isset($field['name']) ? esc_attr($field['name']) : $key; ?>-preview selection-preview" style="<?php echo esc_attr($style); ?>" data-name="<?php echo ($field['taxonomy'] == 'food_manager_ingredient') ? 'food_ingredients' : 'food_nutritions'; ?>">
    <legend>Preview:</legend>
    <ul class="preview-items"><?php echo $preview_htm; ?></ul>
</div>