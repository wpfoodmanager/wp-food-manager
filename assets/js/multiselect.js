var WPFM_MultiSelect = function () {
   /// <summary>Constructor function of the food WPFM_MultiSelect class.</summary>
   /// <returns type="Home" />      
   return {
      ///<summary>
      ///Initializes the WPFM_MultiSelect.  
      ///</summary>     
      ///<returns type="initialization settings" />   
      /// <since>1.0.0</since>         
      init: function () {
         WPFM_Common.logInfo("WPFM_MultiSelect.init...");
         jQuery(".food-manager-multiselect").chosen({ search_contains: !0 });
      }
   } //enf of returnmultiselect
}; //end of class

WPFM_MultiSelect = WPFM_MultiSelect();
jQuery(document).ready(function ($) {
   WPFM_MultiSelect.init();
});