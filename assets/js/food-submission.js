WPFMFoodSubmission = function () {
    /// <summary>Constructor function of the event WPFMFoodSubmission class.</summary>
    /// <returns type="WPFMFoodSubmission" />
    return {
        /// <summary>
        /// Initializes the event submission.
        /// </summary>
        /// <returns type="initialization settings" />
        /// <since>1.0.0</since>
        init: function () {
            WPFMCommon.logInfo("WPFMFoodSubmission.init...");
            jQuery('body').on('click', '.food-manager-remove-uploaded-file', function () {
                jQuery(this).closest('.food-manager-uploaded-file').remove();
                return false;
            });
            if (jQuery('#event_start_time').length > 0) {
                jQuery('#event_start_time').timepicker({
                    'timeFormat': wp_food_manager_event_submission.i18n_timepicker_format,
                    'step': wp_food_manager_event_submission.i18n_timepicker_step,
                });
            }
            if (jQuery('#event_end_time').length > 0) {
                jQuery('#event_end_time').timepicker({
                    'timeFormat': wp_food_manager_event_submission.i18n_timepicker_format,
                    'step': wp_food_manager_event_submission.i18n_timepicker_step,
                });
            }
            if (jQuery('input[data-picker="timepicker"]').length > 0) {
                jQuery('input[data-picker="timepicker"]').timepicker({
                    'timeFormat': wp_food_manager_event_submission.i18n_timepicker_format,
                    'step': wp_food_manager_event_submission.i18n_timepicker_step,
                });
            }
            if (jQuery('input[data-picker="datepicker"]#event_start_date').length > 0) {
                wp_food_manager_event_submission.start_of_week = parseInt(wp_food_manager_event_submission.start_of_week);
                if (wp_food_manager_event_submission.show_past_date) {
                    jQuery('input[data-picker="datepicker"]#event_start_date').datepicker({
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    }).on('change', function () {
                        // set the "event_start_date" end to not be later than "event_end_date" starts:
                        jQuery("#event_end_date").datepicker("destroy");
                        jQuery('input[data-picker="datepicker"]#event_end_date').datepicker({
                            minDate: jQuery('#event_start_date').val(),
                            dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_event_submission.start_of_week,
                            monthNames: wp_food_manager_event_submission.monthNames,
                        });
                    });
                } else {
                    jQuery('input[data-picker="datepicker"]#event_start_date').datepicker({
                        minDate: 0,
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    }).on('change', function () {
                        // set the "event_start_date" end to not be later than "event_end_date" starts:
                        jQuery("#event_end_date").datepicker("destroy");
                        jQuery('input[data-picker="datepicker"]#event_end_date').datepicker({
                            minDate: jQuery('#event_start_date').val(),
                            dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_event_submission.start_of_week,
                            monthNames: wp_food_manager_event_submission.monthNames,
                        });

                    });
                }
            }
            if (jQuery('input[data-picker="datepicker"]#event_end_date').length > 0) {
                jQuery('input[data-picker="datepicker"]#event_end_date').datepicker({
                    dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                    firstDay: wp_food_manager_event_submission.start_of_week,
                    monthNames: wp_food_manager_event_submission.monthNames,
                    beforeShow: function (input, inst) {
                        var mindate = jQuery('input[data-picker="datepicker"]#event_start_date').datepicker('getDate');
                        jQuery(this).datepicker('option', 'minDate', mindate);
                    }
                }).on('change', function () {
                    // set the "event_start_date" end to not be later than "event_end_date" starts:
                    jQuery("#event_registration_deadline").datepicker("destroy");
                    if (wp_food_manager_event_submission.show_past_date) {
                        jQuery('input[data-picker="datepicker"]#event_registration_deadline').datepicker({
                            maxDate: jQuery('#event_end_date').val(),
                            dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_event_submission.start_of_week,
                            monthNames: wp_food_manager_event_submission.monthNames,
                        });
                    } else {
                        jQuery('input[data-picker="datepicker"]#event_registration_deadline').datepicker({
                            minDate: 0,
                            maxDate: jQuery('#event_end_date').val(),
                            dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                            firstDay: wp_food_manager_event_submission.start_of_week,
                            monthNames: wp_food_manager_event_submission.monthNames,
                        });
                    }
                });
            }
            if (jQuery('input[data-picker="datepicker"]#event_registration_deadline').length > 0) {
                if (wp_food_manager_event_submission.show_past_date) {
                    jQuery('input[data-picker="datepicker"]#event_registration_deadline').datepicker({
                        maxDate: jQuery('#event_end_date').val(),
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    });
                } else {
                    jQuery('input[data-picker="datepicker"]#event_registration_deadline').datepicker({
                        minDate: 0,
                        maxDate: jQuery('#event_end_date').val(),
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    });
                }
            }
            if (jQuery('input[data-picker="datepicker"]').length > 0) {
                if (wp_food_manager_event_submission.show_past_date) {
                    jQuery('input[data-picker="datepicker"]').datepicker({
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    });
                } else {
                    jQuery('input[data-picker="datepicker"]').datepicker({
                        minDate: 0,
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week,
                        monthNames: wp_food_manager_event_submission.monthNames,
                    });
                }
            }
            //initially hide address, pincode, location textbox.
            if (jQuery('#event_online').length > 0) {
                //hide event venue name, address, location and pincode fields at the edit event when select online event
                if (jQuery('input[name=event_online]:checked').val() == 'yes') {
                    if (jQuery('.fieldset-event_venue_name').length > 0 && jQuery('input[name=event_venue_name]').length > 0) {
                        if (jQuery('input[name=event_venue_name]').attr('required'))
                            jQuery('input[name=event_venue_name]').attr('required', false);
                        jQuery('.fieldset-event_venue_name').hide();
                    }
                    if (jQuery('.fieldset-event_address').length > 0 && jQuery('input[name=event_address]').length > 0) {
                        if (jQuery('input[name=event_address]').attr('required'))
                            jQuery('input[name=event_address]').attr('required', false);

                        jQuery('.fieldset-event_address').hide();
                    }
                    if (jQuery('.fieldset-event_pincode').length > 0 && jQuery('input[name=event_pincode]').length > 0) {
                        if (jQuery('input[name=event_pincode]').attr('required'))
                            jQuery('input[name=event_pincode]').attr('required', false);

                        jQuery('.fieldset-event_pincode').hide();
                    }
                    if (jQuery('.fieldset-event_location').length > 0 && jQuery('input[name=event_location]').length > 0) {
                        if (jQuery('input[name=event_location]').attr('required'))
                            jQuery('input[name=event_location]').attr('required', false);

                        jQuery('.fieldset-event_location').hide();
                    }
                    if (jQuery('.fieldset-event_venue_ids').length > 0) {
                        jQuery('.fieldset-event_venue_ids').hide();
                    }
                }
            }
            //initially hide ticket price textbox
            if (jQuery('#event_ticket_options').length > 0 && jQuery('#event_ticket_options:checked').val() == 'free') {
                if (jQuery('input[name=event_ticket_price]').attr('required'))
                    jQuery('input[name=event_ticket_price]').attr('required', false);
                jQuery('.fieldset-event_ticket_price').hide();
            }
            jQuery('input[name=event_online]').on('change', WPFMFoodSubmission.actions.onlineEvent);
            jQuery('input[name=event_ticket_options]').on('change', WPFMFoodSubmission.actions.eventTicketOptions);
            //add links for paid and free tickets   
            jQuery('.add-group-row').on('click', WPFMFoodSubmission.actions.addGroupField);
            //delete tickets 
            jQuery(document).delegate('.remove-group-row', 'click', WPFMFoodSubmission.actions.removeGroupField);
        },
        actions: {
            /// <summary>
            /// On click add ticket link fields paid and free
            //It will generate dynamic name and id for ticket fields.
            /// </summary>                 
            /// <returns type="generate name and id " />     
            /// <since>1.0.0</since>            
            addGroupField: function (event) {
                var $wrap = jQuery(this).closest('.field');
                var max_index = 0;
                $wrap.find('input.group-row').each(function () {
                    if (parseInt(jQuery(this).val()) > max_index) {
                        max_index = parseInt(jQuery(this).val());
                    }
                });
                var html = jQuery(this).data('row').replace(/%%group-row-index%%/g, max_index + 1);
                html = html.replace(/%group-row-index%/g, max_index + 1);
                jQuery(this).before(html);
                if ($wrap.find('input[data-picker="datepicker"]').length > 0) {
                    $wrap.find('input[data-picker="datepicker"]').datepicker({
                        dateFormat: wp_food_manager_event_submission.i18n_datepicker_format,
                        firstDay: wp_food_manager_event_submission.start_of_week
                    });
                }
                if ($wrap.find('input[data-picker="timepicker"]').length > 0) {
                    $wrap.find('input[data-picker="timepicker"]').timepicker({
                        'timeFormat': wp_food_manager_event_submission.i18n_timepicker_format,
                        'step': wp_food_manager_event_submission.i18n_timepicker_step,
                    });
                }
                if ($wrap.find('select[multiple="multiple"]').length > 0) {
                    $wrap.find('select[multiple="multiple"]').chosen();
                }
                event.preventDefault();
            },
            /// <summary>
            /// Remove Paid and free tickets fields 
            /// </summary>                 
            /// <returns type="remove paid and free tickets fields" />     
            /// <since>1.0.0</since>
            removeGroupField: function (event) {
                jQuery("." + this.id).remove();
                event.preventDefault();
            },
            /// <summary>
            /// Hide address,location and pincode filed when online event.
            /// </summary>
            /// <returns type="initialization settings" />
            /// <since>1.0.0</since>
            onlineEvent: function (event) {
                event.preventDefault();
                WPFMCommon.logInfo("EventDashboard.actions.onlineEvent...");
                if (jQuery('#event_online').length > 0) {
                    if (jQuery(this).val() == "yes") {
                        if (jQuery('.fieldset-event_venue_name').length > 0 && jQuery('input[name=event_venue_name]').length > 0) {
                            if (jQuery('input[name=event_venue_name]').attr('required'))
                                jQuery('input[name=event_venue_name]').attr('required', false);
                            jQuery('.fieldset-event_venue_name').hide();
                        }
                        if (jQuery('.fieldset-event_address').length > 0 && jQuery('input[name=event_address]').length > 0) {
                            if (jQuery('input[name=event_address]').attr('required'))
                                jQuery('input[name=event_address]').attr('required', false);
                            jQuery('.fieldset-event_address').hide();
                        }
                        if (jQuery('.fieldset-event_pincode').length > 0 && jQuery('input[name=event_pincode]').length > 0) {
                            if (jQuery('input[name=event_pincode]').attr('required'))
                                jQuery('input[name=event_pincode]').attr('required', false);
                            jQuery('.fieldset-event_pincode').hide();
                        }
                        if (jQuery('.fieldset-event_location').length > 0 && jQuery('input[name=event_location]').length > 0) {
                            if (jQuery('input[name=event_location]').attr('required'))
                                jQuery('input[name=event_location]').attr('required', false);
                            jQuery('.fieldset-event_location').hide();
                        }
                        if (jQuery('.fieldset-event_venue_ids').length > 0) {
                            jQuery('.fieldset-event_venue_ids').hide();
                        }
                    } else {
                        if (jQuery('.fieldset-event_venue_name').length > 0 && jQuery('input[name=event_venue_name]').length > 0) {
                            if (jQuery('input[name=event_venue_name]').attr('required'))
                                jQuery('input[name=event_venue_name]').attr('required', true);
                            jQuery('.fieldset-event_venue_name').show();
                        }
                        if (jQuery('.fieldset-event_address').length > 0 && jQuery('input[name=event_address]').length > 0) {
                            if (jQuery('input[name=event_address]').attr('required'))
                                jQuery('input[name=event_address]').attr('required', true);
                            jQuery('.fieldset-event_address').show();
                        }
                        if (jQuery('.fieldset-event_pincode').length > 0 && jQuery('input[name=event_pincode]').length > 0) {
                            if (jQuery('input[name=event_pincode]').attr('required'))
                                jQuery('input[name=event_pincode]').attr('required', true);
                            jQuery('.fieldset-event_pincode').show();
                        }
                        if (jQuery('.fieldset-event_location').length > 0 && jQuery('input[name=event_location]').length > 0) {
                            if (jQuery('input[name=event_location]').attr('required'))
                                jQuery('input[name=event_location]').attr('required', true);
                            jQuery('.fieldset-event_location').show();
                        }
                        if (jQuery('.fieldset-event_venue_ids').length > 0) {
                            jQuery('.fieldset-event_venue_ids').show();
                        }
                    }
                }
            },
            /// <summary>
            /// Show and Hide ticket price textbox.
            /// </summary>
            /// <returns type="initialization ticket price settings" />
            /// <since>1.0.0</since>
            eventTicketOptions: function (event) {
                event.preventDefault();
                WPFMCommon.logInfo("EventDashboard.actions.eventTicketOptions...");
                if (jQuery('#event_ticket_options').length > 0) {
                    if (jQuery(this).val() == "free") {
                        if (jQuery('.fieldset-event_ticket_price').length > 0 && jQuery('input[name=event_ticket_price]').length > 0) {
                            if (jQuery('input[name=event_ticket_price]').attr('required'))
                                jQuery('input[name=event_ticket_price]').attr('required', false);
                            jQuery('.fieldset-event_ticket_price').hide();
                        }
                    } else {
                        if (jQuery('.fieldset-event_ticket_price').length > 0 && jQuery('input[name=event_ticket_price]').length > 0)
                            if (jQuery('input[name=event_ticket_price]').attr('required'))
                                jQuery('input[name=event_ticket_price]').attr('required', true);
                        jQuery('.fieldset-event_ticket_price').show();
                    }
                }
            },
        } //end of action
    } //enf of return
}; //end of class

WPFMFoodSubmission = WPFMFoodSubmission();
jQuery(document).ready(function ($) {
    WPFMFoodSubmission.init();
});
