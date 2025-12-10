<?php

/**
 * Mageprince
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageprince.com license that is
 * available through the world-wide-web at this URL:
 * https://mageprince.com/end-user-license-agreement
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageprince
 * @package     Mageprince_MageAI
 * @copyright   Copyright (c) Mageprince (https://mageprince.com/)
 * @license     https://mageprince.com/end-user-license-agreement
 */

namespace Mageprince\Paymentfee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Mageprince\Paymentfee\Helper\Data;
use Mageprince\Paymentfee\Model\Calculation\CalculatorFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class PaymentFeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * PaymentFeeConfigProvider constructor.
     *
     * @param Data $helper
     * @param Session $checkoutSession
     * @param CalculatorFactory $calculatorFactory
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Data $helper,
        Session $checkoutSession,
        CalculatorFactory $calculatorFactory,
        PriceHelper $priceHelper
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->calculatorFactory = $calculatorFactory;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get payment fee config
     *
     * @return array
     */
    public function getConfig()
    {
        $displayExclTax = $this->helper->displayExclTax();
        $displayInclTax = $this->helper->displayInclTax();

        $isDescription = $this->helper->isDescription();
        $description = $this->helper->getDescription();

        $paymentFees = [];
        if ($this->helper->isEnable()) {
            $quote = $this->checkoutSession->getQuote();
            $methodFees = $this->helper->getPaymentFee();

            foreach ($methodFees as $methodCode => $feeData) {
                // Temporarily set payment method to calculate fee
                $originalMethod = $quote->getPayment()->getMethod();
                $quote->getPayment()->setMethod($methodCode);

                try {
                    if ($this->helper->canApply($quote, true)) {
                        $feeAmount = $this->calculatorFactory->get()->calculate($quote);
                        if ($feeAmount > 0) {
                            $formattedFee = $this->priceHelper->currency($feeAmount, true, false);
                            $paymentFees[$methodCode] = $formattedFee;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore calculation errors
                }

                // Restore original method
                if ($originalMethod) {
                    $quote->getPayment()->setMethod($originalMethod);
                } else {
                    $quote->getPayment()->unsMethod();
                }
            }
        }

        $paymentFeeConfig = [
            'mageprince_paymentfee' => [
                'isEnabled' => $this->helper->isEnable(),
                'title' => $this->helper->getTitle(),
                'description' => $isDescription ? $description : false,
                'isTaxEnabled' => $this->helper->isTaxEnabled(),
                'displayBoth' => ($displayExclTax && $displayInclTax),
                'displayInclTax' => $this->helper->displayInclTax(),
                'displayExclTax' => $this->helper->displayExclTax(),
                'payment_fees' => $paymentFees
            ]
        ];

        return $paymentFeeConfig;
    }
}
