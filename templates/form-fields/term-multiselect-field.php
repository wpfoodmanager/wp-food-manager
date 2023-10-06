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
wp_enqueue_script('wp-food-manager-term-multiselect');
$args = array(
	'taxonomy'     => esc_attr($field['taxonomy']),
	'hierarchical' => 1,
	'name'         => isset($field['name']) ? esc_attr($field['name']) : esc_attr($key),
	'orderby'      => 'name',
	'selected'     => $selected,
	'hide_empty'   => false
);
if (isset($field['placeholder']) && !empty($field['placeholder'])) $args['placeholder'] = esc_attr($field['placeholder']);
food_manager_dropdown_selection(apply_filters('food_manager_term_multiselect_field_args', $args));
if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>