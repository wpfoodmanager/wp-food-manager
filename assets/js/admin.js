var WPFMAdmin= function () {
    /// <summary>Constructor function of the event settings class.</summary>
    /// <returns type="WPFMAdmin" />   
    // Uploading files

    var file_frame;
    var file_target_input;
    var file_target_wrapper;
    return {
	    ///<summary>
        ///Initializes the AdminSettings.  
        ///</summary>     
        ///<returns type="initialization AdminSettings" />   
        /// <since>1.0.0</since> 
        init: function() 
        {

            jQuery('div.food tr.wpfm-admin-common td.field-type select option').each(function() {
                if ( jQuery(this).val() == 'term-checklist' || jQuery(this).val() == 'term-multiselect' || jQuery(this).val() == 'term-select') {
                    jQuery(this).remove();
                }
            });

            //Tooltips
            jQuery(".tips, .help_tip").tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });

            //jQuery(".option-desc-common").hide();
            /*jQuery("body").ready(function(){
                jQuery("select .wpfm-opt-val").each(function(){
                    var selected_val = jQuery(this).val();
                    if(selected_val == 'options'){
                        jQuery(".wp-food-manager-food-form-field-editor.extra_options td.field-type .field_type .options").attr("disabled","disabled");
                        jQuery(this).prop("disabled", false);
                    }
                });
            });*/
            jQuery("body").on("click",".food-manager-remove-uploaded-file", function(){
                return jQuery(this).closest(".food-manager-uploaded-file").remove();
            });
            
            //if field type is date then load datepicker
            if (jQuery('input[data-picker="datepicker"]').length > 0)
            {
                if (wpfm_admin.show_past_date)
                {
                    jQuery('input[data-picker="datepicker"]').datepicker({
                        dateFormat: wpfm_admin.i18n_datepicker_format,
                        firstDay: wpfm_admin.start_of_week
                    });
                }
                else
                {
                    jQuery('input[data-picker="datepicker"]').datepicker({
                        minDate: 0, 
                        dateFormat: wpfm_admin.i18n_datepicker_format,
                        firstDay: wpfm_admin.start_of_week
                    });
                }
            }

            //Bind on click event of the settings section
			jQuery(".wpfm-tabs li a").on('click',WPFMAdmin.actions.tabClick);
		  	//show by default first Event Listings Settings Tab
            jQuery('.wpfm-tabs li a:first').click();	

            //jQuery(document).on('click', '#wpfm-admin-add-food', WPFMAdmin.actions.updateFoodinMenu); 
            jQuery(document).on('change', '#wpfm-admin-food-selection', WPFMAdmin.actions.updateFoodinMenu); 

            //use body to call after dom update
            jQuery("body").on('click','a.wpfm-food-item-remove',WPFMAdmin.actions.removeFoodItem);

            //jQuery("body").on('change','.wp-food-manager-food-form-field-editor.extra_options .field-type select.field_type',WPFMAdmin.actions.removeFoodSelectionVal);

            //sortable 
            jQuery('.wpfm-admin-food-menu-items ul.wpfm-food-menu').sortable();

            //file upload
            jQuery('body').on('click', '.wp_food_manager_upload_file_button_multiple', WPFMAdmin.fileUpload.multipleFile);
            jQuery('body').on('click', '.wp_food_manager_upload_file_button', WPFMAdmin.fileUpload.addFile);
            jQuery(".wp_food_manager_add_another_file_button").on('click', WPFMAdmin.fileUpload.addAnotherFile);
            
            //food extra options
            jQuery('#wpfm-add-new-option').on('click', WPFMAdmin.actions.addNewOption);
            //jQuery('.wpfm-togglediv').each(function(e){
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

                    jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content').slideToggle("slow");
                    /*if(jQuery('.wpfm-metabox-content').hasClass('.wpfm-options-box-'+row_count)){
                    } else {
                        jQuery(this).parents('.postbox').find('.wpfm-metabox-content.wpfm-options-box').slideToggle("slow");
                    }*/
                });
            //});

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

            /*For Food menu icon search*/
            jQuery('body').on("keyup", "#wpfm_icon_search", (function() {
                jQuery(this).next().show();
                var t = jQuery(this),
                    i = t.parents("div.inside").find(".wpfm-font-awesome-class .sub-font-icon, .wpfm-food-font-icon-class .sub-font-icon"),
                    a = new RegExp(t.val(), "gi");
                a ? i.each((function() {
                    var t = jQuery(this);
                    t.find("label").text().match(a) ? t.show() : t.hide()
                })) : item.show()
                if(i.find("label").text().match(a) === null){
                    jQuery(".no-radio-icons").show();
                } else {
                    jQuery(".no-radio-icons").hide();
                }
            }));

            /*For Clear food icon search text*/
            jQuery('body').on("click", "span.wpfm-searh-clear", function() {
                jQuery(this).prev().val("");
                jQuery(this).hide();
                jQuery("div.inside").find(".wpfm-font-awesome-class .sub-font-icon").show();
                jQuery("div.inside").find(".wpfm-food-font-icon-class .sub-font-icon").show();
                jQuery(".no-radio-icons").hide();
            });

            /*For Ingredient and Nutrition tab*/
            jQuery('body').on("keyup", ".wpfm-item-search input[type=text]", (function() {
                var t = jQuery(this),
                    i = t.parents("ul.wpfm-available-list").find("li.available-item"),
                    a = new RegExp(t.val(), "gi");
                a ? i.each((function() {
                    var t = jQuery(this);
                    t.find("label").text().match(a) ? t.show() : t.hide()
                })) : item.show()
            }));
            jQuery("#wpfm-ingredient-container .wpfm-sortable-list").sortable({
                connectWith: ".wpfm-sortable-list",
                update: function(t, i) {
                    var a = jQuery(this),
                        n = jQuery(i.item);
                    console.log(this);
                    if (a.hasClass("wpfm-active-list")) {
                        if (n.find("input").length < 1) {
                            var r = n.data("id"),
                                o = wpfm_var.units,
                                l = "";
                            o && jQuery.map(o, (function(e, t) {
                                l = l + "<option value='" + t + "'>" + e + "</option>"
                            }));
                            var s = "<input type='number' name='_ingredient[" + r + "][value]'><select name='_ingredient[" + r + "][unit_id]'><option value=''>Unit</option>" + l + "</select>";
                            n.find(".wpfm-sortable-item-values").html(s), n.removeClass("available-item").addClass("active-item")
                        }
                    } else n.find(".wpfm-sortable-item-values").html(""), n.removeClass("active-item").addClass("available-item")
                }
            }).disableSelection();

            jQuery(".post-type-food_manager table.posts #the-list").length && jQuery(".post-type-food_manager table.posts #the-list").sortable({
                items: "tr",
                axis: "y",
                helper: function(t, i) {
                    return i.children().children().each((function() {
                        jQuery(this).width(jQuery(this).width())
                    })), i
                },
                placeholder: "placeholder",
                opacity: .65,
                update: function(t, i) {
                    var a = jQuery("#the-list").sortable("serialize");
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: a+"&action=wpfm-logo-update-menu-order",
                        beforeSend: function() {
                            jQuery("body").append(jQuery("<div id='wpfm-loading'><span class='wpfm-loading'>Updating ...</span></div>"))
                        },
                        success: function(response) {
                            jQuery("#wpfm-loading").remove()
                        },
                    })
                }
            });

            jQuery(".post-type-food_manager .wpfm-admin-options-table table.widefat tbody").sortable({
                connectWith: ".post-type-food_manager .wpfm-admin-options-table table.widefat tbody",
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
                    jQuery('.post-type-food_manager .wpfm-admin-options-table._option_options_'+repeater_row_count+' table.widefat tbody tr').each(function (i) {
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

            jQuery("#wpfm-nutrition-container .wpfm-sortable-list").sortable({
                connectWith: ".wpfm-sortable-list",
                update: function(t, i) {
                    var a = jQuery(this),
                        n = jQuery(i.item);
                    if (a.hasClass("wpfm-active-list")) {
                        if (n.find("input").length < 1) {
                            var r = n.data("id"),
                                o = wpfm_var.units,
                                l = "";
                            o && jQuery.map(o, (function(e, t) {
                                l = l + "<option value='" + t + "'>" + e + "</option>"
                            }));
                            var s = "<input type='number' name='_nutrition[" + r + "][value]'><select name='_nutrition[" + r + "][unit_id]'><option value=''>Unit</option>" + l + "</select>";
                            n.find(".wpfm-sortable-item-values").html(s), n.removeClass("available-item").addClass("active-item")
                        }
                    } else n.find(".wpfm-sortable-item-values").html(""), n.removeClass("active-item").addClass("available-item")
                }
            }).disableSelection()

            //jQuery(".option-tr-1 td a.option-delete-btn").addClass("wpfm-disabled-link");
            /*jQuery(".wpfm-togglediv").each(function(){
                var row_count = jQuery(this).data('row-count');
                var html2 = jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_name').attr('name');
                var h4 = html2.replace( /%%repeated-option-index2%%/g, row_count );
                jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_name').attr('name', h4);

                var html3 = jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_price').attr('name');
                var h5 = html3.replace( /%%repeated-option-index2%%/g, row_count );
                jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_price').attr('name', h5);

                var html4 = jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_default').attr('name');
                var h6 = html4.replace( /%%repeated-option-index2%%/g, row_count );
                jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_default').attr('name', h6);

                var html5 = jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_select').attr('name');
                var h7 = html5.replace( /%%repeated-option-index2%%/g, row_count );
                jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count+' .wpfm-metabox-content div.wpfm-admin-options-table tbody tr td .opt_select').attr('name', h7);
            });*/

            //jQuery('.post-new-php #wpfm-admin-food-selection').prepend('<option value="" selected>Choose a Category</option>');

            jQuery('body').on('change', 'input[name^="option_name"]', WPFMAdmin.actions.updateOptionTitle);
            jQuery('body').on('change', 'select[name^="_option_type"]', WPFMAdmin.actions.changeFieldType);

            //jQuery('body').on('change', 'input[name^="_option_enable_desc"]', WPFMAdmin.actions.changeFieldDescription);

            //jQuery('body').on('keyup', '.wpfm-input-field textarea', WPFMAdmin.actions.keyupTextareaDesc);
            
            //find all the options and hide price
            /*jQuery('select[name^="_option_price_type"]').parent('.wpfm-admin-postbox-form-field').hide();
            jQuery('input[name^="_option_price"]').parent('.wpfm-admin-postbox-form-field').hide();*/

            //jQuery(".wpfm-admin-postbox-form-field._option_price_type").hide();
            //jQuery(".wpfm-admin-postbox-form-field._option_price").hide();

            jQuery(document).on("click", ".wpfm-add-row", WPFMAdmin.actions.addElementRow)
            jQuery(document).on("click", ".wpfm-delete-btn", WPFMAdmin.actions.removeAttributes)
            jQuery(document).on("click", ".option-delete-btn", WPFMAdmin.actions.removeAttributesOptions)

	   },

	actions :
	{
	   /// <summary>
	   /// Click on tab food manager genera or other food tab.     
	   /// </summary>
	   /// <param name="parent" type="Food"></param>    
	   /// <returns type="actions" />
	   /// <since>1.0.0</since>    
	   tabClick: function(event) 
	   {                   
	   	event.preventDefault();
	   	jQuery('.wpfm_panel').hide();
		jQuery('.nav-tab-active').removeClass('nav-tab-active');
		jQuery( jQuery(this).attr('href') ).show();
		jQuery(this).addClass('nav-tab-active');
		var option= jQuery( "#setting-event_manager_submission_expire_options:last option:selected" ).val();	
		if ( option =='days' ) 
		   jQuery('#setting-event_manager_submission_duration').closest('tr').show();
		else
		   jQuery('#setting-event_manager_submission_duration').closest('tr').hide();
		return false;
	   }, 

	   
	   /// <summary>
	   /// Click on category dropdown to update food menu   
	   /// </summary>
	   /// <param name="parent" type="Food"></param>    
	   /// <returns type="actions" />
	   /// <since>1.0.0</since> 
	   updateFoodinMenu: function(event){ 
	   	
	   	var category_id = jQuery('.wpfm-admin-menu-selection #wpfm-admin-food-selection').val();
	   	if(category_id.length > 0){
            jQuery.ajax({
                    type: 'POST',
                    url: wpfm_admin.ajax_url,
                    data: {
                    	action: 'wpfm_get_food_listings_by_category_id',
                    	category_id: category_id,
                    },
                    success: function(response) {
                        jQuery('ul.wpfm-food-menu li').remove();
                        if(response.html.length !== 0){
                            jQuery('ul.wpfm-food-menu').append(response.html);
                            jQuery('.no-menu-item-handle').hide();
                        } else {
                            jQuery('.no-menu-item-handle').show();
                        }
                    },
                    error: function(result) {}
                });
        } else {
            jQuery.ajax({
                    type: 'POST',
                    url: wpfm_admin.ajax_url,
                    data: {
                        action: 'wpfm_get_food_listings_by_category_id',                        
                    },
                    success: function(response) {
                        jQuery('ul.wpfm-food-menu li').remove();
                        if(response.html.length !== 0){
                            jQuery('ul.wpfm-food-menu').append(response.html);
                            jQuery('.no-menu-item-handle').hide();
                        } else {
                            jQuery('.no-menu-item-handle').show();
                        }
                    },
                    error: function(result) {}
                });
        }
	   }, 

	   /// <summary>
	   /// Remove food item from food menu
	   /// </summary>
	   /// <param name="parent" type="Event"></param> 
	   /// <returns type="actions" />     
	   /// <since>1.0.0</since>
	   removeFoodItem: function(event){
	   		jQuery(this).parents('li').remove();
            
            if(jQuery("ul.wpfm-food-menu li").length == 0){
                jQuery("#wpfm-admin-food-selection option").removeAttr('selected');
            }
	   },

       removeFoodSelectionVal: function(event){
            var selected_val = jQuery(this).val();
            if(selected_val == 'options'){
                jQuery(".wp-food-manager-food-form-field-editor.extra_options td.field-type .field_type").find(".options").attr("disabled","disabled");
                jQuery(this).find(".options").prop("disabled", false);
            }
       },

       /// <summary>
       /// add options
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
        addNewOption:function(event){
                var max_index = 0;
                    if(jQuery('.wpfm-options-wrapper').find('div.wpfm-options-wrap').length){
                        jQuery('.wpfm-options-wrapper').find('div.wpfm-options-wrap').each(function(){
                        max_index ++ ;
                        });
                    }
                max_index = max_index + 1;
                var html = jQuery(this).data('row').replace( /%%repeated-option-index%%/g, max_index );

                //Old before() function - Developer Kushang
                //jQuery('.wpfm-options-wrapper .wpfm-actions').before( html );

                //New Before() function - Developer kushang
                jQuery('#extra_options_food_data_content .wpfm-options-wrapper .wpfm-actions').before(html);

                jQuery(".post-type-food_manager .wpfm-admin-options-table table.widefat tbody").sortable({
                    connectWith: ".post-type-food_manager .wpfm-admin-options-table table.widefat tbody",
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
                        jQuery('.post-type-food_manager .wpfm-admin-options-table._option_options_'+repeater_row_count+' table.widefat tbody tr').each(function (i) {
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
                //jQuery(this).closest(".postbox").find(".option-desc-common").hide();


                //initialize WP editor on click for new WP editor's field.
                var repeater_row_counts = jQuery(this).parents(".wpfm-options-wrapper").children(".wpfm-options-wrap").length;
                fieldLabel = jQuery(this).parents(".wpfm-options-wrapper").find("p.wpfm-admin-postbox-form-field.wp-editor-field").attr("data-field-name");
                var fieldChangedLabel = fieldLabel.replace(fieldLabel.match(/(\d+)/g)[0], '');
                var editorId = fieldChangedLabel + repeater_row_counts;
                
                wp.editor.initialize(editorId, {
                    tinymce: {
                      wpautop: false,
                      textarea_rows: 8,
                      plugins : 'lists,paste,tabfocus,wplink,wordpress',
                      toolbar1: 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
                      toolbar2: '',
                    },
                    quicktags: {buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'},
                    mediaButtons: false,
                });
       },

        /// <summary>
       /// updateOptionTitle
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       updateOptionTitle:function(event){
         //console.log(jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text(this.value));
         //jQuery(this).parents('.postbox').find('.attribute_name').text(this.value);
         jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text(this.value);

         //convert text into key
         var option_key = this.value.replace(/\s/g,'_').toLowerCase();
         //jQuery(this).parents('.postbox').find('span.attribute_key input').val(option_key);
         jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val(option_key);

         if(this.value == ''){
            jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text("Option Key");
            jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val("option_key");
         }
       },

       /// <summary>
       /// changeFieldType
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       changeFieldType:function(event){
         var field_type = this.value;
         var row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
         if(jQuery.inArray(field_type, ["checkbox","select","radio"]) !== -1){
            /*jQuery(this).parents('.postbox').find(".wpfm-admin-options-table").show();
 
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price_type").hide();
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price").hide();*/
            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-options-table').show();

            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-postbox-form-field._option_price_type_'+row_count).hide();
            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-postbox-form-field._option_price_'+row_count).hide();

         }
         else{
            /*jQuery(this).parents('.postbox').find(".wpfm-admin-options-table").hide();

            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price_type").show();
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price").show();*/

            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-options-table').hide();

            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-postbox-form-field._option_price_type_'+row_count).show();
            jQuery(this).closest('.postbox').children('.wpfm-metabox-content').children('.wpfm-content').children('.wpfm-admin-postbox-form-field._option_price_'+row_count).show();            
        }
       },

        /*changeFieldDescription:function(event){
            jQuery(this).closest(".wpfm-admin-postbox-form-field").next().slideToggle(this.checked);
            jQuery(this).closest(".wpfm-admin-postbox-form-field").next().children(".wpfm-input-field").children("textarea").val("Please enter a Description of field.");
        },*/

        /*keyupTextareaDesc:function(event){
            var textarea_value = jQuery(this).val();
            
            if(textarea_value.length == 0) {
                jQuery(this).closest(".wpfm-admin-postbox-form-field").prev().children(".wpfm-input-field").children(".wpfm-field-switch").children('input[type="checkbox"]').removeAttr("checked");
                jQuery(this).closest(".wpfm-admin-postbox-form-field").slideUp();
            } else {
                jQuery(this).closest(".wpfm-admin-postbox-form-field").prev().children(".wpfm-input-field").children(".wpfm-field-switch").children('input[type="checkbox"]').prop("checked", true);
            }
        },*/

        /// <summary>
       /// addElementRow
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       addElementRow:function(event){
        
            var total_rows = 0;
            total_rows = jQuery(this).parents('table').find('tbody tr').length;
            total_rows = total_rows + 1;
            //var row_count2 = jQuery(".wpfm-options-wrapper div.wpfm-options-wrap").length;
            var row_count2 = jQuery(this).closest('.postbox').children('.repeated-options').val();
            
            var html = jQuery(this).data('row').replace( /%%repeated-option-index3%%/g, total_rows ).replace( /%%repeated-option-index2%%/g, row_count2 );
            html.replace('value="1"',total_rows);
            jQuery(this).parents('table').find('tbody').append(html);

            /*var html = jQuery(this).parents('table').find('tbody tr:first').html().replace( /1/g, +total_rows );
            jQuery(this).parents('table').find('tbody').append("<tr class='option-tr-"+total_rows+"'>"+ html +"</tr>");*/

       },


       /// <summary>
       /// add attributes fields
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       addAttributesFields: function(event){
        var attributes_box = jQuery(this).parents('.wpfm-attributes-box');

         var field_index = 0;
                    if(attributes_box){
                        jQuery(attributes_box).find('.wpfm-content').each(function(){
                        field_index ++ ;
                        });
                    }
                    
                field_index = field_index + 1

         var wpfm_content = jQuery(attributes_box).find('.wpfm-content:first').html();
         //wpfm_content.replace( /%%repeated-field-index%%/g, field_index );
         jQuery(this).parents('.wpfm-metabox-footer').before('<div class="wpfm-content" data-field="'+field_index+'">'+wpfm_content+'</div>');


        
          
           /* var max_index = 0;
                    if(jQuery('#').find('tr').length){
                        jQuery('#new_tickets_fields').find('tr').each(function(){
                        max_index ++ ;
                        });
                    }
                max_index = max_index + 1
                var html = jQuery(this).data('row').replace( /%%repeated-row-index%%/g, max_index );
                
                jQuery('.wpfm-variation-wrapper').append( html );

                event.preventDefault();*/

       },
        /// <summary>
       /// add attributes fields
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       removeAttributes: function(event){
      
        jQuery('.wpfm-options-box-'+jQuery(this).data('id')).remove();
       },

       removeAttributesOptions: function(event){
        //var row_count3 = jQuery(".wpfm-options-wrapper div.wpfm-options-wrap").length;
        var row_count3 = jQuery(this).closest('.postbox').children('.repeated-options').val();

        jQuery('.wpfm-options-box-'+row_count3+' div.wpfm-admin-options-table table tbody tr.option-tr-'+jQuery(this).data('id')).remove();
       },

        /// <summary>
       /// add attributes fields
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       removeAttributesFields: function(){
        var field_content = jQuery(this).data('id');
        jQuery(field_content).remove();
       },

       
        /// <summary>
       /// save attributes
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       saveAttributes: function(){

        jQuery('input[name="repeated-attribute-row[]"]').each(function (index, attribute) {
                var attr_val = jQuery(attribute).val();
                var attr_label = jQuery(index).closest('input[name="repeated_attribute_lable"]').val();
                console.log(attr_label);
        });


        jQuery.ajax({
                    type: 'POST',
                    url: wpfm_admin.ajax_url,
                    data: {
                        action: 'wpfm_update_food_attributes',
                        security: wpfm_admin.security,
                    },
                    success: function(response) {},
                    error: function(result) {}
                });
       
       },


	},
	fileUpload:
                {
                    /// <summary>
                    /// Upload new file from admin area.
                    /// </summary>
                    /// <param name="parent" type="Event"></param>
                    /// <returns type="actions" />
                    /// <since>1.0.0</since>
                    addFile: function (event)
                    {
                        event.preventDefault();
                        file_target_wrapper = jQuery(this).closest('.food-manager-uploaded-file');
                        file_target_input = file_target_wrapper.find('input');
                        var data_field_name = jQuery(this).parents(".wpfm-admin-postbox-form-field")[0].dataset.fieldName;
                        var image_types     = [ 'jpg', 'gif', 'png', 'jpeg', 'jpe' ];

                        file_target_wrapper_append = jQuery(this).closest('.food-manager-uploaded-file2');
                        // If the media frame already exists, reopen it.

                        if (file_frame)
                        {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: jQuery(this).data('uploader_title'),
                            button: {
                                text: jQuery(this).data('uploader_button_text'),
                            },
                            multiple: false  // Set to true to allow multiple files to be selected
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function ()
                        {
                            // We set multiple to false so only get one image from the uploader
                            attachment = file_frame.state().get('selection').first().toJSON();
                            jQuery(file_target_input).val(attachment.url);
                            jQuery(file_target_wrapper_append).find(".food-manager-uploaded-file").remove();
                            if ( jQuery.inArray( attachment.subtype, image_types ) >= 0 ) {
                                jQuery(file_target_wrapper_append).prepend("<span class='food-manager-uploaded-file'><input type='hidden' name='"+data_field_name+"' id='"+data_field_name+"' placeholder='' value='"+attachment.url+"'><span class='food-manager-uploaded-file-preview'><img src='"+attachment.url+"'><a class='food-manager-remove-uploaded-file' href='javascript:void(0);'>[remove]</a></span>");
                            } else {
                                jQuery(file_target_wrapper_append).prepend("<span class='food-manager-uploaded-file'><input type='hidden' name='"+data_field_name+"' id='"+data_field_name+"' placeholder='' value='"+attachment.url+"'><span class='food-manager-uploaded-file-preview'><span class='wpfm-icon'><strong style='display: block; padding-top: 5px;'>"+attachment.filename+"</strong><a target='_blank' href='"+attachment.url+"'><i class='wpfm-icon-download3' style='margin-right: 3px;'></i>Download</a></span><a class='food-manager-remove-uploaded-file' href='javascript:void(0);'>[remove]</a></span></span>");
                            }
                            
                        });
                        // Finally, open the modal
                        file_frame.open();
                    },
                    multipleFile: function (event)
                    {
                        event.preventDefault();
                        file_target_wrapper = jQuery(this).parent(".file_url").find('.food-manager-uploaded-file.multiple-file');
                        file_target_input = file_target_wrapper.find('input');
                        var data_field_name = jQuery(this).parents(".wpfm-admin-postbox-form-field")[0].dataset.fieldName;
                        var image_types     = [ 'jpg', 'gif', 'png', 'jpeg', 'jpe' ];

                        file_target_wrapper_apeend = jQuery(this).prev();
                        
                        // If the media frame already exists, reopen it.
                        if (file_frame)
                        {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: jQuery(this).data('uploader_title'),
                            button: {
                                text: jQuery(this).data('uploader_button_text'),
                            },
                            multiple: true  // Set to true to allow multiple files to be selected
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function ()
                        {
                            // We set multiple to false so only get one image from the uploader
                            attachment = file_frame.state().get('selection').map( 
                                function( attachment ) {
                                   attachment.toJSON();
                                   return attachment;
                                });
                            jQuery.each(attachment, function( index, attach ) {
                                jQuery(file_target_input).val(attach.attributes.url);
                                if ( jQuery.inArray( attach.attributes.subtype, image_types ) >= 0 ) {
                                    jQuery(file_target_wrapper_apeend).append("<span class='food-manager-uploaded-file multiple-file'><input type='hidden' name='"+data_field_name+"[]' placeholder='' value='"+attach.attributes.url+"'><span class='food-manager-uploaded-file-preview'><img src='"+attach.attributes.url+"'><a class='food-manager-remove-uploaded-file' href='javascript:void(0);'>[remove]</a></span>");
                                } else {
                                    jQuery(file_target_wrapper_apeend).append("<span class='food-manager-uploaded-file multiple-file'><input type='hidden' name='"+data_field_name+"[]' placeholder='' value='"+attach.attributes.url+"'><span class='food-manager-uploaded-file-preview'><span class='wpfm-icon'><strong style='display: block; padding-top: 5px;'>"+attach.attributes.filename+"</strong><a target='_blank' href='"+attach.attributes.url+"'><i class='wpfm-icon-download3' style='margin-right: 3px;'></i>Download</a></span><a class='food-manager-remove-uploaded-file' href='javascript:void(0);'>[remove]</a></span></span>");
                                }
                            });
                            
                            /*jQuery(file_target_input).val(attachment.url);
                            jQuery(file_target_input).parent().find(".food-manager-uploaded-file-preview img").attr("src", attachment.url);*/
                        });
                        // Finally, open the modal
                        file_frame.open();
                    },

                    /// <summary>
                    /// Upload new file from admi area. when admin want to add another file then admin can add new file.
                    /// </summary>
                    /// <param name="parent" type="Event"></param>
                    /// <returns type="actions" />
                    /// <since>1.0.0</since>
                    addAnotherFile: function (event)
                    {
                        event.preventDefault();
                        var wrapper = jQuery(this).closest('.form-field');
                        var field_name = jQuery(this).data('field_name');
                        var field_placeholder = jQuery(this).data('field_placeholder');
                        var button_text = jQuery(this).data('uploader_button_text');
                        var button = jQuery(this).data('uploader_button');
                        jQuery(this).before('<span class="file_url"><input type="text" name="' + field_name + '[]" placeholder="' + field_placeholder + '" /><button class="button button-small wp_food_manager_upload_file_button" data-uploader_button_text="' + button_text + '">' + button + '</button></span>');
                    }
                }
    } //enf of return
}; //end of class

WPFMAdmin = WPFMAdmin();
jQuery(document).ready(function($) 
{
  WPFMAdmin.init();
});