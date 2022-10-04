<?php

//$location = get_food_location( $post_id );
$food_type = get_food_type( $post_id );
$food_category = get_food_category( $post_id );

//$ticket_price  = get_food_ticket_option( $post_id );

do_action('food_fee_item_start');

/*if ( $location ) {

	echo "<food_manager:location><![CDATA[" . esc_html( $location ) . "]]></food_manager:location>\n";
}*/

if (  isset($food_type->name)  ) {
	
	echo "<food_manager:food_type><![CDATA[" . esc_html( $food_type->name ) . "]]></food_manager:food_type>\n";
}

if (  isset($food_category->name)  ) {
	
	echo "<food_manager:food_category><![CDATA[" . esc_html( $food_category->name ) . "]]></food_manager:food_category>\n";
}

/*if ( $ticket_price ) {

	echo "<food_manager:ticket_price><![CDATA[" . esc_html( $ticket_price ) . "]]></food_manager:ticket_price>\n";
}*/
do_action('food_fee_item_end');