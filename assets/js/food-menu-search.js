var WPFM_FoodMenuFilters = (function () {
    return {
        init: function () {
            jQuery('#food-menu-search').on('keyup', function() {
                var value = jQuery(this).val().toLowerCase();
                jQuery('#food-menu-container .food-menu-section').filter(function() {
                    jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        }
    };
})();

jQuery(document).ready(function($) {
    WPFM_FoodMenuFilters.init();
});