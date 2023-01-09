var FoodAjaxFilters = function() {
    var supportHtml5History;
    var xmlHttpRequest = [];
    return {
        init: function() {
            WPFM_Common.logInfo("FoodAjaxFilters.init...");

            if (jQuery.isFunction(jQuery.fn.chosen)) {
                if (wpfm_ajax_filters.is_rtl == 1) {
                    jQuery('select[name^="search_datetimes"]').addClass('chosen-rtl');
                    jQuery('select[name^="search_categories"]').addClass('chosen-rtl');
                    jQuery('select[name^="search_food_types"]').addClass('chosen-rtl');
                    jQuery('select[name^="search_ticket_prices"]').addClass('chosen-rtl')
                }
                jQuery('select[name^="search_datetimes"]').chosen({
                    search_contains: true
                });
                jQuery('select[name^="search_categories"]').chosen({
                    search_contains: true
                });
                jQuery('select[name^="search_food_types"]').chosen({
                    search_contains: true
                });
                jQuery('select[name^="search_ticket_prices"]').chosen({
                    search_contains: true
                })
            }
            if (window.history && window.history.pushState) {
                supportHtml5History = true
            } else {
                supportHtml5History = false
            }
            //jQuery(window).on('load', FoodAjaxFilters.actions.windowLoad);
            jQuery(document).ready(FoodAjaxFilters.actions.windowLoad);
            jQuery(document.body).on('click', '.load_more_foods', FoodAjaxFilters.actions.loadMorefoods);
            jQuery('.food_filters').on('click', '.reset', FoodAjaxFilters.actions.FoodAjaxFiltersReset);
            jQuery('div.food_listings').on('click', '.food-manager-pagination a', FoodAjaxFilters.actions.foodPagination);
            jQuery('.food_listings').on('update_food_listings', FoodAjaxFilters.actions.getfoodListings);
            jQuery('#search_keywords, #search_location, #search_datetimes, #search_categories, #search_food_types, #search_ticket_prices, .food-manager-filter').change(function() {
                var target = jQuery(this).closest('div.food_listings');
                target.triggerHandler('update_food_listings', [1, false]);
                
                FoodAjaxFilters.food_manager_store_state(target, 1)
            }).on("keyup", function(e) {
                if (e.which === 13) {
                    jQuery(this).trigger('change')
                }
            })
        },
        food_manager_store_state: function(target, page) {
            WPFM_Common.logInfo("FoodAjaxFilters.food_manager_store_state...");
            var location = document.location.href.split('#')[0];
            if (supportHtml5History) {
                var form = target.find('.food_filters');
                var data = jQuery(form).serialize();
                var index = jQuery('div.food_listings').index(target);
                window.history.replaceState({
                    id: 'food_manager_state',
                    page: page,
                    data: data,
                    index: index
                }, '', location)
            }
        },
        actions: {
            windowLoad: function(event) {
                
                WPFM_Common.logInfo("FoodAjaxFilters.actions.windowLoad...");
                jQuery('.food_filters').each(function() {
                    var target = jQuery(this).closest('div.food_listings');
                    console.log(jQuery(this).closest('div.food_listings').length);
                    var form = target.find('.food_filters');
                    var inital_page = 1;
                    var index = jQuery('div.food_listings').index(target);
                    if (window.history.state && window.location.hash) {
                        var state = window.history.state;
                        console.log(form.deserialize(state.data));
                        
                        if (state.id && 'food_manager_state' === state.id && index == state.index) {
                           //set initial_page with 1 on page refresh
                            inital_page = 1;
                            form.deserialize(state.data);
                            form.find(':input[name^="search_datetimes"]').not(':input[type="hidden"]').trigger('chosen:updated');
                            form.find(':input[name^="search_categories"]').not(':input[type="hidden"]').trigger('chosen:updated');
                            form.find(':input[name^="search_event_types"]').not(':input[type="hidden"]').trigger('chosen:updated');
                            form.find(':input[name^="search_ticket_prices"]').not(':input[type="hidden"]').trigger('chosen:updated')
                        }
                    }
                    target.triggerHandler('update_food_listings', [inital_page, false])
                });
                //event.preventDefault();
            },
            FoodAjaxFiltersReset: function(event) {
                WPFM_Common.logInfo("FoodAjaxFilters.actions.FoodAjaxFiltersReset...");
                var target = jQuery(this).closest('div.food_listings');
                var form = jQuery(this).closest('form');
                form.find(':input[name="search_keywords"], :input[name="search_location"], .food-manager-filter').not(':input[type="hidden"]').val('').trigger('chosen:updated');
                form.find(':input[name^="search_datetimes"]').not(':input[type="hidden"]').val(0).trigger('chosen:updated');
                form.find(':input[name^="search_categories"]').not(':input[type="hidden"]').val('').trigger('chosen:updated');
                form.find(':input[name^="search_food_types"]').not(':input[type="hidden"]').val('').trigger('chosen:updated');
                form.find(':input[name^="search_ticket_prices"]').not(':input[type="hidden"]').val(0).trigger('chosen:updated');
                target.triggerHandler('reset');
                target.triggerHandler('update_food_listings', [1, false]);
                FoodAjaxFilters.food_manager_store_state(target, 1);
                return false;
                event.preventDefault ()
            },
            loadMorefoods: function(event) {
                WPFM_Common.logInfo("FoodAjaxFilters.actions.loadMorefoods...");
                var target = jQuery(this).closest('div.food_listings');
                var page = parseInt(jQuery(this).data('page') || 1);
                var loading_previous = false;
                jQuery(this).addClass('wpfm-loading');               
                page = page + 1;
                jQuery(this).data('page', page);
                //FoodAjaxFilters.food_manager_store_state(target, page);
                target.triggerHandler('update_food_listings', [page, true, loading_previous]);
                return false;
                event.preventDefault ()
            },
            foodPagination: function(event) {
                WPFM_Common.logInfo("FoodAjaxFilters.actions.foodPagination...");
                var target = jQuery(this).closest('div.food_listings');
                var page = jQuery(this).data('page');
                FoodAjaxFilters.food_manager_store_state(target, page);
                target.triggerHandler('update_food_listings', [page, false]);
                jQuery("body, html").animate({
                    scrollTop: target.offset().top
                }, 600);
                return false;
                event.preventDefault ()
            },
            getfoodListings: function(event, page, append, loading_previous) {

                WPFM_Common.logInfo("FoodAjaxFilters.actions.getfoodListings...");
               
                var data = '';
                var target = jQuery(this);
                var form = target.find('.food_filters');
                var filters_bar = target.find('.showing_applied_filters');
                var results = target.find('.food_listings');
                var per_page = target.data('per_page');
                var orderby = target.data('orderby');
                var order = target.data('order');
                var featured = target.data('featured');
                var cancelled = target.data('cancelled');
                var index = jQuery('div.food_listings').index(this);
                if (index < 0) {
                    return
                }
                if (xmlHttpRequest[index]) {
                    xmlHttpRequest[index].abort()
                }
                if (!append) {
                    jQuery(results).addClass('wpfm-loading');
                    jQuery('div.food_listing, div.no_food_listings_found', results).css('visibility', 'hidden');         
                    target.find('.load_more_foods').data('page', page)
                }
                if (true == target.data('show_filters')) {
                    var filter_food_type = [];
                    var datetimes = form.find(':input[name^="search_datetimes"]').map(function() {
                        return jQuery(this).val()
                    }).get();
                    var categories = form.find(':input[name^="search_categories"]').map(function() {
                        return jQuery(this).val()
                    }).get();
                    var food_types = form.find(':input[name^="search_food_types"]').map(function() {
                        return jQuery(this).val()
                    }).get();
                    var ticket_prices = form.find(':input[name^="search_ticket_prices"]').map(function() {
                        return jQuery(this).val()
                    }).get();
                    var keywords = '';
                    var location = '';
                    var $keywords = form.find(':input[name="search_keywords"]');
                    var $location = form.find(':input[name="search_location"]');
                    if ($keywords.val() !== $keywords.attr('placeholder')) {
                        keywords = $keywords.val()
                    }
                    if ($location.val() !== $location.attr('placeholder')) {
                        location = $location.val()
                    }
                    data = {
                        lang: wpfm_ajax_filters.lang,
                        search_keywords: keywords,
                        search_location: location,
                        search_datetimes: datetimes,
                        search_categories: categories,
                        search_food_types: food_types,
                        search_ticket_prices: ticket_prices,
                        per_page: per_page,
                        orderby: orderby,
                        order: order,
                        page: page,
                        featured: featured,
                        cancelled: cancelled,
                        show_pagination: target.data('show_pagination'),
                        form_data: form.serialize()
                    }
                } else {
                    var keywords = target.data('keywords');
                    var location = target.data('location');
                    var datetimes = target.data('datetimes');
                    var categories = target.data('categories');
                    var food_types = target.data('food_types');
                    var ticket_prices = target.data('ticket_prices');
                    if (categories) {
                        categories = categories.split(',')
                    }
                    data = {
                        lang: wpfm_ajax_filters.lang,
                        search_keywords: keywords,
                        search_location: location,
                        search_datetimes: datetimes,
                        search_categories: categories,
                        search_food_types: food_types,
                        search_ticket_prices: ticket_prices,
                        per_page: per_page,
                        orderby: orderby,
                        order: order,
                        page: page,
                        featured: featured,
                        cancelled: cancelled,
                        show_pagination: target.data('show_pagination')
                    }
                }
                xmlHttpRequest[index] = jQuery.ajax({
                    type: 'POST',
                    url: wpfm_ajax_filters.ajax_url.toString().replace("%%endpoint%%", "get_listings"),
                    data: data,
                    success: function(result) {
                        if (result) {
                            try {
                                if (result.filter_value) {
                                    jQuery(filters_bar).show().html('<span>' + result.filter_value + '</span>' + result.showing_links)
                                } else {
                                    jQuery(filters_bar).hide()
                                }
                                if (result.showing_applied_filters) {
                                    jQuery(filters_bar).addClass('showing-applied-filters');
                                } else {
                                    jQuery(filters_bar).removeClass('showing-applied-filters');
                                }
                                if (result.html) {
                                    if (append && loading_previous) 
									{
                                        jQuery(results).prepend(result.html);
										if(jQuery('div.google-map-loadmore').length > 0 )
										{
											jQuery('div .google-map-loadmore').not('div.google-map-loadmore:first').remove();
										}
                                    } 
									else if (append) 
									{ 
									   jQuery(results).append(result.html);
									    if(jQuery('div.google-map-loadmore').length > 0 )
										{
											jQuery('div .google-map-loadmore').not('div.google-map-loadmore:first').remove();
										}
                                    } 
									else 
									{
                                        jQuery(results).html(result.html);
                                    }
                                }
                                if (true == target.data('show_pagination')) {
                                    target.find('.food-manager-pagination').remove();
                                    if (result.pagination) {
                                        target.append(result.pagination)
                                    }
                                } else {
                                    if (!result.found_foods || result.max_num_pages <= page) {
                                        jQuery('.load_more_foods:not(.load_previous)', target).hide()
                                    } else if (!loading_previous) {
                                        jQuery('.load_more_foods', target).show()
                                    }
                                    jQuery('.load_more_foods', target).removeClass('wpfm-loading');
                                    jQuery('li.food_listing', results).css('visibility', 'visible')
                                }
                                jQuery(results).removeClass('wpfm-loading');
                                target.triggerHandler('updated_results', result)
                            } catch (err) {
                                if (window.console) {
                                    WPFM_Common.logError(err)
                                }
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, error) {
                        if (window.console && 'abort' !== textStatus) {
                            WPFM_Common.logError(textStatus + ': ' + error)
                        }
                    },
                    statusCode: {
                        404: function() {
                            if (window.console) {
                                WPFM_Common.logError("Error 404: Ajax Endpoint cannot be reached. Go to Settings > Permalinks and save to resolve.")
                            }
                        }
                    }
                });
                event.preventDefault ()
            }
        }
    }
};
FoodAjaxFilters = FoodAjaxFilters();
jQuery(document).ready(function($) {
    FoodAjaxFilters.init()
});