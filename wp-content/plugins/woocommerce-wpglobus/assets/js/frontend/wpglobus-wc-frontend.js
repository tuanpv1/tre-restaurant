/**
 * wpglobus-wc-frontend.js
 *
 * @package WooCommerce-WPGlobus
 */

/*jslint browser: true*/
/*global jQuery, woocommerce_params */
jQuery(
    /**
     * WooCommerce caches the shopping cart content in the HTML5 storage.
     * When WPGlobus switches language, WooCommerce does not know about it,
     * and continues showing the cached copy, in the previous language -
     * until a product is added to the cart, or removed from it.
     *
     * This code solves the problem by running WC's "fragment refresh" AJAX.
     *
     * @since 1.6.0
     */
    function ($) {
        "use strict";


        /**
         * Piece of code from cart-fragments.js responsible for refreshing the
         * shopping cart widget.
         */

        /**
         * Callback for AJAX success.
         *
         * @param {Object} data
         * @param {array} data.fragments
         * @param {string} data.cart_hash
         */
        function ajax_success(data) {
            if (data && data.fragments) {

                $.each(data.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });

                //noinspection JSLint
                if (typeof sessionStorage !== "undefined") {
                    sessionStorage.setItem("wc_fragments", JSON.stringify(data.fragments));
                    sessionStorage.setItem("wc_cart_hash", data.cart_hash);
                }

                $("body").trigger("wc_fragments_refreshed");
            }
        }

        var $fragment_refresh = {
            url: woocommerce_params.ajax_url,
            type: "POST",
            data: {action: "woocommerce_get_refreshed_fragments"},
            success: ajax_success
        };

        //noinspection JSLint
        /**
         * Hook the above code to the "language changed" event,
         * triggered by the main WPGlobus plugin.
         */
        $(document).on("wpglobus_current_language_changed", function (event, args) {
            $.ajax($fragment_refresh);
        });

    }
);

/* EOF */
