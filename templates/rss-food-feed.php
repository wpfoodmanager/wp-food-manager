<?php

$food_type = get_food_type($post_id);
$food_category = get_food_category($post_id);

do_action('food_fee_item_start');

if (isset($food_type->name)) {

	echo "<food_manager:food_type><![CDATA[" . esc_html($food_type->name) . "]]></food_manager:food_type>\n";
}

if (isset($food_category->name)) {

	echo "<food_manager:food_category><![CDATA[" . esc_html($food_category->name) . "]]></food_manager:food_category>\n";
}

do_action('food_fee_item_end');
