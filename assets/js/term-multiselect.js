var WPFM_TermMultiSelect = function () {
    /// <summary>Constructor function of the food WPFM_TermMultiSelect class.</summary>
    /// <returns type="Home" />      
    return {
        ///<summary>
        ///Initializes the term multiselect.  
        ///</summary>     
        ///<returns type="initialization settings" />   
        /// <since>1.0.0</since> 
        init: function () {
            WPFM_Common.logInfo("WPFM_TermMultiSelect.init...");
            jQuery(".food-manager-category-dropdown").chosen({ search_contains: !0 });
        }
    } //enf of return.
}; //end of class.

WPFM_TermMultiSelect = WPFM_TermMultiSelect();
jQuery(document).ready(function ($) {
    WPFM_TermMultiSelect.init();
});