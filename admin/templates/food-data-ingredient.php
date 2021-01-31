<?php
/**
*  Template ingredient panel
*/
	
echo '<div id="ingredient_food_data_content" class="panel wpfm_panel">
<div class="wp_food_manager_meta_data">';

do_action( 'food_manager_food_data_ingredient_start', $thepostid );
?>
<div class="wpfm-ingredient-fields">
	
</div>
<?php
do_action( 'food_manager_food_data_ingredient_end', $thepostid );
echo '</div></div>';