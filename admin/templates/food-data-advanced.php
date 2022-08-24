<?php
/**
*  Template advanced panel
*/
	
echo '<div id="advanced_food_data_content" class="panel wpfm_panel">
<div class="wp_food_manager_meta_data">';

do_action( 'food_manager_food_data_advanced_start', $thepostid );


do_action( 'food_manager_food_data_advanced_end', $thepostid );
echo '</div></div>';