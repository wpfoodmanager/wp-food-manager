<?php
$field_val_num = '';
if(!empty($field['value']) && is_array($field['value']) && isset($field['value'])){
   $counter = end(explode("_", $key));
   $field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';
} else {
   $field_val_num = !empty($field['value']) ? $field['value'] : '';
}

foreach ( $field['options'] as $option_key => $value ) : 

	?>
	<label><input type="checkbox" class="input-checkbox" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>[]" id="<?php echo esc_attr( $option_key ); ?>" attribute="<?php echo esc_attr( isset( $field['attribute'] ) ? $field['attribute'] : '' ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php if (!empty($field_val_num) && is_array($field_val_num)) checked(in_array($option_key, $field_val_num, true)); ?> /> <?php echo esc_html( $value ); ?></label>
<?php endforeach; ?>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>