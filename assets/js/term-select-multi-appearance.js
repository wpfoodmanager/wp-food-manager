var WPFM_MultiAppearanceSelect = function () {
    return {
        init: function () {
            WPFM_Common.logInfo("WPFM_MultiAppearanceSelect.init...");
            jQuery('.multiselect_appearance .food-manager-category-dropdown').on('change', function () {
                if (jQuery('.selection-preview .preview-items li').length) {
                    var selected_val = {};
                    jQuery('.selection-preview .preview-items li').each(function (index) {
                        var data_id = jQuery(this).attr('data-id');
                        var value = jQuery(this).find('input').val();
                        var unit_id = jQuery(this).find('select').val();
                        selected_val[data_id] = { 'value': value, 'unit_id': unit_id };
                    });
                }
                // defining variables
                var data_name = jQuery(this).parents('.wpfm-form-group').find('.selection-preview').attr('data-name'),
                    preview_htm = '',
                    unit_options = '<option value="">Unit</option>',
                    selected_terms = {},
                    units = JSON.parse(appearance_params.unit_terms);
                // Push selected value array
                jQuery.each(jQuery(this).find('option'), function (key, value) {
                    if (jQuery(this).is(':selected')) {
                        // Define term id and term name
                        var term_id = jQuery(this).val(),
                            label = jQuery(this).text();
                        // array
                        selected_terms[term_id] = label;
                    }
                });
                // units html
                jQuery.each(units, function (key, unit) {
                    unit_options += '<option value="' + unit.term_id + '">' + unit.name + '</option>';
                });
                // preview html
                jQuery.each(selected_terms, function (term_id, label) {
                    term_id = parseInt(term_id);
                    preview_htm += '<li class="term-item" data-id="' + term_id + '">';
                    preview_htm += '<label>' + label + '</label>';
                    preview_htm += '<div class="term-item-flex">';
                    preview_htm += '<input type="number" min="0" name="' + data_name + '[' + term_id + '][value]">';
                    preview_htm += '<select name="' + data_name + '[' + term_id + '][unit_id]">' + unit_options + '</select>';
                    preview_htm += '</div>';
                    preview_htm += '</li>';
                });
                jQuery(this).parents('.wpfm-form-group').find('.selection-preview ul').html(preview_htm);
                if( jQuery(this).parents('.wpfm-form-group').find('.selection-preview ul').find('li').length == 0 ){
                    jQuery(this).parents('.wpfm-form-group').find('.selection-preview').hide();
                }else{
                    jQuery(this).parents('.wpfm-form-group').find('.selection-preview').show();
                }
                // Append preview html
                if (selected_val) {
                    jQuery.each(selected_val, function (term_id, value) {
                        jQuery('.selection-preview .preview-items li[data-id="' + term_id + '"]').find('input').val(value.value);
                        jQuery('.selection-preview .preview-items li[data-id="' + term_id + '"]').find('select').val(value.unit_id);
                    });
                }
            });
        }
    }
};

WPFM_MultiAppearanceSelect = WPFM_MultiAppearanceSelect();
jQuery(document).ready(function ($) {
    WPFM_MultiAppearanceSelect.init();
});