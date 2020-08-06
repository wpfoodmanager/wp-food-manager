<input type="checkbox" class="input-checkbox" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php checked( ! empty( $field['value'] ), true ); ?> value="1" attribute="<?php echo esc_attr( isset( $field['attribute'] ) ? $field['attribute'] : '' ); ?>"  <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> />


<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>