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
		        //Bind on click event of the settings section
			     jQuery(".wpfm-tabs li a").on('click',WPFMAdmin.actions.tabClick);
		  	 //show by default first Event Listings Settings Tab
            jQuery('.wpfm-tabs li a:first').click();	

            jQuery('#wpfm-admin-add-food').on('click',WPFMAdmin.actions.updateFoodinMenu);	

            //use body to call after dom update
            jQuery("body").on('click','a.wpfm-food-item-remove',WPFMAdmin.actions.removeFoodItem);						

            //sortable 
            jQuery('.wpfm-admin-food-menu-items ul.wpfm-food-menu').sortable();

            //file upload
            jQuery('body').on('click', '.wp_food_manager_upload_file_button', WPFMAdmin.fileUpload.addFile);
            jQuery(".wp_food_manager_add_another_file_button").on('click', WPFMAdmin.fileUpload.addAnotherFile);
            
            //food extra options
            jQuery('#wpfm-add-new-option').on('click', WPFMAdmin.actions.addNewOption);
            jQuery('.wpfm-togglediv').on('click', function(){
            
                var row_count = jQuery(this).data('row-count');
                jQuery(this).parents('.postbox').find('.wpfm-options-box-'+row_count).slideToggle("slow");
            });

            jQuery('input[name^="_option_name"]').on('change', WPFMAdmin.actions.updateOptionTitle);
            jQuery('body').on('change', 'select[name^="_option_type"]' ,WPFMAdmin.actions.changeFieldType);

            //find all the options and hide price
            jQuery('select[name^="_option_price_type"]').parent('.wpfm-admin-postbox-form-field').hide();
            jQuery('input[name^="_option_price"]').parent('.wpfm-admin-postbox-form-field').hide();

            //jQuery(".wpfm-admin-postbox-form-field._option_price_type").hide();
            //jQuery(".wpfm-admin-postbox-form-field._option_price").hide();

            jQuery(".wpfm-add-row").on('click',WPFMAdmin.actions.addElementRow)
            jQuery(".wpfm-delete-btn").on('click',WPFMAdmin.actions.removeAttributes)



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
	   	if(category_id.length > 0)
	   	jQuery.ajax({
                    type: 'POST',
                    url: wpfm_admin.ajax_url,
                    data: {
                    	action: 'wpfm_get_food_listings_by_category_id',
                    	category_id: category_id,
                    },
                    success: function(response) {
                    	jQuery('ul.wpfm-food-menu').append(response.html);

                    },
                    error: function(result) {}
                });

	   },

	   /// <summary>
	   /// Remove food item from food menu
	   /// </summary>
	   /// <param name="parent" type="Event"></param> 
	   /// <returns type="actions" />     
	   /// <since>1.0.0</since>
	   removeFoodItem: function(event){
	   		jQuery(this).parents('li').remove();
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

                jQuery('.wpfm-options-wrapper .wpfm-actions').before( html );
       },

        /// <summary>
       /// updateOptionTitle
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       updateOptionTitle:function(event){

         jQuery(this).parents('.postbox').find('.attribute_name').text(this.value);

         //convert text into key
         var option_key = this.value.replace(/\s/g,'_').toLowerCase();
         jQuery(this).parents('.postbox').find('span.attribute_key input').attr('value','_'+option_key);
       },

       /// <summary>
       /// changeFieldType
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       changeFieldType:function(event){
        console.log('change field type');
         var field_type = this.value;
         if(jQuery.inArray(field_type, ["checkbox","select","radio"]) !== -1){
            jQuery(this).parents('.postbox').find(".wpfm-admin-options-table").show();
 
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price_type").hide();
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price").hide();
         }
         else{
             jQuery(this).parents('.postbox').find(".wpfm-admin-options-table").hide();

            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price_type").show();
            jQuery(this).parents('.postbox').find(".wpfm-admin-postbox-form-field._option_price").show();
        }
       },
        /// <summary>
       /// changeFieldType
       /// </summary>
       /// <param name="parent" type="Event"></param> 
       /// <returns type="actions" />     
       /// <since>1.0.0</since>
       addElementRow:function(event){
        

        var total_rows = 0;
        total_rows = jQuery(this).parents('table').find('tbody tr').length;
                total_rows = total_rows + 1;
                var html = jQuery(this).parents('table').find('tbody tr:first').html().replace( /1/g, +total_rows );
                html.replace('value="1"',total_rows);
                jQuery(this).parents('table').find('tbody').append("<tr>"+ html +"</tr>");


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
        console.log(jQuery(this).data('id'));
      
        jQuery('.wpfm-options-box-'+jQuery(this).data('id')).remove();
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
                        file_target_wrapper = jQuery(this).closest('.file_url');
                        file_target_input = file_target_wrapper.find('input');
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
                        jQuery(this).before('<span class="file_url"><input type="text" name="' + field_name + '[]" placeholder="' + field_placeholder + '" /><button class="button button-small wp_event_manager_upload_file_button" data-uploader_button_text="' + button_text + '">' + button + '</button></span>');
                    }
                }
    } //enf of return
}; //end of class

WPFMAdmin = WPFMAdmin();
jQuery(document).ready(function($) 
{
  WPFMAdmin.init();
});
