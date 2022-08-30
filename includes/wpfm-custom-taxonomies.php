<?php

/**
 * Taxonomies
 */

if ( get_option( 'food_manager_enable_categories' ,true) ) {

	$singular  = __( 'Food Category', 'wp-food-manager' );

	$plural    = __( 'Categories', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['category_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false
		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;
	}

	register_taxonomy( "food_manager_category",
		
	apply_filters( 'register_taxonomy_food_manager_category_object_type', array( 'food_manager' ) ),

   	 	apply_filters( 'register_taxonomy_food_manager_category_args', array(

            'hierarchical' 			=> true,

            'update_count_callback' => '_update_post_term_count',

            'label' 				=> $plural,

            'labels' => array(

				'name'              => $plural,

				'singular_name'     => $singular,

				'menu_name'         => ucwords( $plural ),

				'search_items'      => sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

				'all_items'         => sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

				'parent_item'       => sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

				'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

				'edit_item'         => sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

				'update_item'       => sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

				'add_new_item'      => sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

				'new_item_name'     => sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )

        	),

            'show_ui' 				=> true,
   	 		'show_in_rest'          => true,
   	 		//'hierarchical'        => false, // Hierarchical causes memory issues
            'public' 	     		=> $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,

            ),

            'rewrite' 				=> $rewrite,

        ) )

    );

}

if ( get_option( 'food_manager_enable_food_types' ,true) ) {

        $singular  = __( 'Food Type', 'wp-food-manager' );

		$plural    = __( 'Types', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['type_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false

		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;

	}

	register_taxonomy( "food_manager_type",

	apply_filters( 'register_taxonomy_food_manager_types_object_type', array( 'food_manager' ) ),

        apply_filters( 'register_taxonomy_food_manager_types_args', array(

            'hierarchical' 			=> true,

            'label' 				=> $plural,

            'labels' => array(

                'name' 				=> $plural,

                'singular_name' 	=> $singular,

                'menu_name'         => ucwords( $plural ),

                'search_items' 		=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

                'all_items' 		=> sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

                'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

                'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

                'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

                'update_item' 		=> sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

                'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

                'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )
        	),

            'show_ui' 				=> true,
        		
        	'show_in_rest'          => true,

            'public' 			    => $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,
            ),

           'rewrite' 				=> $rewrite,
        ) )
    );
}


if ( get_option( 'food_manager_enable_food_tags' ,true) ) {

        $singular  = __( 'Food Tags', 'wp-food-manager' );

		$plural    = __( 'Tags', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['type_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false

		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;

	}

	register_taxonomy( "food_manager_tag",

	apply_filters( 'register_taxonomy_food_manager_tags_object_tag', array( 'food_manager' ) ),

        apply_filters( 'register_taxonomy_food_manager_tags_args', array(

            'hierarchical' 			=> true,

            'label' 				=> $plural,

            'labels' => array(

                'name' 				=> $plural,

                'singular_name' 	=> $singular,

                'menu_name'         => ucwords( $plural ),

                'search_items' 		=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

                'all_items' 		=> sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

                'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

                'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

                'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

                'update_item' 		=> sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

                'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

                'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )
        	),

            'show_ui' 				=> true,
        		
        	'show_in_rest'          => true,

   	 		'hierarchical'        => false,

            'public' 			    => $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,
            ),

           'rewrite' 				=> $rewrite,
        ) )
    );
}


if ( get_option( 'food_manager_enable_food_ingredients' ,true) ) {

        $singular  = __( 'Food Ingredient', 'wp-food-manager' );

		$plural    = __( 'Ingredients', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['type_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false

		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;

	}

	register_taxonomy( "food_manager_ingredient",

	apply_filters( 'register_taxonomy_food_manager_ingredients_object_type', array( 'food_manager' ) ),

        apply_filters( 'register_taxonomy_food_manager_ingredients_args', array(

            'hierarchical' 			=> true,

            'label' 				=> $plural,

            'labels' => array(

                'name' 				=> $plural,

                'singular_name' 	=> $singular,

                'menu_name'         => ucwords( $plural ),

                'search_items' 		=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

                'all_items' 		=> sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

                'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

                'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

                'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

                'update_item' 		=> sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

                'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

                'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )
        	),

            'show_ui' 				=> true,
        		
        	'show_in_rest'          => true,

        	'meta_box_cb'		    => false,

            'public' 			    => $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,
            ),

           'rewrite' 				=> $rewrite,
        ) )
    );
}

if ( get_option( 'food_manager_enable_food_nutritions' ,true) ) {

        $singular  = __( 'Food Nutritions', 'wp-food-manager' );

		$plural    = __( 'Nutritions', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['type_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false

		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;

	}

	register_taxonomy( "food_manager_nutrition",

	apply_filters( 'register_taxonomy_food_manager_nutritions_object_type', array( 'food_manager' ) ),

        apply_filters( 'register_taxonomy_food_manager_nutritions_args', array(

            'hierarchical' 			=> true,

            'label' 				=> $plural,

            'labels' => array(

                'name' 				=> $plural,

                'singular_name' 	=> $singular,

                'menu_name'         => ucwords( $plural ),

                'search_items' 		=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

                'all_items' 		=> sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

                'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

                'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

                'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

                'update_item' 		=> sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

                'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

                'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )
        	),

            'show_ui' 				=> true,
        		
        	'show_in_rest'          => true,

        	'meta_box_cb'		    => false,

            'public' 			    => $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,
            ),

           'rewrite' 				=> $rewrite,
        ) )
    );
}

if ( get_option( 'food_manager_enable_food_units' ,true) ) {

        $singular  = __( 'Unit', 'wp-food-manager' );

		$plural    = __( 'Units', 'wp-food-manager' );

	if ( current_theme_supports( 'food-manager-templates' ) ) {

		$rewrite   = array(

			'slug'         => $permalink_structure['type_rewrite_slug'],

			'with_front'   => false,

			'hierarchical' => false

		);

		$public    = true;

	} else {

		$rewrite   = false;

		$public    = false;

	}

	register_taxonomy( "food_manager_unit",

	apply_filters( 'register_taxonomy_food_manager_units_object_type', array( 'food_manager' ) ),

        apply_filters( 'register_taxonomy_food_manager_units_args', array(

            'hierarchical' 			=> true,

            'label' 				=> $plural,

            'labels' => array(

                'name' 				=> $plural,

                'singular_name' 	=> $singular,

                'menu_name'         => ucwords( $plural ),

                'search_items' 		=> sprintf( __( 'Search %s', 'wp-food-manager' ), $plural ),

                'all_items' 		=> sprintf( __( 'All %s', 'wp-food-manager' ), $plural ),

                'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-food-manager' ), $singular ),

                'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-food-manager' ), $singular ),

                'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-food-manager' ), $singular ),

                'update_item' 		=> sprintf( __( 'Update %s', 'wp-food-manager' ), $singular ),

                'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-food-manager' ), $singular ),

                'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-food-manager' ),  $singular )
        	),

            'show_ui' 				=> true,
        		
        	'show_in_rest'          => true,

        	'meta_box_cb'		    => false,

            'public' 			    => $public,

            'capabilities'			=> array(

            	'manage_terms' 		=> $admin_capability,

            	'edit_terms' 		=> $admin_capability,

            	'delete_terms' 		=> $admin_capability,

            	'assign_terms' 		=> $admin_capability,
            ),

           'rewrite' 				=> $rewrite,
        ) )
    );
}