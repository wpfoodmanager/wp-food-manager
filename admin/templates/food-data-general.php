<?php
/**
*  Template general panel
*/
?>			
<div id="general_food_data_content" class="panel wpfm_panel">
<div class="wp_food_manager_meta_data">
<?php
do_action( 'food_manager_food_data_start', $thepostid );
foreach ( $this->food_listing_fields() as $key => $field ) {
	$type = ! empty( $field['type'] ) ? $field['type'] : 'text';
	if($type == 'wp-editor') $type = 'textarea';
	
	if ( has_action( 'food_manager_input_' . $type ) ) {
		do_action( 'food_manager_input_' . $type, $key, $field );
	} elseif ( method_exists( $this, 'input_' . $type ) ) {
		call_user_func( array( $this, 'input_' . $type ), $key, $field );
	}
}
do_action( 'food_manager_food_data_end', $thepostid );
?>
</div>
</div>
