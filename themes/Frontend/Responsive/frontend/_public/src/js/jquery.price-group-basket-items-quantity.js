;(function ($, window) {
    'use strict';

    $.plugin('swPriceGroupBasketItemsQuantity', {
        defaults: {
            getPriceGroupBasketItemsQuantityUrl: window.controller['ajax_get_price_group_basket_items_quantity'],
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            this.registerEvents();
        },

        /**
         * Registers all necessary event listeners.
         */
        registerEvents: function() {
            $.subscribe(this.getEventName('plugin/swAddArticle/onAddArticle'), $.proxy(this.onAddArticle, this));
        },

        /**
         * Updates the shown PriceGroupBasketItemsQuantity after adding an article to the basket.
         */
        onAddArticle: function () {
            var url = this.opts.getPriceGroupBasketItemsQuantityUrl;
            var element = this.$el[0];
            var ajaxData = {
                priceGroupId: element.dataset.pricegroupid,
            };

            $.ajax({
                data: ajaxData,
                dataType: 'json',
                method: 'GET',
                url: url,
                success: function (result) {
                    element.innerText = result.priceGroupBasketItemsQuantity;
                }
            });
        }
    });
})(jQuery, window);
