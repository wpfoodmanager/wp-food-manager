var WPFMTermMultiselect = function () {
    /// <summary>Constructor function of the food WPFMTermMultiselect class.</summary>
    /// <returns type="Home" />      
    return {
        ///<summary>
        ///Initializes the term multiselect.  
        ///</summary>     
        ///<returns type="initialization settings" />   
        /// <since>1.0.0</since> 
        init: function () {
            WPFMCommon.logInfo("WPFMTermMultiselect.init...");
            //jQuery('.food-manager-category-dropdown').chosen({ search_contains: true });
            jQuery(".food-manager-category-dropdown").chosen({ search_contains: !0 });
        }
    } //enf of return
}; //end of class

WPFMTermMultiselect = WPFMTermMultiselect();
jQuery(document).ready(function ($) {
    WPFMTermMultiselect.init();
});