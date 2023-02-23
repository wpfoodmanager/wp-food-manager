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
    'taxonomy'     => $field['taxonomy'],
    'hierarchical' => 1,
    'name'         => isset($field['name']) ? $field['name'] : $key,
    'orderby'      => 'name',
    'selected'     => $selected,
    'hide_empty'   => false
);

// For Edit screen of food
$preview_htm = '';
if (isset($_GET['food_id']) && !empty($_GET['food_id'])) {
    $food_id = $_GET['food_id'];
    $meta_key = ($field['taxonomy'] == 'food_manager_nutrition') ? '_nutrition' : ($field['taxonomy'] == 'food_manager_ingredient' ? '_ingredient' : '');

    if ($meta_key) {

        $tax_values = get_post_meta($food_id, $meta_key, true);
        $unit_terms = get_terms(array(
            'taxonomy' => 'food_manager_unit',
            'hide_empty' => false,
        ));

        if ($tax_values) {
            foreach ($tax_values as $tax_value) {

                $unit_option = '<option value="">Unit</option>';

                if ($unit_terms) {
                    foreach ($unit_terms as $unit) {
                        $unit_option .= '<option value="' . $unit->term_id . '" ' . ($unit->term_id == $tax_value['unit_id'] ? 'selected' : '') . '>' . $unit->name . '</option>';
                    }
                }

                $preview_htm .= '<li class="term-item" data-id="' . $tax_value['id'] . '">';
                $preview_htm .= '<label>' . $tax_value['term_name'] . '</label>';
                $preview_htm .= '<div class="term-item-flex">';
                $preview_htm .= '<input type="number" value="' . $tax_value['value'] . '" name="' . $meta_key . '[' . $tax_value['id'] . '][value]">';
                $preview_htm .= '<select name="' . $meta_key . '[' . $tax_value['id'] . '][unit_id]">' . $unit_option . '</select>';
                $preview_htm .= '</div>';
                $preview_htm .= '</li>';
            }
        }
    }
}

if (isset($field['placeholder']) && !empty($field['placeholder'])) $args['placeholder'] = $field['placeholder'];

echo '<div class="multiselect_appearance">';
food_manager_dropdown_selection(apply_filters('food_manager_term_select_multi_appearance_field_args', $args));
echo '</div>';

if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<div class="<?php echo isset($field['name']) ? $field['name'] : $key; ?>-preview selection-preview" data-name="<?php echo ($field['taxonomy'] == 'food_manager_ingredient') ? '_ingredient' : '_nutrition'; ?>">
    <legend>Preview:</legend>
    <ul class="preview-items"><?php echo $preview_htm; ?></ul>
</div>