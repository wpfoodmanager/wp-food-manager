<!-- search box for search food menu -->
<input type="text" id="food-menu-search" placeholder="Search for food items..." style="margin-bottom: 20px; padding: 5px; width: 100%; box-sizing: border-box;">
<div id="food-menu-container">
<!-- Food menu heading  -->
<?php if ($title_query->have_posts()) : ?>
        <div class="food-menu-titles" id="food-menu-titles" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php while ($title_query->have_posts()) : $title_query->the_post(); ?>
                <div class="wpfm-food-menu-title" style="flex: 0 auto;">
                    <a href="#menu-<?php the_ID(); ?>" class="wpfm-menu-title-link"><?php the_title(); ?></a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif;