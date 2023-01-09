var ContentFoodListing= function () {
    /// <summary>Constructor function of the food ContentFoodListing class.</summary>
    /// <returns type="ContentFoodListing" />      
    return {
	    ///<summary>
        ///Initializes the content food listing.  
        ///</summary>     
        ///<returns type="initialization settings" />   
        /// <since>1.0.0</since> 
        init: function() 
        {   
	           WPFM_Common.logInfo("ContentFoodListing.init..."); 
	           
			   jQuery(document).delegate('#wpfm-food-list-layout','click', ContentFoodListing.actions.lineLayoutIconClick);
			   jQuery(document).delegate('#wpfm-food-box-layout','click', ContentFoodListing.actions.boxLayoutIconClick);
			   
			   //check if default layout is set or icon are on the page to load according to localstorage
			   if(jQuery('.wpfm-food-list-layout').length > 0 || jQuery('.wpfm-food-box-layout').length > 0) {
				   //With show_pagination attribute in shortcodes. e.g [foods per_page="10" show_pagination="true"]
				   //Check when user has changed page using pagination and then need to keep current selected layout
		           //When layout is box and user changed page using pagination then need to show line layout instead of line layout  
		           if(localStorage.getItem("layout")=="line-layout" ){    
		                    jQuery(".wpfm-food-box-col").show();
		                    jQuery('.wpfm-food-box-layout').removeClass('wpfm-active-layout');
		                    jQuery('.wpfm-food-list-layout').addClass('wpfm-active-layout');
		                    
		                    if(jQuery(".wpfm-food-listings").hasClass('wpfm-row'))
		                       jQuery(".wpfm-food-listings").removeClass('wpfm-row');
	
		                   jQuery(".wpfm-food-listings").removeClass("wpfm-food-listing-box-view");
		                   jQuery(".wpfm-food-listings").addClass("wpfm-food-listing-list-view");
		   
		              } 
		              else if(localStorage.getItem("layout")=="calendar-layout" ){      
		                jQuery(".wpfm-food-box-col").hide();
		                jQuery('.wpfm-food-list-layout').removeClass('wpfm-active-layout');
		                jQuery('.wpfm-food-box-layout').removeClass('wpfm-active-layout');
		                jQuery('.wpfm-food-calendar-layout').addClass('wpfm-active-layout');
	
		                if(!jQuery(".wpfm-food-listings").hasClass('wpfm-row'))
		                   jQuery(".wpfm-food-listings").addClass('wpfm-row');
	
		               jQuery(".wpfm-food-listings").removeClass("wpfm-food-listing-list-view");
		               jQuery(".wpfm-food-listings").removeClass("wpfm-food-listing-box-view");      
		               jQuery(".wpfm-food-listings").addClass("wpfm-food-listing-calendar-view");      	                 
		              }  
		              else {   
		                jQuery(".wpfm-food-box-col").show();
		                jQuery('.wpfm-food-list-layout').removeClass('wpfm-active-layout');
		                jQuery('.wpfm-food-box-layout').addClass('wpfm-active-layout');
	
		                if(!jQuery(".wpfm-food-listings").hasClass('wpfm-row'))
		                   jQuery(".wpfm-food-listings").addClass('wpfm-row');
	
		               jQuery(".wpfm-food-listings").removeClass("wpfm-food-listing-list-view");
		               jQuery(".wpfm-food-listings").addClass("wpfm-food-listing-box-view"); 
		              }
			   	}

			   if(jQuery( 'input.date_range_picker' ).length > 0)
		     	{
		     		jQuery("input.date_range_picker").daterangepicker({
	                    datepickerOptions : {
	                        numberOfMonths : 2,
	                        minDate: null,
					        maxDate: null
	                    },
	                    initialText: food_manager_content_food_listing.i18n_dateLabel,
	                    dateFormat: 'yy-mm-dd',
	                    rangeSplitter: ' : ',
	                    presetRanges: [
	                      {
	                        text: food_manager_content_food_listing.i18n_today,
	                        dateStart: function() { return moment() },
	                        dateEnd: function() { return moment() }
	                      }, 
	                      {
	                        text: food_manager_content_food_listing.i18n_tomorrow,
	                        dateStart: function() { return moment().add('days', 1) },
	                        dateEnd: function() { return moment().add('days', 1) }
	                      },
	                      {
	                        text: food_manager_content_food_listing.i18n_thisWeek,
	                        dateStart: function() { return moment().startOf('week') },
	                        dateEnd: function() { return moment().endOf('week') }
	                      }, 
	                      {
	                        text: food_manager_content_food_listing.i18n_nextWeek,
	                        dateStart: function() { return moment().add('weeks', 1).startOf('week') },
	                        dateEnd: function() { return moment().add('weeks', 1).endOf('week') }
	                      },
	                      {
	                        text: food_manager_content_food_listing.i18n_thisMonth,
	                        dateStart: function() { return moment().startOf('month') },
	                        dateEnd: function() { return moment().endOf('month') }
	                      },
	                      {
	                        text: food_manager_content_food_listing.i18n_nextMonth,
	                        dateStart: function() { return moment().add('months', 1).startOf('month') },
	                        dateEnd: function() { return moment().add('months', 1).endOf('month') }
	                      },
	                      {
	                        text: food_manager_content_food_listing.i18n_thisYear,
	                        dateStart: function() { return moment().startOf('year') },
	                        dateEnd: function() { return moment().endOf('year') }
	                      },
	                      {
	                        text: food_manager_content_food_listing.i18n_nextYear,
	                        dateStart: function() { return moment().add('years', 1).startOf('year') },
	                        dateEnd: function() { return moment().add('years', 1).endOf('year') }
	                      },
	                    ],
	                });
		     	}
        },
        actions: 
        {
		        /// <summary>
	            /// Click on line layout.
	            /// </summary>     
	            /// <returns type="foods listing view" />    
	            /// <since>1.0.0</since>     
	            lineLayoutIconClick: function (food)
	            {   
	                      WPFM_Common.logInfo("ContentFoodListing.actions.lineLayoutIconClick...");   

                jQuery(this).addClass("wpfm-active-layout");
                jQuery("#wpfm-food-box-layout").removeClass("wpfm-active-layout");
                
                
                jQuery(".wpfm-food-box-col").show();
                

                jQuery(".wpfm-food-listings").removeClass("wpfm-row wpfm-food-listing-box-view");
            
                jQuery(".wpfm-food-listings").addClass("wpfm-food-listing-list-view");
                    	            
    		      localStorage.setItem("layout", "line-layout");
    		      food.preventDefault ();
	            },
	            
	            /// <summary>
	            /// Click on box layout.
	            /// </summary>     
	            /// <returns type="foods listing view" />    
	            /// <since>1.0.0</since>     
	            boxLayoutIconClick: function (food)
	            {                 	       
	                WPFM_Common.logInfo("ContentFoodListing.actions.boxLayoutIconClick...");    
                    jQuery(this).addClass("wpfm-active-layout");

                    if(jQuery("#wpfm-food-list-layout").hasClass("wpfm-active-layout"))
                        jQuery("#wpfm-food-list-layout").removeClass("wpfm-active-layout");
                        
                    jQuery(".wpfm-food-box-col").show();
                    //jQuery("#calendar-layout-view-container").hide();

                    jQuery(".wpfm-food-listings").removeClass("wpfm-food-listing-list-view");
                   // jQuery(".wpfm-food-listings").addClass("wpfm-row wpfm-food-listing-box-view");
                    
                     jQuery(".wpfm-food-listings").addClass('wpfm-row wpfm-food-listing-box-view');
                    // jQuery(".wpfm-food-listings").addClass('wpfm-food-listing-box-view');
                    
    		       localStorage.setItem("layout", "box-layout"); 
    		       food.preventDefault ();
	            }		   
        }

    } //enf of return

}; //end of class

ContentFoodListing= ContentFoodListing();
jQuery(document).ready(function($) 
{
   ContentFoodListing.init();
});
