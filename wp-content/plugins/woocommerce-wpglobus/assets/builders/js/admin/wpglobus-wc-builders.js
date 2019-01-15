/*jslint browser: true*/
/*global jQuery, console, window, WPGlobusWC */
jQuery(document).ready(function ($) {
    "use strict";

    if ( "undefined" === typeof(WPGlobusWC) ) {
        return;
    }
    if ( "undefined" === typeof(WPGlobusAdmin.builder) ) {
        return;
    }

    var api = {
        init: function () {
			api.initShortDescription();
           // api.addListeners();
        },
		initShortDescription: function() {
			setTimeout( function(){
				$('#excerpt_ifr').addClass(WPGlobusAdmin.builder.translatableClass).css({'width':'98%'});
				var shortDescTitle = $('#postexcerpt .hndle span');
				if ( shortDescTitle.length == 1 ) {
					shortDescTitle.text( shortDescTitle.text() + ' (' + WPGlobusWC.en_language_name[WPGlobusWC.language] + ')' );
				}
			}, 500);
			

		}
    };

    WPGlobusWC = $.extend({}, WPGlobusWC, api);
    WPGlobusWC.init();

});
