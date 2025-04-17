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
	<select name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo esc_attr($key); ?>" <?php if (!empty($field['required'])) echo 'required'; ?> attribute="<?php echo esc_attr(isset($field['attribute']) ? $field['attribute'] : ''); ?>" <?php echo esc_attr(isset($field['custom_attribute']) ? $field['custom_attribute'] : ''); ?>>
		<?php 
		if (is_array($field['options']) || is_object($field['options'])) :
			foreach ($field['options'] as $key => $value) : 
				if(isset($field['value']) ){
					if(is_array($field['value']))
						$selected = $field['value'][0];
					else
						$selected = $field['value'];
				}else{
					if(isset($field['default']))
						$selected = $field['default'];
					else
						$selected = '';
				} ?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected($selected, $key); ?>><?php echo esc_attr($value); ?></option>
		<?php endforeach;
		endif; ?>
	</select>
<?php }
if( !empty( $field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>