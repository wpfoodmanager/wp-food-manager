var TermMultiselect= function () {
    /// <summary>Constructor function of the food TermMultiselect class.</summary>
    /// <returns type="Home" />      
    return {
	    ///<summary>
        ///Initializes the term multiselect.  
        ///</summary>     
        ///<returns type="initialization settings" />   
        /// <since>1.0.0</since> 
        init: function() 
        {   
	        WPFM_Common.logInfo("TermMultiselect.init...");  
	       //jQuery('.food-manager-category-dropdown').chosen({ search_contains: true });
	        jQuery(".food-manager-category-dropdown").chosen({ search_contains: !0 });
	   }   
    } //enf of return
}; //end of class

TermMultiselect= TermMultiselect();
jQuery(document).ready(function($) 
{
   TermMultiselect.init();
});