var WPFMTermAutocomplete = function () {
    /// <summary>Constructor function of the food WPFMTermAutocomplete class.</summary>
    /// <returns type="Home" />      
    return {
        ///<summary>
        ///Initializes the WPFMTermAutocomplete.  
        ///</summary>     
        ///<returns type="initialization settings" />   
        /// <since>1.0.0</since>         
        init: function () {
            jQuery('#wpfm-add-new-option').on('click', WPFMTermAutocomplete.autocomplete);
        },
        autocomplete: function () {
            jQuery('.wpfm-autocomplete').each(function () {
                var taxonomy = jQuery(this).data('taxonomy');
                var $this = jQuery(this);
                jQuery(this).autocomplete({
                    source: function (request, response) {
                        jQuery.ajax({
                            dataType: 'json',
                            type: "GET",
                            url: wpfm_term_autocomplete.ajax_url,
                            data: {
                                term: request.term,
                                action: 'term_ajax_search',
                                taxonomy: taxonomy
                            },
                            success: function (data) {
                                response(data.data);
                            }
                        });
                    },
                    select: function (event, ui) {
                        $this.val(ui.item.label);
                        var wrapper = jQuery($this).parents('.wpfm-options-wrap');
                        var count = wrapper.find('.repeated-options').val();
                        if (ui.item.selection_type != '') {
                            wrapper.find('[name="_topping_type_' + count + '"]').val(ui.item.selection_type);
                        } else {
                            wrapper.find('[name="_topping_type_' + count + '"]').val('');
                        }
                        if (ui.item.required != '') {
                            wrapper.find('[name="_topping_required_' + count + '"][value="'+ui.item.required+'"]').prop('checked', true);
                        } else {
                            wrapper.find('[name="_topping_required_' + count + '"]').prop('checked', false);
                        }
                        if (ui.item.description != '') {
                            tinyMCE.get('_topping_description_' + count).setContent(ui.item.description);
                        } else {
                            tinyMCE.get('_topping_description_' + count).setContent('');
                        }
                    },
                });
            });
        }
    } //enf of returnmultiselect
}; //end of class

WPFMTermAutocomplete = WPFMTermAutocomplete();
jQuery(document).ready(function ($) {
    WPFMTermAutocomplete.init();
    WPFMTermAutocomplete.autocomplete();
});