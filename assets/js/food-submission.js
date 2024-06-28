WPFM_FoodSubmission = function () {
    /// <summary>Constructor function of the event WPFM_FoodSubmission class.</summary>
    /// <returns type="WPFM_FoodSubmission" />
    return {
        /// <summary>
        /// Initializes the event submission.
        /// </summary>
        /// <returns type="initialization settings" />
        /// <since>1.0.0</since>
        init: function () {
            jQuery('body').on('click', '.food-manager-remove-uploaded-file', function () {
                jQuery(this).closest('.food-manager-uploaded-file').remove();
                return false;
            });
            //add links for paid and free Group.
            jQuery('.add-group-row').on('click', WPFM_FoodSubmission.actions.addGroupField);
            //delete groups 
            jQuery(document).delegate('.remove-group-row', 'click', WPFM_FoodSubmission.actions.removeGroupField);
            // Datepicker
            if (jQuery('input[data-picker="datepicker"]').length > 0) {
                jQuery('input[data-picker="datepicker"]').datepicker({
                    dateFormat: wpfm_food_submission.i18n_datepicker_format,
                });
            }
        },
        actions: {
            /// <summary>
            /// On click add link fields paid and free.
            //It will generate dynamic name and id for fields.
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
                if ($wrap.find('select[multiple="multiple"]').length > 0) {
                    $wrap.find('select[multiple="multiple"]').chosen();
                }
                event.preventDefault();
            },
            /// <summary>
            /// Remove Paid and free fields. 
            /// </summary>                 
            /// <returns type="fields" />     
            /// <since>1.0.0</since>
            removeGroupField: function (event) {
                jQuery("." + this.id).remove();
                event.preventDefault();
            },
        } //end of action.
    } //enf of return.
}; //end of class.

WPFM_FoodSubmission = WPFM_FoodSubmission();
jQuery(document).ready(function ($) {
    WPFM_FoodSubmission.init();
});