var WPFM_FoodDashboard = function () {
	/// <summary>Constructor function of the food WPFM_FoodDashboard class.</summary>
	/// <returns type="Home" />      
	return {
		/// <summary>
		/// Initializes the food dashboard.  
		/// </summary>     
		/// <returns type="initialization settings" />   
		/// <since>1.0.0</since> 
		init: function () {
			if (jQuery('.food-dashboard-action-delete').length > 0 && jQuery('td .wpfm-dboard-food-action').length == 0) {
				jQuery('.food-dashboard-action-delete').css({ 'cursor': 'pointer' });
				//for delete food confirmation dialog / tooltip .
				jQuery('.food-dashboard-action-delete').on('click', WPFM_FoodDashboard.confirmation.showDialog);
			}
			// For Dashboard Menu Toggle.
			if (jQuery('.wpfm-main-vmenu-dashboard-sub-menu .wpfm-main-vmenu-dashboard-link').length > 0) {
				jQuery('.wpfm-main-vmenu-dashboard-sub-menu .wpfm-main-vmenu-dashboard-submenu-ul').hide();
				jQuery('.wpfm-main-vmenu-dashboard-sub-menu .wpfm-main-vmenu-dashboard-link').on('click', WPFM_FoodDashboard.actions.openSubmenu);
			}
			// For Active Dashboard Menu Open.
			if (jQuery('.wpfm-main-vmenu-dashboard-sub-menu .wpfm-main-vmenu-dashboard-link-active').length > 0) {
				jQuery('.wpfm-main-vmenu-dashboard-sub-menu .wpfm-main-vmenu-dashboard-link-active').trigger('click');
			}
			// For food List Toggle.
			if (jQuery('#wpfm-dashboard-food-list-wrapper .wpfm-food-dashboard-information-toggle').length > 0) {
				jQuery('#wpfm-dashboard-food-list-wrapper .wpfm-food-dashboard-information-toggle').hide();
				jQuery('#wpfm-dashboard-food-list-wrapper')
					.on('click', '.food-dashboard-action-details', function () {
						jQuery(this).closest('div.wpfm-dashboard-food-list').find('section:not(.wpfm-food-dashboard-information-toggle)').slideUp();
						jQuery(this).closest('div.wpfm-dashboard-food-list').find('section.wpfm-food-dashboard-information-toggle').slideToggle();
						return false;
					})
					.on('click', 'a.hide_section', function () {
						jQuery(this).closest('section').slideUp();
						return false;
					});
			}
			// For Food Filter Toggle.
			if (jQuery('.wpfm-dashboard-main-header .wpfm-food-dashboard-filter-toggle').length > 0) {
				jQuery('.wpfm-dashboard-main-header .wpfm-food-dashboard-filter-toggle').hide();
				jQuery('.wpfm-dashboard-main-header .wpfm-dashboard-main-filter')
					.on('click', '.wpfm-dashboard-food-filter', function () {
						jQuery(this).closest('div.wpfm-dashboard-main-header').find('form:not(.wpfm-food-dashboard-filter-toggle)').slideUp();
						jQuery(this).closest('div.wpfm-dashboard-main-header').find('form.wpfm-food-dashboard-filter-toggle').slideToggle();
						if (jQuery('.wpfm-dashboard-main-header .wpfm-food-dashboard-filter-toggle').hasClass('wpfm-d-block')) {
							jQuery('.wpfm-dashboard-main-header .wpfm-food-dashboard-filter-toggle').removeClass('wpfm-d-block');
						}
						return false;
					})
			}
		},
		confirmation: {
			/// <summary>
			/// Show bootstrap third party confirmation dialog when click on 'Delete' options on food dashboard page where show delete food option.	     
			/// </summary>
			/// <param name="parent" type="assign"></param>           
			/// <returns type="actions" />     
			/// <since>1.0.0</since>       
			showDialog: function (event) {
				return confirm(food_manager_food_dashboard.i18n_confirm_delete);
				event.preventDefault();
			},
		},//end of comfirmation.
		actions: {
			openSubmenu: function (event) {
				event.stopPropagation();
				var parentLI = jQuery(this).closest("li");
				var other = parentLI.siblings();
				var myUL = parentLI.find("ul");
				var myToggle = jQuery(this).find(".wpfm-main-vmenu-caret");
				other.find("ul").slideUp("100");
				other.find("a i.wpfm-main-vmenu-caret").removeClass("wpfm-main-vmenu-caret-down").addClass("wpfm-main-vmenu-caret-up");
				myUL.slideToggle("100");
				myToggle.toggleClass("wpfm-main-vmenu-caret-up wpfm-main-vmenu-caret-down");
			},
		}, //end of actions.
	} //enf of return.	
}; //end of class.

WPFM_FoodDashboard = WPFM_FoodDashboard();
jQuery(document).ready(function ($) {
	WPFM_FoodDashboard.init();
});