<select name="<?php echo esc_attr(isset($field['name']) ? $field['name'] : $key); ?>" id="<?php echo isset($field['id']) ? esc_attr($field['id']) : esc_attr($key); ?>" class="input-select <?php echo esc_attr(isset($field['class']) ? $field['class'] : $key); ?>">
	<?php
	$value = isset($field['value']) ? $field['value'] : $field['default'];
	echo esc_html(WPFM_Date_Time::timezone_choice($value));
	?>
</select>