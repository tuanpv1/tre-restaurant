/*jslint browser: true*/
/*global jQuery, console, window, WPGlobusWC */
jQuery(document).ready(function ($) {
    "use strict";

    var wpglobus_wc = function () {
        if ("undefined" === typeof(WPGlobusWC)) {
            return;
        }

        var toggleExcerpt = function () {
            if ($("#postexcerpt-hide").prop("checked")) {
                $("#wpglobus-wc-excerpt-tabs").css({"display": "block"});
            } else {
                $("#wpglobus-wc-excerpt-tabs").css({"display": "none"});
            }
            $("#postexcerpt").css({"position": "absolute", "left": "-9999px"});
        };

        var post_edit = function () {
            $("#wp-excerpt-wrap").addClass("hidden");
            // tabs on
            var wpglobus_wc_excerpt_tabs = $("#wpglobus-wc-excerpt-tabs");
            wpglobus_wc_excerpt_tabs.tabs();
            toggleExcerpt();
            $("#wpglobus-wc-excerpt-tabs .handlediv").on("click", function (event) {
                $(".wpglobus-tabs-list, .wpglobus-excerpt-editor").toggleClass("hidden");
                $("#wpglobus-wc-excerpt-tabs .postbox").toggleClass("closed");
                var tab = wpglobus_wc_excerpt_tabs.tabs("option", "active");

                if ($(".wpglobus-excerpt-editor:eq(0)").hasClass("hidden")) {
                    wpglobus_wc_excerpt_tabs.tabs({
                        hide: {effect: "explode", duration: 100}
                    });
                    $(".wpglobus-excerpt-editor").attr("style", "display:none");
                } else {
                    wpglobus_wc_excerpt_tabs.tabs({
                        show: {effect: "blind", duration: 100}
                    });
                    $(".wpglobus-excerpt-editor:eq(" + tab + ")").attr("style", "display:block");
                }
            });
            if ("hidden" === WPGlobusWC.box_short_desc) {
                $("#wpglobus-wc-excerpt-tabs .handlediv").trigger("click");
            }
            $("#postexcerpt-hide").on("click", function (event) {
                toggleExcerpt();
            });
        };
        var product_attributes = function () {
            var html = $("#wpglobus-wc-attribute-labels");
            var $al = $("#attribute_label");
            $al.addClass("hidden");
            $(html).insertAfter($al);

            $("body").on("blur", ".wpglobus-wc-attribute-label", function (event) {
                var s = "";
                $(".wpglobus-wc-attribute-label").each(function (index, e) {
                    var $e = $(e);
                    var l = $e.data("language");
                    if ($e.val() !== "") {
                        s = s + WPGlobusWC.locale_tag_start.replace("%s", l) + $e.val() + WPGlobusWC.locale_tag_end;
                    }
                });
                $al.val(s);
            });
        };

        var set_float_header = function (fromTopPx) {
            var $float_header = $("table.float-header");
            var th_size = {};
            $("table.wpglobus-wc-translations th").each(function (index, element) {
                th_size[index] = $(element).css("width");
            });
            $("table.float-header thead th").each(function (index, element) {
                $(element).css("width", th_size[index]);
            });
            $(window).scroll(function () {
                var scrolledFromtop = $(window).scrollTop();
                if (scrolledFromtop > fromTopPx) {
                    $float_header.addClass("fixed-header").css("display", "block");
                } else {
                    $float_header.removeClass("fixed-header").css("display", "none");
                }
            });
        };

        var translations_table = function () {
            var set_columns_width = function () {
                $(".wpglobus-wc-translations thead th").each(function (i, e) {
                    var $e = $(e);
                    var id = $e.attr("id");
                    $(".float-header .column-" + id).css("width", $e.css("width"));
                });
            };

            if (0 < WPGlobusWC.hidden_columns.length) {
                $.each(WPGlobusWC.hidden_columns, function (i, e) {
                    /** @see unchecked() wp-admin\js\common.js */
                    columns.unchecked(e);
                });
            }
            set_columns_width();

            $(document).ajaxComplete(function (event, jqxhr, settings) {
                //noinspection JSLint
                if ("undefined" === typeof settings.data) {
                    return;
                }
                if (0 <= settings.data.indexOf("action=hidden-columns&")) {
                    set_columns_width();
                }
            });

            $("body").on("change", ".wpglobus-translate.wpglobus-tax-name", function (event) {
                var id = "#" + event.currentTarget.id;
                var $this = $(this);
                var order = {};
                if ($this.hasClass("wpglobus-meta")) {
                    order["order"] = "save_meta";
                    order["meta"] = $this.data("meta");
                } else {
                    order["order"] = "save_term";
                }
                order["full_id"] = $this.attr("id");
                order["id"] = $this.data("id");
                order["language"] = $this.data("language");
                order["taxonomy"] = $this.data("taxonomy");
                order["value"] = $this.val();
                order["wc_type"] = $this.data("wc-type");
                $(id).css({"background-color": "#dadada"});
                $.ajax({
                    type: "POST",
                    url: WPGlobusWC.ajaxurl,
                    data: {action: WPGlobusWC.process_ajax, order: order},
                    dataType: "json"
                })
                    .done(function (result) {
                        $(id).css({"background-color": "#fff"});
                        if (result.error) {
                            $(id).css({"background-color": "#f99"});
                        } else {
                            $(id).effect("highlight", {color: "#00ff00"}, 1000, function () {
                            });
                            $("#" + result.parent + " .column-source").text(result.source);
                        }
                    })
                    .fail(function (error) {
                        $(id).css({"background-color": "#f99"});
                    })
                    .always(function (jqXHR, status) {
                    });
            });
        }

        var set_description_edit_form = function () {

            var $this = null, id = 0,
                dialog_title = $("#wpglobus-wc-edit-description-form").attr("title"),
                description = $("#description"),
                description_2 = $("#description_2"),
                allFields = $([]).add(description).add(description_2);

            $(".description").on("click", function () {
                $this = $(this);
                dialog.dialog("open");
            });

            var saveDescription = function () {
                allFields.removeClass("ui-state-error");
                var valid = true;

                var order = {};
                order["order"] = "save_description";
                order["data-wc-type"] = $this.data("wc-type");
                order["taxonomy"] = $this.data("taxonomy");
                order["id"] = $this.data("id");
                order["field"] = {};

                $.each(allFields, function (i, e) {
                    var $e = $(e);
                    order["field"][i] = {};
                    order["field"][i]["type"] = "textarea";
                    order["field"][i]["language"] = $e.data("language");
                    order["field"][i]["value"] = $e.val();

                });
                $(".ui-dialog-buttonset").css({"visibility": "hidden"});
                $.ajax({
                    type: "POST",
                    url: WPGlobusWC.ajaxurl,
                    data: {action: WPGlobusWC.process_ajax, order: order},
                    dataType: "json"
                })
                    .done(function (result) {
                        if (result.result == "error") {
                            $(allFields).effect("highlight", {color: "#f00"}, 1000, function () {
                            });
                        } else {
                            $(allFields).effect("highlight", {color: "#0f0"}, 1000, function () {
                            });
                            $.each(result.ids, function (i, id) {
                                if (result.value[id] == "") {
                                    $("#" + id + " span.dashicons")
                                        .removeClass("dashicons-edit wp-ui-text-highlight")
                                        .addClass("dashicons-plus-alt wp-ui-text-notification")
                                        .attr("title", "Click to add description");
                                    $("#" + id + " span.text").text("");

                                } else {
                                    $("#" + id + " span.dashicons")
                                        .removeClass("dashicons-plus-alt wp-ui-text-notification")
                                        .addClass("dashicons-edit wp-ui-text-highlight")
                                        .attr("title", result.value[id]);
                                    $("#" + id + " span.text").text(result.value[id]);
                                }

                            });
                        }
                    })
                    .fail(function (error) {
                        $(allFields).effect("highlight", {color: "#f00"}, 1000, function () {
                        });
                    })
                    .always(function (jqXHR, status) {
                        $(".ui-dialog-buttonset").css({"visibility": "visible"});
                    });
                return valid;
            }
            var dialog = $("#wpglobus-wc-edit-description-form").dialog({
                autoOpen: false,
                height: 450,
                width: 650,
                modal: true,
                buttons: [
                    {
                        text: "Save",
                        class: "wpglobus-button-save",
                        click: function () {
                            saveDescription();
                            dialog.dialog("close");
                        }
                    },
                    {
                        text: "Cancel",
                        class: "wpglobus-button-cancel",
                        click: function () {
                            dialog.dialog("close");
                        }
                    }
                ],
                open: function () {

                    id = $this.data("id");

                    $(".ui-dialog-title").text(dialog_title + " " + $this.data("term-name"));
                    var l = $this.data("language");

                    description.attr("placeholder", WPGlobusWC.en_language_name[WPGlobusWC.default_language]);
                    description.attr("data-language", WPGlobusWC.default_language);
                    description.attr("data-id", id);

                    var d = $.trim($("#description-" + WPGlobusWC.default_language + id).text());
                    if (l == WPGlobusWC.default_language) {
                        l = $("#wpglobus-table-wrapper").data("second-language");
                    }
                    description_2.attr("placeholder", WPGlobusWC.en_language_name[l]);
                    description_2.attr("data-language", l);
                    description_2.attr("data-id", id);
                    var d2 = $.trim($("#description-" + l + id).text());
                    description.val(d);
                    description_2.val(d2);
                },
                close: function () {
                    form[0].reset();
                    allFields.removeClass("ui-state-error");
                }
            });
            var form = dialog.find("form#edit-description").on("submit", function (event) {
                event.preventDefault();
                saveDescription();
            });

        }

        if ("product-attributes" == WPGlobusWC.page) {
            product_attributes();
        }

        if ("translations" == WPGlobusWC.page) {
            set_float_header($(".wpglobus-wc-translations thead").offset().top - 10);
            set_description_edit_form();
            translations_table();
        }

        if ("post-edit" == WPGlobusWC.page) {
            post_edit();
            WPGlobusDialogApp.addElement("_purchase_note");
            $("#_purchase_note").addClass("wpglobus-translatable");
        }

        /**
         * @todo Check not only page, but also tab and section, and add only elements for that
         * admin.php?page=wc-settings&tab=shipping&section=wc_shipping_flat_rate
         */
        if ("wc-settings" === WPGlobusWC.page) {

            /**
             * Add UEdit elements to the page.
             * Assume that there is a label associated with each element and grab the text from it.
             * @param array elementIds
             */
            var addElements = function (elementIds) {
                jQuery.each(elementIds, function (i, elementId) {
                    /**
                     * Check if the element ID exists in DOM and then add it.
                     */
                    if (jQuery(elementId)) {
                        WPGlobusDialogApp.addElement({
                            id: elementId,
                            dialogTitle: jQuery("label[for='" + elementId + "']").text()
                        });
                    }
                });
            }

            addElements([
                "woocommerce_demo_store_notice",
                /**
                 * Tax options
                 */
                "woocommerce_price_display_suffix",
                /**
                 * Standard payment gateways
                 */
                "woocommerce_bacs_title",
                "woocommerce_bacs_description",
                "woocommerce_bacs_instructions",
                "woocommerce_cheque_title",
                "woocommerce_cheque_description",
                "woocommerce_cheque_instructions",
                "woocommerce_cod_title",
                "woocommerce_cod_description",
                "woocommerce_cod_instructions",
                "woocommerce_paypal_title",
                "woocommerce_paypal_description",
                "woocommerce_paypal_express_title",
                "woocommerce_paypal_express_description",
                /**
                 * Stripe
                 * @since 3.4.0
                 */
                "woocommerce_stripe_title",
                "woocommerce_stripe_description",
                "woocommerce_stripe_bancontact_title",
                "woocommerce_stripe_bancontact_description",
                "woocommerce_stripe_sofort_title",
                "woocommerce_stripe_sofort_description",
                "woocommerce_stripe_giropay_title",
                "woocommerce_stripe_giropay_description",
                "woocommerce_stripe_eps_title",
                "woocommerce_stripe_eps_description",
                "woocommerce_stripe_ideal_title",
                "woocommerce_stripe_ideal_description",
                "woocommerce_stripe_p24_title",
                "woocommerce_stripe_p24_description",
                "woocommerce_stripe_alipay_title",
                "woocommerce_stripe_alipay_description",
                "woocommerce_stripe_sepa_title",
                "woocommerce_stripe_sepa_description",
                "woocommerce_stripe_bitcoin_title",
                "woocommerce_stripe_bitcoin_description",
                "woocommerce_stripe_multibanco_title",
                "woocommerce_stripe_multibanco_description",
                /**
                 * Shipping methods
                 */
                "woocommerce_flat_rate_title",
                "woocommerce_flat_rate_options",
                "woocommerce_free_shipping_title",
                "woocommerce_international_delivery_title",
                "woocommerce_local_delivery_title",
                "woocommerce_local_pickup_title",
                /**
                 * Accounts & Privacy
                 * @since 3.4.0
                 */
                "woocommerce_registration_privacy_policy_text",
                "woocommerce_checkout_privacy_policy_text",
                /**
                 * Email options
                 */
                "woocommerce_email_from_name",
                "woocommerce_email_footer_text",
                /**
                 * Standard WC emails
                 */
                "woocommerce_new_order_subject",
                "woocommerce_new_order_heading",
                "woocommerce_cancelled_order_subject",
                "woocommerce_cancelled_order_heading",
                "woocommerce_failed_order_subject",
                "woocommerce_failed_order_heading",
                "woocommerce_customer_on_hold_order_subject",
                "woocommerce_customer_on_hold_order_heading",
                "woocommerce_customer_processing_order_subject",
                "woocommerce_customer_processing_order_heading",
                "woocommerce_customer_completed_order_subject",
                "woocommerce_customer_completed_order_heading",
                "woocommerce_customer_completed_order_subject_downloadable",
                "woocommerce_customer_completed_order_heading_downloadable",
                "woocommerce_customer_refunded_order_subject_full",
                "woocommerce_customer_refunded_order_subject_partial",
                "woocommerce_customer_refunded_order_heading_full",
                "woocommerce_customer_refunded_order_heading_partial",
                "woocommerce_customer_invoice_subject",
                "woocommerce_customer_invoice_heading",
                "woocommerce_customer_invoice_subject_paid",
                "woocommerce_customer_invoice_heading_paid",
                "woocommerce_customer_note_subject",
                "woocommerce_customer_note_heading",
                "woocommerce_customer_reset_password_subject",
                "woocommerce_customer_reset_password_heading",
                "woocommerce_customer_new_account_subject",
                "woocommerce_customer_new_account_heading",
                "woocommerce_new_renewal_order_subject",
                "woocommerce_new_renewal_order_heading",
                /**
                 * WC Subscriptions plugin
                 */
                "woocommerce_new_switch_order_subject",
                "woocommerce_new_switch_order_heading",
                "woocommerce_customer_processing_renewal_order_subject",
                "woocommerce_customer_processing_renewal_order_heading",
                "woocommerce_customer_completed_renewal_order_subject",
                "woocommerce_customer_completed_renewal_order_heading",
                "woocommerce_customer_completed_renewal_order_subject_downloadable",
                "woocommerce_customer_completed_renewal_order_heading_downloadable",
                "woocommerce_customer_completed_switch_order_subject",
                "woocommerce_customer_completed_switch_order_heading",
                "woocommerce_customer_completed_switch_order_subject_downloadable",
                "woocommerce_customer_completed_switch_order_heading_downloadable",
                "woocommerce_customer_renewal_invoice_subject",
                "woocommerce_customer_renewal_invoice_heading",
                "woocommerce_customer_renewal_invoice_subject_paid",
                "woocommerce_customer_renewal_invoice_heading_paid",
                "woocommerce_cancelled_subscription_subject",
                "woocommerce_cancelled_subscription_heading",
                "woocommerce_expired_subscription_subject",
                "woocommerce_expired_subscription_heading",
                "woocommerce_suspended_subscription_subject",
                "woocommerce_suspended_subscription_heading",
                "woocommerce_subscriptions_add_to_cart_button_text",
                "woocommerce_subscriptions_order_button_text"
            ]);


        }

    };
    wpglobus_wc();

    /**
     * @since 1.4.0
     */
    if (typeof(WPGlobusWC) === "undefined") {
        return;
    }

    var api = {
        init: function () {
            api.addListeners();
        },
        addListeners: function () {
            if ("post-edit" == WPGlobusWC.page) {
                $(document).ajaxComplete(function (event, jqxhr, settings) {
                    if (typeof settings.data === "undefined") {
                        return;
                    }
                    if (settings.data.indexOf("action=woocommerce_load_variations") >= 0 ||
                        settings.data.indexOf("action=woocommerce_add_variation") >= 0) {

                        setTimeout(function () {
                            $(".woocommerce_variable_attributes textarea[name^=variable_description]").each(function (i, e) {
                                var $e = $(e),
                                    attr,
                                    pa = $(".woocommerce_variations").data("attributes"),
                                    order = $e.attr("name").replace("variable_description", "");

                                $.each(pa, function (i, e) {
                                    attr = i;
                                });
                                $e.addClass("wpglobus-translatable");
								
								/**
								 * Height of parent div of variable description field for correct output next elements (@see variations tab).
								 * @since 3.0.2
								 */
								var wrapper = $e.parents('div');
								wrapper = wrapper[0];
								$(wrapper).addClass('wpglobus-wc-variation-description-wrapper');

                                WPGlobusDialogApp.addElement({
                                    id: $e.attr("name"),
                                    dialogTitle: "Variation Description: " + $('select[name="attribute_' + attr + order + '"] option:selected').text()
                                });
                            });
                        }, 500);

                    }
                });
            }
        }
    };

    WPGlobusWC = $.extend({}, WPGlobusWC, api);
    WPGlobusWC.init();

});
