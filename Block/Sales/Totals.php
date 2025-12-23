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

namespace Mageprince\Paymentfee\Block\Sales;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;
use Mageprince\Paymentfee\Helper\Data;

class Totals extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Totals constructor.
     * @param Template\Context $context
     * @param Data $helper
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->setTemplate('Magento_Sales::order/totals/default.phtml');
        parent::__construct($context, $data);
    }

    /**
     * Get source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        // Debug Logging
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/payment_fee_debug.log');
        $logger = new \Zend_Log($writer);
        $logger->info('Payment Fee Debug: initTotals called');
        $logger->info('Payment Fee Debug: My Block Name: ' . $this->getNameInLayout());

        $parent = $this->getParentBlock();
        if ($parent) {
            $logger->info('Payment Fee Debug: Parent Block Name: ' . $parent->getNameInLayout());
            $logger->info('Payment Fee Debug: Parent Block Class: ' . get_class($parent));
            $children = $parent->getChildNames();
            $logger->info('Payment Fee Debug: Parent Child Names: ' . implode(', ', $children));
            $child = $parent->getChildBlock('payment_fee');
            if ($child) {
                $logger->info('Payment Fee Debug: Parent found child "payment_fee"');
            } else {
                $logger->info('Payment Fee Debug: Parent DID NOT find child "payment_fee"');
            }
        } else {
            $logger->info('Payment Fee Debug: No Parent Block found!');
        }

        $source = $this->getSource();
        $storeId = $source->getStoreId();

        if ($source->getPaymentFee() == 0) {
            $logger->info('Payment Fee Debug: Fee is 0, skipping.');
            return $this;
        }

        $paymentFeeTitle = $this->helper->getTitle($storeId);

        $paymentFeeExclTax = $source->getPaymentFee();
        $basePaymentFeeExclTax = $source->getBasePaymentFee();
        $paymentFeeExclTaxTotal = [
            'code' => 'payment_fee',
            'block_name' => 'mageprince_paymentfee',
            'strong' => false,
            'value' => $paymentFeeExclTax,
            'base_value' => $basePaymentFeeExclTax,
            'label' => $paymentFeeTitle,
        ];

        $paymentFeeInclTax = $paymentFeeExclTax + $source->getPaymentFeeTax();
        $basePaymentFeeInclTax = $basePaymentFeeExclTax + $source->getBasePaymentFeeTax();
        $paymentFeeInclTaxTotal = [
            'code' => 'payment_fee_incl_tax',
            'block_name' => 'mageprince_paymentfee',
            'strong' => false,
            'value' => $paymentFeeInclTax,
            'base_value' => $basePaymentFeeInclTax,
            'label' => $paymentFeeTitle,
        ];

        if ($this->helper->displayExclTax($storeId) && $this->helper->displayInclTax($storeId)) {
            $inclTxt = __('Incl. Tax');
            $exclTxt = __('Excl. Tax');
            $paymentFeeInclTaxTotal['label'] .= ' ' . $inclTxt;
            $paymentFeeExclTaxTotal['label'] .= ' ' . $exclTxt;
        }

        if ($this->helper->displayExclTax($storeId)) {
            $parent->addTotal(
                $this->dataObjectFactory->create()->setData($paymentFeeExclTaxTotal),
                'shipping'
            );
        }

        if ($this->helper->displayInclTax($storeId)) {
            $parent->addTotal(
                $this->dataObjectFactory->create()->setData($paymentFeeInclTaxTotal),
                'shipping'
            );
        }

        return $this;
    }
    /**
     * Format total value based on order currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return string
     */
    public function formatValue($total)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/payment_fee_debug.log');
        $logger = new \Zend_Log($writer);
        $logger->info('Payment Fee Debug: formatValue called');

        if (!$total->getIsFormated()) {
            return $this->getParentBlock()->formatValue($total);
        }
        return $total->getValue();
    }

    /**
     * Get label properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
