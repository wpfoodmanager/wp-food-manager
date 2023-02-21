var MultiSelect = function () {
   /// <summary>Constructor function of the food MultiSelect class.</summary>
   /// <returns type="Home" />      
   return {
      ///<summary>
      ///Initializes the multiselect.  
      ///</summary>     
      ///<returns type="initialization settings" />   
      /// <since>1.0.0</since>         
      init: function () {
         WPFM_Common.logInfo("MultiSelect.init...");
         //jQuery('.food-manager-multiselect').chosen({ search_contains: true });
         jQuery(".food-manager-multiselect").chosen({ search_contains: !0 });
      }
   } //enf of returnmultiselect
}; //end of class

MultiSelect = MultiSelect();
jQuery(document).ready(function ($) {
   MultiSelect.init();
});