<?php if ($title_query->have_posts()) : ?>
        <div class="food-menu-titles" id="food-menu-titles" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php while ($title_query->have_posts()) : $title_query->the_post(); ?>
                <div class="wpfm-food-menu-title" style="flex: 0 auto;">
                    <a href="#menu-<?php the_ID(); ?>" class="wpfm-menu-title-link"><?php the_title(); ?></a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif;