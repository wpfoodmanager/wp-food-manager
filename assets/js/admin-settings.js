var WPFM_AdminSettings = function () {
	/// <summary>Constructor function of the food settings class.</summary>
	/// <returns type="Settings" />
	return {
		/// <summary>
		/// Initializes the WPFM_AdminSettings.  
		/// </summary>     
		/// <returns type="initialization WPFM_AdminSettings" />   
		/// <since>1.0.0</since> 
		init: function () {
			// Bind on click food of the settings section.
			jQuery(".food-manager-settings-wrap .nav-tab-wrapper a").on('click', WPFM_AdminSettings.actions.tabClick);
			// Show by default first food Listings Settings Tab.
			jQuery('.food-manager-settings-wrap .nav-tab-wrapper a:first').click();
		},
		actions: {
			/// <summary>
			/// Click on tab either food Listings, food Submission or Pages.     
			/// </summary>
			/// <param name="parent" type="food"></param>    
			/// <returns type="actions" />
			/// <since>1.0.0</since>    
			tabClick: function (event) {
				event.preventDefault();
				jQuery('.settings_panel').hide();
				jQuery('.nav-tab-active').removeClass('nav-tab-active');
				jQuery(jQuery(this).attr('href')).show();
				jQuery(this).addClass('nav-tab-active');
				return false;
			},
		}
	} // Enf of return
}; // End of class

WPFM_AdminSettings = WPFM_AdminSettings();
jQuery(document).ready(function ($) {
	WPFM_AdminSettings.init();
});
