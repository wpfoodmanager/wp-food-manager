var WPFM_Common = function () {
    /// <summary>Constructor function of the WPFM_Common class.</summary>
    /// <since>1.0.0</since>
    /// <returns type="WPFM_Common" />
    var abortObjects = [];
    var javaScriptInfoLoggingEnabled = false;
    var javaScriptTraceLoggingEnabled = false;
    return {
        init: function () {
            /// <summary>Initializes the WPFM_common.</summary>
            /// <since>1.0.0</since>              
            WPFM_Common.logInfo("WPFM_Common.init...");
            jQuery(document).delegate("ul.wpfm-tabs-wrap li.wpfm-tab-link", "click", WPFM_Common.tabChanged);

            window.addEventListener('keydown', function (e) {
                if (e.keyCode === 27 && jQuery('.wpfm-modal-close').length > 0) {
                    jQuery('.wpfm-modal-close').trigger('click');
                }
            });

            jQuery(".wpfm-modal-button").click(function () {
                jQuery('body').addClass("wpfm-modal-open");
                var modal_id = jQuery(this).attr('data-modal-id');
                if (jQuery('#' + modal_id).length > 0)
                    jQuery('#' + modal_id).addClass("wpfm-modal-open");
            });

            jQuery(".wpfm-modal-overlay").click(function () {
                jQuery('body').removeClass("wpfm-modal-open");
                jQuery('.wpfm-modal').removeClass("wpfm-modal-open");
            });

            jQuery(".wpfm-modal-close").click(function () {
                jQuery('body').removeClass("wpfm-modal-open");
                jQuery('.wpfm-modal').removeClass("wpfm-modal-open");
            });

        },
        tabChanged: function (food) {
            jQuery(this).addClass('active').parents('ul.wpfm-tabs-wrap').find('li').not(jQuery(this)).removeClass('active');

            var tabId = jQuery(this).data('tab');

            jQuery(this).closest('.wpfm-tabs-wrapper').find('.wpfm-tab-content .wpfm-tab-pane').not('#' + tabId).removeClass('active');
            jQuery(this).closest('.wpfm-tabs-wrapper').find('.wpfm-tab-content .wpfm-tab-pane#' + tabId).addClass('active');
        },

        jsonToString: function (jsonObject) {
            /// <summary>Converts a json object to a string.</summary>
            /// <param name="jsonObject" type="json">The json object.</param>
            /// <since>1.0.0</since>
            /// <returns type="string" />
            if (jsonObject === undefined) {
                return "";
            }
            return JSON.stringify(jsonObject);
        },

        stringToJson: function (jsonString) {
            /// <summary>Converts a json string to a json object.</summary>
            /// <param name="jsonString" type="string">The json string.</param>
            /// <since>1.0.0</since>
            /// <returns type="json" />
            WPFM_Common.logInfo("stringToJson...");
            if (jsonString === undefined || jsonString.length === 0) {
                return undefined;
            }
            return eval('(' + jsonString + ')');
        },

        getCurrentDateTime: function () {
            /// <summary>Get Current Date Time.</summary>
            /// <since>1.0.0</since>
            /// <param name="DateTime" />
            var today = new Date();
            var month = today.getMonth() + 1;
            var dateTime = today.getFullYear() + '-' + month + '-' + today.getDate() + ' ' + today.getHours() + ':' + today.getMinutes() + ':' + today.getSeconds();
            return dateTime;
        },

        setLogValue: function (value) {
            /// <summary>set data value.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />  	      
            if (value === undefined || value.length === 0) {
                return;
            }
            javaScriptInfoLoggingEnabled = value;
        },

        setTraceValue: function (value) {
            /// <summary>set data value.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />  	      
            if (value === undefined || value.length === 0) {
                return;
            }
            javaScriptTraceLoggingEnabled = value;
        },

        logInfo: function (data) {
            /// <summary>Logs some info data if JavaScript info logging is enabled.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />  	      
            if (javaScriptInfoLoggingEnabled) {
                if (window.console) {
                    console.log(data);
                }
            }
        },

        logTrace: function (data) {
            /// <summary>Logs some trace data if JavaScript trace logging is enabled.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />	       
            if (javaScriptTraceLoggingEnabled) {
                if (window.console) {
                    console.log(data);
                }
            }
        },

        logForce: function (data) {
            /// <summary>Logs some data, always.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />
            if (window.console) {
                console.log(data);
            }
        },

        logError: function (data) {
            /// <summary>Logs some error data, always.</summary>
            /// <since>1.0.0</since>
            /// <param name="data" />
            if (window.console) {
                console.error(data);
            }
        },

        htmlEncode: function (value) {
            /// <summary>Encode html text or code.</summary>
            /// <since>1.0.0</since>
            /// <param name="value" />
            if (value === undefined || value.length === 0) {
                return;
            }
            return (escape(value));
        },

        htmlDecode: function (value) {
            /// <summary>Decode html text or code.</summary>
            /// <since>1.0.0</since>
            /// <param name="value" />
            if (value === undefined || value.length === 0) {
                return;
            }
            return (unescape(value));
        },

        /// <summary>
        /// Validate email address field of th e post food before submitting a form. 
        /// Make sure that user has entered correct email address.   
        /// </summary>
        /// <param name="parent" type="string"></param>           
        /// <returns type="bool" />     
        /// <since>1.0.0</since>       
        validateEmail: function (email) {
            WPFM_Common.logInfo("WPFM_Common.validateEmail...");
            var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
            return expr.test(email);
        },

        /// <summary>
        /// Validate pincode field of th e post food before submitting a form. 
        /// Make sure that user has entered correct pincode (area code).   
        /// </summary>
        /// <param name="parent" type="string"></param>           
        /// <returns type="bool" />     
        /// <since>1.0.0</since>       
        validatePincode: function (email) {
            WPFM_Common.logInfo("WPFM_Common.validatePincode...");
            var expr = /^[0-9]+$/;
            return expr.test(email);
        },

        /// <summary>
        /// Validate contact person name for food submit. 
        /// Make sure that user has entered correct contact person name.  Also allow german umlaut.
        /// </summary>
        /// <param name="parent" type="string"></param>           
        /// <returns type="bool" />     
        /// <since>1.0.0</since>       
        validateName: function (name) {
            WPFM_Common.logInfo("WPFM_Common.validateName...");
            var expr = /[A-Za-z \-_.\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF]/;
            return expr.test(name);
        },

        /// <summary>
        /// Validate website url or any url.
        /// Make sure that user has entered correct valid website or url address.   
        /// </summary>
        /// <param name="parent" type="string"></param>           
        /// <returns type="bool" />     
        /// <since>1.0.0</since>       
        isURL: function (str) {
            WPFM_Common.logInfo("WPFM_Common.isURL...");
            var urlRegex = '^(?!mailto:)(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?$';
            var url = new RegExp(urlRegex, 'i');
            return str.length < 2083 && url.test(str);
        },

        /// <summary>
        /// show toggle content.    
        ///  </summary>
        /// <param name="parent" type="Event"></param>   
        /// <returns type="actions" />    
        /// <since>3.1.5</since>       
        showToggleContent: function (food) {
            WPFM_Common.logInfo("WPFM_Common.actions.showToggleContent...");
            jQuery(this).toggleClass("wpfm-active-button");

            event.preventDefault();
        },

        /// <summary>
        /// Cancel button click for close toggle content.    
        ///  </summary>
        /// <param name="parent" type="Event"></param>           
        /// <returns type="actions" />     
        /// <since>3.1.6</since>       
        hideToggleContent: function (food) {
            WPFM_Common.logInfo("WPFM_Common.actions.hideToggleContent...");
            //jQuery('#wpfm_contact_organizer').removeClass("wpfm-active-button");
            //jQuery('#wpfm_contact_organizer_form').slideUp("slow");
            event.preventDefault();
        },


    }
};

WPFM_Common = WPFM_Common();
jQuery(document).ready(function ($) {
    WPFM_Common.init();
});