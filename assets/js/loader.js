var WPFM_Loader = function () {
    return {
        init: function () {
            jQuery(document).ready(function() {
                // Function to show loader and success message
                function showLoaderAndMessage() {
                    // Hide the "no-menu-item-handle" notice
                    jQuery('.no-menu-item-handle').hide();

                    // Hide menu items and show the loader
                    jQuery('#wpfm-food-menu-list').hide();
                    jQuery('.wpfm-loader').show();
                    
                    // Show the menu items and any data result after loader is hidden
                    setTimeout(function() {
                        jQuery('.wpfm-loader').hide();
                        jQuery('#wpfm-food-menu-list').fadeIn(); 
                        jQuery('.wpfm-success-message').fadeIn();
                        setTimeout(function() {
                            jQuery('.wpfm-success-message').fadeOut();
                        }, 5000);
                    }, 3000); 
                }

                // Trigger loader and message on category or type selection
                jQuery('#wpfm-admin-food-selection, #wpfm-admin-food-types-selection').on('change', function() {
                    showLoaderAndMessage();
                });
            });
        }
    }; // End of return
}; // End of class

// Initialize the loader functionality
WPFM_Loader = WPFM_Loader();
jQuery(document).ready(function() {
    WPFM_Loader.init();
});
