<ul class="food-manager-term-checklist food-manager-term-checklist-<?php echo $key ?>"><?php	$field_val_num = '';	if(!empty($field['value']) && is_array($field['value']) && isset($field['value'])){	   $counter = end(explode("_", $key));	   $field_val_num = !empty($field['value'][$counter]) ? $field['value'][$counter] : '';	} else {	   $field_val_num = !empty($field['value']) ? $field['value'] : '';	}		/*require_once( ABSPATH . '/wp-admin/includes/template.php' );	if ( empty( $field['default'] ) ) {		$field['default'] = '';	}*/	/*$args = array(		'descendants_and_self'  => 0,		'selected_cats'         => isset( $field['value'] ) ? $field['value'] : ( is_array( $field['default'] ) ? $field['default'] : array( $field['default'] ) ),		'popular_cats'          => false,		'taxonomy'              => $field['taxonomy'],		'checked_ontop'         => true	);*/    $my_check_value_arr = [];	if(is_array($field['value'])){        foreach ($field['value'][$field['taxonomy']] as $my_value) {            $my_check_value_arr[] = $my_value;        }    }    //$my_check_value_arr = implode(', ', $my_check_value_arr);    // $field['post_id'] needs to be passed via the args so we can get the existing terms	ob_start();	//wp_terms_checklist( 0, $args );	wpfm_category_checklist($field['taxonomy'], $key, $my_check_value_arr);	$checklist = ob_get_clean();	echo str_replace( "disabled='disabled'", '', $checklist );?></ul><?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>