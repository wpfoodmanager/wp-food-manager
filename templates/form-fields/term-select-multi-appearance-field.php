<?php
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

$unit = get_terms( 'food_manager_unit', array(
    'orderby'    => 'count',
    'hide_empty' => 0
) );

wp_enqueue_script('wp-food-manager-term-multiselect');
wp_enqueue_script('wp-food-manager-term-select-multi-appearance');
wp_localize_script(
    'wp-food-manager-term-select-multi-appearance',
    'appearance_params',
    array(
        'unit_terms' => json_encode($unit),
    )
);

$args = array(
    'taxonomy'     => $field['taxonomy'],
    'hierarchical' => 1,
    'name'         => isset($field['name']) ? $field['name'] : $key,
    'orderby'      => 'name',
    'selected'     => $selected,
    'hide_empty'   => false
);

if (isset($field['placeholder']) && !empty($field['placeholder'])) $args['placeholder'] = $field['placeholder'];

echo '<div class="multiselect_appearance">';
food_manager_dropdown_selection(apply_filters('food_manager_term_select_multi_appearance_field_args', $args));
echo '</div>';

if (!empty($field['description'])) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<div class="<?php echo isset($field['name']) ? $field['name'] : $key; ?>-preview selection-preview" data-name="<?php echo ($field['taxonomy'] == 'food_manager_ingredient') ? '_ingredient' : '_nutrition'; ?>">
    <legend>Preview:</legend>
    <ul class="preview-items"></ul>
</div>