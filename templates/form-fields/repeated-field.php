<?php if (!empty($field['value']) && is_array($field['value'])) : ?>
    <?php foreach ($field['value'] as $index => $value) : ?>
        <div class="repeated-row-<?php echo esc_attr($key); ?>">
            <input type="hidden" class="repeated-row" name="repeated-row-<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr(absint($index)); ?>" />
            <div class="wpfm-tabs-wrapper wpfm-add-tab-wrapper">
                <div id="repeated-field-details-<?php echo esc_attr($key) . '-' . esc_attr(absint($index)); ?>">
                    <?php foreach ($field['fields'] as $subkey => $subfield) : ?>
                        <div class="row">
                            <fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($subkey); ?>">
                                <?php if (!empty($subfield['label'])) : ?>
                                    <label for="<?php echo esc_attr($subkey); ?>" class="wpfm-form-label-text"><?php echo esc_html(sanitize_title($subfield['label'])) . ($subfield['required'] ? '' : ' <small>' . esc_html__('(optional)', 'wp-food-manager') . '</small>'); ?></label>
                                <?php endif; ?>
                                <div class="field">
                                    <?php
                                    $subfield['name']  = esc_attr($key) . '_' . esc_attr($subkey) . '_' . esc_attr(absint($index));
                                    $subfield['id']  = esc_attr($key) . '_' . esc_attr($subkey) . '_' . esc_attr(absint($index));
                                    $subfield['value'] = esc_attr(isset($value[$subkey]) ? $value[$subkey] : '');
                                    get_food_manager_template('form-fields/' . $subfield['type'] . '-field.php', array('key' => $subkey, 'field' => $subfield));
                                    ?>
                                </div>

                            </fieldset>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div><!-- / wpfmtab wraper  -->
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<button class="button"><?php esc_html_e('Add field', 'wp-food-manager'); ?></button>
<a href="#" class="wpfm-theme-text-button wpfm_repeated_add_field" data-row="<?php echo esc_attr(ob_start()); ?>
		<div class=" repeated-row-<?php echo esc_attr($key . '_%%repeated-row-index%%'); ?>>
    <input type="hidden" class="repeated-row" name="repeated-row-<?php echo esc_attr($key); ?>[]" value="%%repeated-row-index%%" />
    <div class="wpfm-tabs-wrapper wpfm-add-tab-wrapper">
        <div class="wpfm-tabs-action-buttons">
            <div class="wpfm-counter-wrapper">
                <div class="wpfm-counter"><?php echo esc_html('%%repeated-row-index%%'); ?></div>
            </div>
            <div class="wpfm-close-button"><a href="#remove" class="remove-row" title="<?php esc_html_e('Remove', 'wp-food-manager'); ?>" id="repeated-row-<?php echo esc_attr($key . '_%%repeated-row-index%%'); ?>"><i class="wpfm-icon-cross"></i></a></div>
        </div>
        <ul class="wpfm-tabs-wrap">
            <li class="wpfm-tab-link active" data-tab="%%repeated-row-index%%"><?php esc_html_e('Details', 'wp-food-manager'); ?></li>
            <li class="wpfm-tab-link" data-tab="<?php echo esc_attr($key); ?>_%%repeated-row-index%%"><?php esc_html_e('Settings', 'wp-food-manager'); ?></li>
        </ul>
        <div id="<?php echo esc_attr($key . '-' . '%%repeated-row-index%%'); ?>" class="wpfm-tab-content current">
            <div id="%%repeated-row-index%%" class="wpfm-tab-pane active">
                <?php foreach ($field['fields'] as $subkey => $subfield) : ?>
                    <fieldset class="wpfm-form-group fieldset-<?php echo esc_attr($subkey); ?>">
                        <?php if (!empty($subfield['label'])) : ?>
                            <label for="<?php echo esc_attr($subkey); ?>" class="wpfm-form-label-text"><?php echo esc_html($subfield['label']) . ($subfield['required'] ? '' : ' <small>' . esc_html__('(optional)', 'wp-food-manager') . '</small>'); ?></label>
                        <?php endif; ?>
                        <div class="field">
                            <?php
                            $subfield['name']  = esc_attr($key) . '_' . esc_attr($subkey) . '_%%repeated-row-index%%';
                            $subfield['id']  = esc_attr($key) . '_' . esc_attr($subkey) . '_%%repeated-row-index%%';
                            get_food_manager_template('form-fields/' . $subfield['type'] . '-field.php', array('key' => $subkey, 'field' => $subfield));
                            ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        echo esc_attr(ob_get_clean());
        ?>">
        <?php
        if (!empty($field['label'])) {
            echo esc_html($field['label']);
        };
        ?>
</a>
<?php if (!empty($field['description'])) : ?><small class="description"><?php echo esc_html($field['description']); ?></small><?php endif; ?>