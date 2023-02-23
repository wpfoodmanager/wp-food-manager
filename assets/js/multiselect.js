var WPFMMultiSelect = function () {
   /// <summary>Constructor function of the food WPFMMultiSelect class.</summary>
   /// <returns type="Home" />      
   return {
      ///<summary>
      ///Initializes the WPFMMultiSelect.  
      ///</summary>     
      ///<returns type="initialization settings" />   
      /// <since>1.0.0</since>         
      init: function () {
         WPFMCommon.logInfo("WPFMMultiSelect.init...");
         //jQuery('.food-manager-WPFMMultiSelect').chosen({ search_contains: true });
         jQuery(".food-manager-multiselect").chosen({ search_contains: !0 });
      }
   } //enf of returnmultiselect
}; //end of class

WPFMMultiSelect = WPFMMultiSelect();
jQuery(document).ready(function ($) {
   WPFMMultiSelect.init();
});