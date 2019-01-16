/*jslint browser: true*/
/*global jQuery, console, WPGlobusWC, WPGlobusWC_CP, WPGlobusCore, WPGlobusCoreData, WPGlobusDialogApp */
jQuery(document).ready(function ($) {
    "use strict";
    var api;

    //noinspection JSLint
    if ("undefined" === typeof WPGlobusWC || "undefined" === typeof WPGlobusWC_CP) {
        return;
    }

    api = {
        option: {},
        init: function (args) {
            api.option = $.extend(api.option, args);
            api.addElements();
            api.addListeners();
            api.setGroupTitle();
        },
        setGroupTitle: function () {
            setTimeout(function () {
                $("#bto_config_group_inner .group_name").each(function (i, e) {
                    var $e = $(e);
                    $e.text(WPGlobusCore.TextFilter($e.text(), WPGlobusCoreData.language));
                });
            }, 3000);
        },
        addListeners: function () {

            $(document).ajaxComplete(function (event, jqxhr, settings) {

                if ("undefined" === typeof settings.data) {
                    return;
                }

                if (-1 != settings.data.indexOf("woocommerce_add_composite_component")) {
                    api.addElements();
                }

                if (-1 != settings.data.indexOf("action=woocommerce_bto_composite_save")) {
                    api.setGroupTitle();
                    setTimeout(function () {
                        api.addElements();
                    }, 6000);
                }

            });

        },
        addElements: function () {
            $.each(WPGlobusWC_CP.addElementsByClass, function (i, cl) {
                api.addElementsBy(cl);
            });
        },

        /**
         * Add element to the UEdit dialog if it's an "input" or a "textarea"
         * having the CSS class we need and having either "id" or "name" attribute.
         * @param cl The CSS class.
         */
        addElementsBy: function (cl) {
            $("." + cl).each(function (ignore, e) {
                var node = e.nodeName.toLowerCase();
                var idForDialog = e.id || e.name;

                if (idForDialog && ("textarea" === node || "input" === node)) {
                    WPGlobusDialogApp.addElement(
                        {"id": idForDialog, "style": "float:left;"}
                    );
                }
            });
        }

    };

    WPGlobusWC_CP = $.extend({}, WPGlobusWC_CP, api);

    WPGlobusWC_CP.init();

});
