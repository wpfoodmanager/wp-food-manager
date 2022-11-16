var WPFMFront= function () {

    return {
	    init: function() 
        {
            jQuery(".wpfm-form-group.fieldset-option_description").hide();

            //use body to call after dom update
            jQuery("body").on('click','a.wpfm-food-item-remove',WPFMFront.actions.removeFoodItem);

            //Action button For Extra topping field's content to View more and View less
            jQuery("body").on('click','span.wpfm-view-more',WPFMFront.actions.viewmoreFoodFields);
            jQuery("body").on('click','span.wpfm-view-less',WPFMFront.actions.viewlessFoodFields);

            /* General tab - Regular and Sale price validation */
            jQuery('body').on( 'wpfm_add_error_tip', function( e, element, error_type ) {
                var offset = element.position();

                if ( element.parent().find( '.wpfm_error_tip' ).length === 0 ) {
                    element.after( '<div class="wpfm_error_tip ' + error_type + '">' + wpfm_accounting_params[error_type] + '</div>' );
                    element.parent().find( '.wpfm_error_tip' )
                        .css( 'left', offset.left + element.width() - ( element.width() / 2 ) - ( jQuery( '.wpfm_error_tip' ).width() / 2 ) )
                        .css( 'top', offset.top + element.height() )
                        .fadeIn( '100' );
                }
            });

            jQuery('body').on( 'wpfm_remove_error_tip', function( e, element, error_type ) {
                element.parent().find( '.wpfm_error_tip.' + error_type ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });

            jQuery('body').on( 'click', function() {
                jQuery( '.wpfm_error_tip' ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });

            jQuery('body').on( 'blur', '#_food_sale_price', function() {
                jQuery( '.wpfm_error_tip' ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });
            jQuery('body').on( 'keyup', '#_food_sale_price', function(s, l) {
                var sale_price_field = jQuery( this ), regular_price_field;
                regular_price_field = jQuery( '#_food_price' );

                var sale_price    = parseFloat(
                    window.accounting.unformat( sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat( regular_price_field.val())
                );

                if ( sale_price >= regular_price ) {
                    jQuery( document.body ).triggerHandler( 'wpfm_add_error_tip', [ jQuery(this), 'wpfm_sale_less_than_regular_error' ] );
                } else {
                    jQuery( document.body ).triggerHandler( 'wpfm_remove_error_tip', [ jQuery(this), 'wpfm_sale_less_than_regular_error' ] );
                }
            });
            jQuery('body').on( 'change', '#_food_sale_price', function() {
                var sale_price_field = jQuery( this ), regular_price_field;
                regular_price_field = jQuery( '#_food_price' );

                var sale_price    = parseFloat(
                    window.accounting.unformat( sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat( regular_price_field.val())
                );

                if ( sale_price >= regular_price ) {
                    jQuery( this ).val( '' );
                }
            });

            //jQuery('body').on('change', 'input[name^="_option_enable_desc"]', WPFMFront.actions.changeFieldDescription);

            //jQuery('body').on('keyup', '.wpfm-form-group.option-desc-common textarea', WPFMFront.actions.keyupTextareaDesc);
            
            jQuery(document).on("click", ".wpfm-add-row", WPFMFront.actions.addElementRow)
            jQuery(document).on("click", ".option-delete-btn", WPFMFront.actions.removeAttributesOptions)
            jQuery(document).on("click", ".wpfm-delete-btn", WPFMFront.actions.removeAttributes)
            jQuery('#wpfm-add-new-option').on('click', WPFMFront.actions.addNewOption);
            jQuery('body').on('change', 'input[name^="option_name"]', WPFMFront.actions.updateOptionTitle);

            jQuery(document).on("click", ".wpfm-togglediv", function(e){
                var row_count = jQuery(this).data('row-count');
                var menuItem = jQuery( e.currentTarget );

                if (menuItem.attr( 'aria-expanded') === 'true') {
                    jQuery('.wpfm-options-wrap.wpfm-options-box-'+row_count).removeClass("closed");
                    jQuery(this).attr( 'aria-expanded', 'false');
                } else {
                    jQuery('.wpfm-options-wrap.wpfm-options-box-'+row_count).addClass("closed");
                    jQuery(this).attr( 'aria-expanded', 'true');
                }

                jQuery(this).parents('.postbox').find('.wpfm-options-box').slideToggle("slow");
            });

            jQuery(".container .wpfm-form-wrapper table.widefat tbody").sortable({
                connectWith: ".container .wpfm-form-wrapper table.widefat tbody",
                items:"tr",
                axis:"y",
                helper:function(t,i){
                    return i.children().children().each((function(){
                        jQuery(this).width(jQuery(this).width())
                    }
                    )),i
                },
                placeholder:"placeholder",
                opacity:.65,
                update: function (event, ui) {
                    var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                    jQuery('.container .wpfm-form-group.fieldset_option_options_'+repeater_row_count+' table.widefat tbody tr').each(function (i) {
                        var humanNum = i + 1;
                        //var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                        jQuery(this).children('td:nth-child(2)').html(humanNum);
                        jQuery(this).attr('class', 'option-tr-'+humanNum);
                        jQuery(this).children('.option-value-class').val(humanNum);
                        jQuery(this).children('td').children('.opt_name').attr('name', repeater_row_count+'_option_value_name_'+humanNum);
                        jQuery(this).children('td').children('.opt_default').attr('name', repeater_row_count+'_option_value_default_'+humanNum);
                        jQuery(this).children('td').children('.opt_price').attr('name', repeater_row_count+'_option_value_price_'+humanNum);
                        jQuery(this).children('td').children('.opt_select').attr('name', repeater_row_count+'_option_value_price_type_'+humanNum);
                        jQuery(this).children('td').children('.option-delete-btn').attr('data-id', humanNum);
                    });
                }
            }).disableSelection();


            /* General tab - Regular and Sale price validation */
            jQuery('body').on( 'wpfm_add_error_tip', function( e, element, error_type ) {
                var offset = element.position();

                if ( element.parent().find( '.wpfm_error_tip' ).length === 0 ) {
                    element.after( '<div class="wpfm_error_tip ' + error_type + '">' + wpfm_accounting_params[error_type] + '</div>' );
                    element.parent().find( '.wpfm_error_tip' )
                        .css( 'left', offset.left + element.width() - ( element.width() / 2 ) - ( jQuery( '.wpfm_error_tip' ).width() / 2 ) )
                        .css( 'top', offset.top + element.height() )
                        .fadeIn( '100' );
                }
            });
            jQuery('body').on( 'wpfm_remove_error_tip', function( e, element, error_type ) {
                element.parent().find( '.wpfm_error_tip.' + error_type ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });

            jQuery('body').on( 'click', function() {
                jQuery( '.wpfm_error_tip' ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });

            jQuery('body').on( 'blur', '#food_sale_price', function() {
                jQuery( '.wpfm_error_tip' ).fadeOut( '100', function() { jQuery( this ).remove(); } );
            });
            jQuery('body').on( 'keyup', '#food_sale_price', function(s, l) {
                var sale_price_field = jQuery( this ), regular_price_field;
                regular_price_field = jQuery( '#food_price' );

                var sale_price    = parseFloat(
                    window.accounting.unformat( sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat( regular_price_field.val())
                );

                if ( sale_price >= regular_price ) {
                    jQuery( document.body ).triggerHandler( 'wpfm_add_error_tip', [ jQuery(this), 'wpfm_sale_less_than_regular_error' ] );
                } else {
                    jQuery( document.body ).triggerHandler( 'wpfm_remove_error_tip', [ jQuery(this), 'wpfm_sale_less_than_regular_error' ] );
                }
            });
            jQuery('body').on( 'change', '#food_sale_price', function() {
                var sale_price_field = jQuery( this ), regular_price_field;
                regular_price_field = jQuery( '#food_price' );

                var sale_price    = parseFloat(
                    window.accounting.unformat( sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat( regular_price_field.val())
                );

                if ( sale_price >= regular_price ) {
                    jQuery( this ).val( '' );
                }
            });

	   },

    	actions :
    	{
            removeFoodItem: function(event){
    	   	   jQuery(this).parents('li').remove();
            },

            /*changeFieldDescription:function(event){
                var row_count = jQuery(this).closest('.postbox').children('.repeated-options').val();
                jQuery(this).closest(".fieldset_option_enable_desc_"+row_count).next().slideToggle(this.checked);
                jQuery(this).closest(".fieldset_option_enable_desc_"+row_count).next().children(".field").children("textarea").val("Please enter a Description of field.");
            },

            keyupTextareaDesc:function(event){
                var textarea_value = jQuery(this).val();
                var row_count = jQuery(this).closest('.postbox').children('.repeated-options').val();
                if(textarea_value.length == 0) {
                    jQuery(this).closest(".fieldset_option_description_"+row_count).prev().children(".wpfm-input-field").children(".wpfm-field-switch").children('input[type="checkbox"]').removeAttr("checked");
                    jQuery(this).closest(".fieldset_option_description_"+row_count).slideUp();
                } else {
                    jQuery(this).closest(".fieldset_option_description_"+row_count).prev().children(".wpfm-input-field").children(".wpfm-field-switch").children('input[type="checkbox"]').prop("checked", true);
                }
            },*/

            viewmoreFoodFields: function(event){
                jQuery(this).prev().slideToggle();
                jQuery(this).fadeOut();
                jQuery(this).next().fadeIn();
            },

            viewlessFoodFields: function(event){
                jQuery(this).prev().prev().slideToggle();
                jQuery(this).fadeOut();
                jQuery(this).prev().fadeIn();
            },

            addElementRow:function(event){
                var total_rows = 0;
                total_rows = jQuery(this).parents('table').find('tbody tr').length;
                total_rows = total_rows + 1;
                //var row_count2 = jQuery(".wpfm-options-wrapper div.wpfm-options-wrap").length;
                var row_count2 = jQuery(this).closest('.postbox').children('.repeated-options').val();
                
                var html = jQuery(this).data('row').replace( /%%repeated-option-index3%%/g, total_rows ).replace( /%%repeated-option-index2%%/g, row_count2 );
                html.replace('value="1"',total_rows);
                jQuery(this).parents('table').find('tbody').append(html);
            },

            removeAttributesOptions: function(event){
                var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                jQuery('.wpfm-options-box-'+repeater_row_count+' tbody tr.option-tr-'+jQuery(this).data('id')).remove();
            },

            removeAttributes: function(event){
                jQuery('.wpfm-options-box-'+jQuery(this).data('id')).remove();
            },

            updateOptionTitle:function(event){
                jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text(this.value);

                var option_key = this.value.replace(/\s/g,'_').toLowerCase();
                jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val(option_key);

                if(this.value == ''){
                    jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text("Option Key");
                    jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val("option_key");
                }
            },
            addNewOption:function(event){
                var max_index = 0;
                    if(jQuery('.wpfm-form-wrapper').find('div.wpfm-options-wrap').length){
                        jQuery('.wpfm-form-wrapper').find('div.wpfm-options-wrap').each(function(){
                        max_index ++ ;
                        });
                    }
                max_index = max_index + 1;
                var html = jQuery(this).data('row').replace( /%%repeated-option-index%%/g, max_index );

                //Old before() function - Developer Kushang
                //jQuery('.wpfm-options-wrapper .wpfm-actions').before( html );

                //New Before() function - Developer kushang
                jQuery('.wpfm-form-wrapper .wpfm-actions').before(html);

                jQuery(".container .wpfm-form-wrapper table.widefat tbody").sortable({
                    connectWith: ".container .wpfm-form-wrapper table.widefat tbody",
                    items:"tr",
                    axis:"y",
                    helper:function(t,i){
                        return i.children().children().each((function(){
                            jQuery(this).width(jQuery(this).width())
                        }
                        )),i
                    },
                    placeholder:"placeholder",
                    opacity:.65,
                    update: function (event, ui) {
                        var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                        jQuery('.container .wpfm-form-group.fieldset_option_options_'+repeater_row_count+' table.widefat tbody tr').each(function (i) {
                            var humanNum = i + 1;
                            //var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                            jQuery(this).children('td:nth-child(2)').html(humanNum);
                            jQuery(this).attr('class', 'option-tr-'+humanNum);
                            jQuery(this).children('.option-value-class').val(humanNum);
                            jQuery(this).children('td').children('.opt_name').attr('name', repeater_row_count+'_option_value_name_'+humanNum);
                            jQuery(this).children('td').children('.opt_default').attr('name', repeater_row_count+'_option_value_default_'+humanNum);
                            jQuery(this).children('td').children('.opt_price').attr('name', repeater_row_count+'_option_value_price_'+humanNum);
                            jQuery(this).children('td').children('.opt_select').attr('name', repeater_row_count+'_option_value_price_type_'+humanNum);
                            jQuery(this).children('td').children('.option-delete-btn').attr('data-id', humanNum);
                        });
                    }
                }).disableSelection();
                //jQuery(this).closest(".wpfm-actions").prev().find(".option-desc-common").hide();
                if (jQuery('input[data-picker="datepicker"]').length > 0) {
                    if (wp_food_manager_food_submission.show_past_date) {
                        jQuery('input[data-picker="datepicker"]').datepicker({
                            dateFormat: wp_food_manager_food_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_food_submission.start_of_week,
                            monthNames: wp_food_manager_food_submission.monthNames,
                        });
                    }
                    else {
                        jQuery('input[data-picker="datepicker"]').datepicker({
                            minDate: 0,
                            dateFormat: wp_food_manager_food_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_food_submission.start_of_week,
                            monthNames: wp_food_manager_food_submission.monthNames,
                        });
                    }
                }
                jQuery(".food-manager-multiselect").chosen({search_contains:!0})
            },
    	},
    }
};

WPFMFront = WPFMFront();
jQuery(document).ready(function($) 
{
  WPFMFront.init();
});