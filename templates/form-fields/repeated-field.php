<?php
/**
 * Repeated fields is generated from this page .
 * Repeated fields for the paid and free tickets.
 * This field is used in submit food form.
 **/
?>
<?php if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) : ?>

<?php foreach ( $field['value'] as $index => $value ) : ?>

    <div class="repeated-row-<?php echo esc_attr( $key ); ?>">
    <input type="hidden" class="repeated-row" name="repeated-row-<?php echo esc_attr( $key ); ?>[]" value="<?php echo absint( $index ); ?>" />
    <div class="wpfm-tabs-wrapper wpfm-add-tickets-tab-wrapper">
            <div class="wpfm-tabs-action-buttons">
                <div class="wpfm-ticket-counter-wrapper"><div class="wpfm-ticket-counter"><?php echo absint( $index ); ?></div></div>                
                <div class="wpfm-ticket-notice-info"><a class="ticket-notice-info" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?php _e('You can\'t delete ticket once it is added.You can make it private from settings tab.','wp-food-manager');?>" ><i class="wpfm-icon-blocked"></i></a></div>
            </div>
            <ul class="wpfm-tabs-wrap">
                <li class="wpfm-tab-link active" data-tab="sell-ticket-details_<?php echo esc_attr( $index ); ?>"><?php _e('Ticket details','wp-food-manager');?></li>
                <li class="wpfm-tab-link" data-tab="<?php echo $key; ?>_<?php echo absint( $index ); ?>"><?php _e('Settings','wp-food-manager');?></li>
            </ul>

            <div id="sell-ticket-details-<?php echo $key . '-' . $index; ?>"  class="wpfm-tab-content current">
            <div id="sell-ticket-details_<?php echo absint( $index ); ?>" class="wpfm-tab-pane active">
              <?php foreach ( $field['fields'] as $subkey => $subfield ) : 
                        if ($subkey == 'ticket_description') : ?>
            </div><!------------end ticket details tab------>
            <div id="<?php echo $key . '_' . $index; ?>" class="wpfm-tab-pane">
                        <?php endif;?>        
             <div class="row">
                    <fieldset class="wpfm-form-group fieldset-<?php esc_attr_e( $subkey ); ?>" >
                       <?php if(!empty($subfield['label'])) : ?>
                         <label for="<?php esc_attr_e( $subkey ); ?>" class="wpfm-form-label-text"><?php echo $subfield['label'] . ( $subfield['required'] ? '' : ' <small>' . __( '(optional)', 'wp-food-manager' ) . '</small>' ); ?></label>
                       <?php endif; ?>
                       
                            <div class="field">
                                <?php                                
                                    $subfield['name']  = $key . '_' . $subkey . '_' . $index;
                                    $subfield['id']  =$key . '_' . $subkey . '_' . $index;   
                                    $subfield['value'] = isset( $value[ $subkey ]) ? $value[ $subkey ] : '';
                                    get_food_manager_template( 'form-fields/' . $subfield['type'] . '-field.php', array( 'key' => $subkey, 'field' => $subfield ) );
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
<a href="#" class="wpfm-theme-text-button food_ticket_add_link" data-row="<?php
	ob_start();
	?>
		<div class="repeated-row-<?php echo esc_attr( $key.'_%%repeated-row-index%%' ); ?>">
		
		<input type="hidden" class="repeated-row" name="repeated-row-<?php echo esc_attr( $key ); ?>[]" value="%%repeated-row-index%%" />
		
        <div class="wpfm-tabs-wrapper wpfm-add-tickets-tab-wrapper">

            <div class="wpfm-tabs-action-buttons">

                <div class="wpfm-ticket-counter-wrapper"><div class="wpfm-ticket-counter"><?php echo '%%repeated-row-index%%'; ?></div></div>

                <div class="wpfm-ticket-close-button"><a href="#remove" class="remove-row" title="<?php _e( 'Remove', 'wp-food-manager' ); ?>" id="repeated-row-<?php echo esc_attr( $key.'_%%repeated-row-index%%' ); ?>" ><i class="wpfm-icon-cross"></i></a></div>   
            </div>

            <ul class="wpfm-tabs-wrap">
                <li class="wpfm-tab-link active" data-tab="sell-ticket-details_%%repeated-row-index%%"><?php _e('Ticket details','wp-food-manager');?></li>
                <li class="wpfm-tab-link" data-tab="<?php echo $key; ?>_%%repeated-row-index%%"><?php _e('Settings','wp-food-manager');?></li>
            </ul>
            <div id="sell-ticket-details-<?php echo $key . '-' . '%%repeated-row-index%%'; ?>" class="wpfm-tab-content current">
                <div id="sell-ticket-details_%%repeated-row-index%%" class="wpfm-tab-pane active">
                <?php  foreach ( $field['fields'] as $subkey => $subfield ) : 
                                if ($subkey == 'ticket_description') : ?>           
                </div><!------------end ticket details tab------>
                <div id="<?php echo $key; ?>_%%repeated-row-index%%" class="wpfm-tab-pane">
                <?php endif;?>
                
                    <fieldset class="wpfm-form-group fieldset-<?php esc_attr_e( $subkey ); ?>">
                        <?php if(!empty($subfield['label'])) : ?>
                        <label for="<?php esc_attr_e( $subkey ); ?>" class="wpfm-form-label-text"><?php echo $subfield['label'] . ( $subfield['required'] ? '' : ' <small>' . __( '(optional)', 'wp-food-manager' ) . '</small>' ); ?></label>
                        <?php endif; ?>

                        <div class="field">
                            <?php                           
                                $subfield['name']  = $key . '_' . $subkey . '_%%repeated-row-index%%';
                                $subfield['id']  = $key . '_' . $subkey . '_%%repeated-row-index%%';    
                                get_food_manager_template( 'form-fields/' . $subfield['type'] . '-field.php', array( 'key' => $subkey, 'field' => $subfield ) );
                            ?>
                        </div>
                    </fieldset>   
            <?php endforeach; ?>
                </div>
        </div>
	<?php
	echo esc_attr( ob_get_clean() );
?>">+ <?php if( ! empty( $field['label'] ) ){ echo $field['label'];};
?></a>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>