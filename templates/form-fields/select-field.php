<?php
$field_val_num = '';
if (!empty($field['value']) && is_array($field['value'])) {
	$tmp_cnt = explode("_", $key);
	$counter = end($tmp_cnt);
	$field_val_num = isset($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
	$field_val_num = !empty($field['value']) ? $field['value'] : '';
}

if( isset( $field['post_type'] ) && !empty( $field['post_type'] ) ){
	$food_posts_listing = get_posts (array (
		'numberposts' => -1,   
		'post_type' => $field['post_type'],
		'orderby' => 'title',
		'order' => 'ASC'
	)); ?>
	<select name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo esc_attr($key); ?>" <?php if (!empty($field['required'])) echo 'required'; ?> attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>">
		<?php 
		if( !empty( $food_posts_listing ) ) {
			foreach ($food_posts_listing as $option_key => $option_value) :
			$field_val = ( !empty($field['value']) && $field['value'] === $option_value->post_title) ? 'selected' : ''; ?>
			<option value="<?php echo esc_attr($option_value->post_title); ?>" <?php echo esc_attr($field_val); ?>><?php echo esc_html($option_value->post_title); ?></option>
			<?php endforeach; 
		} else {
			echo '<option value="">No Food Menu Added</option>';
		} ?>
	</select>
	<?php
} else { ?>
	<select name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo esc_attr($key); ?>" <?php if (!empty($field['required'])) echo 'required'; ?> attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>">
		<?php foreach ($field['options'] as $option_key => $option_value) :
			$field_val = (!empty($field_val_num) && $field_val_num === $option_key) ? 'selected' : '';	?>
			<option value="<?php echo esc_attr($option_key); ?>" <?php echo esc_attr($field_val); ?>><?php echo esc_html($option_value); ?></option>
		<?php endforeach; ?>
	</select>
<?php }
if( !empty( $field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>