<?php foreach ( $field['options'] as $option_key => $value ) : 

	?>
	<!-- <input type="checkbox" class="input-checkbox" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php if(! empty($field['value']) && $field['value'] == true ){ echo 'checked="checked"'; } ?> value="1" attribute="<?php echo esc_attr( isset( $field['attribute'] ) ? $field['attribute'] : '' ); ?>"  <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> /> -->

	<label><input type="checkbox" class="input-checkbox" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" attribute="<?php echo esc_attr( isset( $field['attribute'] ) ? $field['attribute'] : '' ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php if(isset($field['value']) == $value ){ echo 'checked="checked"'; } ?> /> <?php echo esc_html( $value ); ?></label>
<?php endforeach; ?>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>