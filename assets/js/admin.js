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
	   		console.log('remove food item');
	   }
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
