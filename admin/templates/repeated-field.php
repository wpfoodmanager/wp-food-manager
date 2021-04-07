<div class="wpfm-content" data-field="1">
	<?php  foreach( $field['fields'] as $subkey => $subfield ) : ?>
		<?php	
		echo $subkey;
		$subfield['name']  	=  $subkey.'_%%attribute_label%%[]';
		$subfield['id']  	=   $subkey.'_%%repeated-field-index%%';
		$subfield['attribute'] = $key;
		$subfield['value'] = '';
	
		$type = ! empty( $subfield['type'] ) ? $subfield['type'] : 'text';
	if($type == 'wp-editor') $type = 'wp_editor';
	
	if ( has_action( 'food_manager_input_' . $type ) ) {
		do_action( 'food_manager_input_' . $type, $key, $subfield );
	} elseif ( method_exists( $this, 'input_' . $type ) ) {
		call_user_func( array( $this, 'input_' . $type ), $key, $subfield );
	}
	?>
	<?php endforeach; ?>
		<a class="wpfm-remove-attribute-field" data-id="wpfm-attributes-box-%%repeated-row-index%%"><?php _e('Delete','wp-food-manager');?></a>	
</div>
