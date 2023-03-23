var WPFMFront = function () {
    return {
        init: function () {
            jQuery(".wpfm-form-group.fieldset-topping_description").hide();
            //use body to call after dom update
            jQuery("body").on('click', 'a.wpfm-food-item-remove', WPFMFront.actions.removeFoodItem);
            //Action button For Extra topping field's content to View more and View less
            jQuery("body").on('click', 'span.wpfm-view-more', WPFMFront.actions.viewmoreFoodFields);
            jQuery("body").on('click', 'span.wpfm-view-less', WPFMFront.actions.viewlessFoodFields);
            /* General tab - Regular and Sale price validation */
            jQuery('body').on('wpfm_add_error_tip', function (e, element, error_type) {
                var offset = element.position();
                if (element.parent().find('.wpfm_error_tip').length === 0) {
                    element.after('<div class="wpfm_error_tip ' + error_type + '">' + wpfm_accounting_params[error_type] + '</div>');
                    element.parent().find('.wpfm_error_tip')
                        .css('left', offset.left + element.width() - (element.width() / 2) - (jQuery('.wpfm_error_tip').width() / 2))
                        .css('top', offset.top + element.height())
                        .fadeIn('100');
                }
            });
            jQuery('body').on('wpfm_remove_error_tip', function (e, element, error_type) {
                element.parent().find('.wpfm_error_tip.' + error_type).fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('click', function () {
                jQuery('.wpfm_error_tip').fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('blur', '#_food_sale_price', function () {
                jQuery('.wpfm_error_tip').fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('keyup', '#_food_sale_price', function (s, l) {
                var sale_price_field = jQuery(this), regular_price_field;
                regular_price_field = jQuery('#_food_price');
                var sale_price = parseFloat(
                    window.accounting.unformat(sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat(regular_price_field.val())
                );
                if (sale_price >= regular_price) {
                    jQuery(document.body).triggerHandler('wpfm_add_error_tip', [jQuery(this), 'wpfm_sale_less_than_regular_error']);
                } else {
                    jQuery(document.body).triggerHandler('wpfm_remove_error_tip', [jQuery(this), 'wpfm_sale_less_than_regular_error']);
                }
            });
            jQuery('body').on('change', '#_food_sale_price', function () {
                var sale_price_field = jQuery(this), regular_price_field;
                regular_price_field = jQuery('#_food_price');
                var sale_price = parseFloat(
                    window.accounting.unformat(sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat(regular_price_field.val())
                );
                if (sale_price >= regular_price) {
                    jQuery(this).val('');
                }
            });
            jQuery(document).on("click", ".wpfm-add-row", WPFMFront.actions.addElementRow)
            jQuery(document).on("click", ".option-delete-btn", WPFMFront.actions.removeAttributesOptions)
            jQuery(document).on("click", ".wpfm-delete-btn", WPFMFront.actions.removeAttributes)
            jQuery('#wpfm-add-new-option').on('click', WPFMFront.actions.addNewOption);
            jQuery('body').on('change', 'input[name^="topping_name"]', WPFMFront.actions.updateOptionTitle);
            jQuery(document).on("click", ".wpfm-togglediv", function (e) {
                var row_count = jQuery(this).data('row-count');
                var menuItem = jQuery(e.currentTarget);
                if (menuItem.attr('aria-expanded') === 'true') {
                    jQuery('.wpfm-options-wrap.wpfm-options-box-' + row_count).removeClass("closed");
                    jQuery(this).attr('aria-expanded', 'false');
                } else {
                    jQuery('.wpfm-options-wrap.wpfm-options-box-' + row_count).addClass("closed");
                    jQuery(this).attr('aria-expanded', 'true');
                }
                jQuery(this).parents('.postbox').find('.wpfm-options-box').slideToggle("slow");
            });
            jQuery(".container .wpfm-form-wrapper table.widefat tbody").sortable({
                connectWith: ".container .wpfm-form-wrapper table.widefat tbody",
                items: "tr",
                axis: "y",
                helper: function (t, i) {
                    return i.children().children().each((function () {
                        jQuery(this).width(jQuery(this).width())
                    }
                    )), i
                },
                placeholder: "placeholder",
                opacity: .65,
                update: function (event, ui) {
                    var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                    jQuery('.container .wpfm-form-group.fieldset_topping_options_' + repeater_row_count + ' table.widefat tbody tr').each(function (i) {
                        var humanNum = i + 1;
                        jQuery(this).children('td:nth-child(2)').html(humanNum);
                        jQuery(this).attr('class', 'option-tr-' + humanNum);
                        jQuery(this).children('.option-value-class').val(humanNum);
                        jQuery(this).children('td').children('.opt_name').attr('name', repeater_row_count + '_option_name_' + humanNum);
                        jQuery(this).children('td').children('.opt_default').attr('name', repeater_row_count + '_option_default_' + humanNum);
                        jQuery(this).children('td').children('.opt_price').attr('name', repeater_row_count + '_option_price_' + humanNum);
                        jQuery(this).children('td').children('.opt_select').attr('name', repeater_row_count + '_option_price_type_' + humanNum);
                        jQuery(this).children('td').children('.option-delete-btn').attr('data-id', humanNum);
                    });
                }
            }).disableSelection();
            /* General tab - Regular and Sale price validation */
            jQuery('body').on('wpfm_add_error_tip', function (e, element, error_type) {
                var offset = element.position();
                if (element.parent().find('.wpfm_error_tip').length === 0) {
                    element.after('<div class="wpfm_error_tip ' + error_type + '">' + wpfm_accounting_params[error_type] + '</div>');
                    element.parent().find('.wpfm_error_tip')
                        .css('left', offset.left + element.width() - (element.width() / 2) - (jQuery('.wpfm_error_tip').width() / 2))
                        .css('top', offset.top + element.height())
                        .fadeIn('100');
                }
            });
            jQuery('body').on('wpfm_remove_error_tip', function (e, element, error_type) {
                element.parent().find('.wpfm_error_tip.' + error_type).fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('click', function () {
                jQuery('.wpfm_error_tip').fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('blur', '#food_sale_price', function () {
                jQuery('.wpfm_error_tip').fadeOut('100', function () { jQuery(this).remove(); });
            });
            jQuery('body').on('keyup', '#food_sale_price', function (s, l) {
                var sale_price_field = jQuery(this), regular_price_field;
                regular_price_field = jQuery('#food_price');
                var sale_price = parseFloat(
                    window.accounting.unformat(sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat(regular_price_field.val())
                );
                if (sale_price >= regular_price) {
                    jQuery(document.body).triggerHandler('wpfm_add_error_tip', [jQuery(this), 'wpfm_sale_less_than_regular_error']);
                } else {
                    jQuery(document.body).triggerHandler('wpfm_remove_error_tip', [jQuery(this), 'wpfm_sale_less_than_regular_error']);
                }
            });
            jQuery('body').on('change', '#food_sale_price', function () {
                var sale_price_field = jQuery(this), regular_price_field;
                regular_price_field = jQuery('#food_price');
                var sale_price = parseFloat(
                    window.accounting.unformat(sale_price_field.val())
                );
                var regular_price = parseFloat(
                    window.accounting.unformat(regular_price_field.val())
                );
                if (sale_price >= regular_price) {
                    jQuery(this).val('');
                }
            });
        },
        actions: {
            removeFoodItem: function (event) {
                jQuery(this).parents('li').remove();
            },
            viewmoreFoodFields: function (event) {
                jQuery(this).prev().css({ "visibility": "visible", "height": "100%", "transform": "scaleY(1)" });
                jQuery(this).fadeOut();
                jQuery(this).next().fadeIn();
            },
            viewlessFoodFields: function (event) {
                jQuery(this).prev().prev().css({ "visibility": "hidden", "height": "0", "transform": "scaleY(0)", "transition": "all 150ms" });
                jQuery(this).fadeOut();
                jQuery(this).prev().fadeIn();
            },
            addElementRow: function (event) {
                var total_rows = 0;
                total_rows = jQuery(this).parents('table').find('tbody tr').length;
                total_rows = total_rows + 1;
                var row_count2 = jQuery(this).closest('.postbox').children('.repeated-options').val();
                var html = jQuery(this).data('row').replace(/%%repeated-option-index3%%/g, total_rows).replace(/%%repeated-option-index2%%/g, row_count2);
                html.replace('value="1"', total_rows);
                jQuery(this).parents('table').find('tbody').append(html);
            },
            removeAttributesOptions: function (event) {
                var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                jQuery('.wpfm-options-box-' + repeater_row_count + ' tbody tr.option-tr-' + jQuery(this).data('id')).remove();
            },
            removeAttributes: function (event) {
                jQuery('.wpfm-options-box-' + jQuery(this).data('id')).remove();
            },
            updateOptionTitle: function (event) {
                jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text(this.value);
                var topping_key = this.value.replace(/\s/g, '_').toLowerCase();
                jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val(topping_key);
                if (this.value == '') {
                    jQuery(this).closest('.postbox').children('h3').children('.attribute_name').text("Option Key");
                    jQuery(this).closest('.postbox').children('h3').children('.attribute_key').children('input').val("topping_key");
                }
            },
            addNewOption: function (event) {
                var max_index = 0;
                if (jQuery('.wpfm-form-wrapper').find('div.wpfm-options-wrap').length) {
                    jQuery('.wpfm-form-wrapper').find('div.wpfm-options-wrap').each(function () {
                        max_index++;
                    });
                }
                max_index = max_index + 1;
                var html = jQuery(this).data('row').replace(/%%repeated-option-index%%/g, max_index);
                var html_data = html.replace(/%repeated-option-index%/g, max_index);
                jQuery('.wpfm-form-wrapper .wpfm-actions').before(html_data);
                jQuery(".container .wpfm-form-wrapper table.widefat tbody").sortable({
                    connectWith: ".container .wpfm-form-wrapper table.widefat tbody",
                    items: "tr",
                    axis: "y",
                    helper: function (t, i) {
                        return i.children().children().each((function () {
                            jQuery(this).width(jQuery(this).width())
                        }
                        )), i
                    },
                    placeholder: "placeholder",
                    opacity: .65,
                    update: function (event, ui) {
                        var repeater_row_count = jQuery(this).closest(".postbox").children(".repeated-options").val();
                        jQuery('.container .wpfm-form-group.fieldset_topping_options_' + repeater_row_count + ' table.widefat tbody tr').each(function (i) {
                            var humanNum = i + 1;
                            jQuery(this).children('td:nth-child(2)').html(humanNum);
                            jQuery(this).attr('class', 'option-tr-' + humanNum);
                            jQuery(this).children('.option-value-class').val(humanNum);
                            jQuery(this).children('td').children('.opt_name').attr('name', repeater_row_count + '_option_name_' + humanNum);
                            jQuery(this).children('td').children('.opt_default').attr('name', repeater_row_count + '_option_default_' + humanNum);
                            jQuery(this).children('td').children('.opt_price').attr('name', repeater_row_count + '_option_price_' + humanNum);
                            jQuery(this).children('td').children('.opt_select').attr('name', repeater_row_count + '_option_price_type_' + humanNum);
                            jQuery(this).children('td').children('.option-delete-btn').attr('data-id', humanNum);
                        });
                    }
                }).disableSelection();
                if (jQuery('input[data-picker="datepicker"]').length > 0) {
                    if (wp_food_manager_food_submission.show_past_date) {
                        jQuery('input[data-picker="datepicker"]').datepicker({
                            dateFormat: wp_food_manager_food_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_food_submission.start_of_week,
                            monthNames: wp_food_manager_food_submission.monthNames,
                        });
                    } else {
                        jQuery('input[data-picker="datepicker"]').datepicker({
                            minDate: 0,
                            dateFormat: wp_food_manager_food_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_food_submission.start_of_week,
                            monthNames: wp_food_manager_food_submission.monthNames,
                        });
                    }
                }
                jQuery(".food-manager-multiselect").chosen({ search_contains: !0 })
                // initialize WP editor on click for new WP editor's field.
                var repeater_row_counts = jQuery(this).parents(".wpfm-options-wrapper").children(".wpfm-options-wrap").length;
                fieldLabel = jQuery(this).parents(".wpfm-options-wrapper").find("fieldset.wpfm-form-group.wp-editor-field").attr("data-field-name");
                var fieldChangedLabel = fieldLabel.replace(fieldLabel.match(/(\d+)/g)[0], '');
                var editorId = fieldChangedLabel + repeater_row_counts;
                wp.editor.initialize(editorId, {
                    tinymce: {
                        wpautop: false,
                        textarea_rows: 8,
                        plugins: 'lists,paste,tabfocus,wplink,wordpress',
                        toolbar1: 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
                        toolbar2: '',
                        paste_as_text: true,
                        paste_auto_cleanup_on_paste: true,
                        paste_remove_spans: true,
                        paste_remove_styles: true,
                        paste_remove_styles_if_webkit: true,
                        paste_strip_class_attributes: true,
                    },
                    quicktags: false,
                    mediaButtons: false,
                });
            },
        },
    }
};

WPFMFront = WPFMFront();
jQuery(document).ready(function ($) {
    WPFMFront.init();
});