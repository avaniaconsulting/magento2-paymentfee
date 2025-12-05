let config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/select-payment-method': {
                'Mageprince_Paymentfee/js/action/payment/select-payment-method-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Mageprince_Paymentfee/js/view/payment/default-mixin': true
            }
        }
    }
};
