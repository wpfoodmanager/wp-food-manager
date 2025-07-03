<div id="food-menu-container" class="wpfm-food-menu-page-main-container">
     <div class="food-menu-page-filters">
        <!-- search box for search food menu -->
        <div class="wpfm-form-wrapper">
            <div class="wpfm-form-group">
                <input type="text" id="food-menu-search" placeholder="Search for food items...">
            </div>
        </div>
    
        <!-- Food menu tabs  -->
        <?php if ($title_query->have_posts()) : ?>
            <div class="food-menu-page-filter-tabs-wrapper">
                <div class="food-menu-page-filter-tabs" id="food-menu-titles">
                    <?php while ($title_query->have_posts()) : $title_query->the_post(); ?>
                        <div class="food-menu-page-filter-tab">
                            <a href="#menu-<?php the_ID(); ?>" class="food-menu-page-filter-tab-link"><?php the_title(); ?></a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif;