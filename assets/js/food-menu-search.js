var WPFM_FoodMenuFilters = (function () {
    return {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function() {
            jQuery('#food-menu-search').on('keyup', this.handleSearch);
        },

        handleSearch: function() {
            var search_term = jQuery(this).val().toLowerCase();

            jQuery.ajax({
                url: foodMenuAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'food_menu_search',
                    search_term: search_term,
                    nonce: foodMenuAjax.nonce,
                    is_ajax: true
                },
                success: function(response) {
                    jQuery('#food-menu-results').html(response);
                }
            });
        }
    };
})();

jQuery(document).ready(function($) {
    WPFM_FoodMenuFilters.init();
});
