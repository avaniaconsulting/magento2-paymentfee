define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * Get payment method title with fee
             * 
             * @returns {String}
             */
            getTitle: function () {
                var title = this._super();
                var paymentFeeConfig = window.checkoutConfig.mageprince_paymentfee;
                var shippingMethod = quote.shippingMethod();

                if (paymentFeeConfig && paymentFeeConfig.isEnabled) {
                    var fees = paymentFeeConfig.payment_fees;
                    var methodCode = this.item.method;

                    if (fees && fees[methodCode]) {
                        // Check if shipping method is Table Rate
                        if (shippingMethod && shippingMethod.carrier_code == 'tablerate') {
                            var feeAmount = fees[methodCode];
                            var feeTitle = paymentFeeConfig.title || 'Fee';
                            return title + ' + ' + feeAmount + ' ' + feeTitle;
                        }
                    }
                }

                return title;
            }
        });
    };
});
