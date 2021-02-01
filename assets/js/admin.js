var WPFMAdmin= function () {
    /// <summary>Constructor function of the event settings class.</summary>
    /// <returns type="Settings" />   
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
	}
    } //enf of return
}; //end of class

WPFMAdmin = WPFMAdmin();
jQuery(document).ready(function($) 
{
  WPFMAdmin.init();
});
