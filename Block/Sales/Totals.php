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
        // DEBUGGING: Log entry into initTotals
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/paymentfee.log');
        $logger = new \Zend_Log($writer);
        $logger->info('PAYMENTFEE: initTotals called');

        $parent = $this->getParentBlock();
        if (!$parent) {
            $logger->info('PAYMENTFEE: No parent block found');
            return $this;
        }
        $logger->info('PAYMENTFEE: Parent block class: ' . get_class($parent));

        $source = $this->getSource();
        if (!$source) {
            $logger->info('PAYMENTFEE: No source found on parent block');
            return $this;
        }
        $logger->info('PAYMENTFEE: Source class: ' . get_class($source));
        $logger->info('PAYMENTFEE: Payment Fee Value: ' . $source->getPaymentFee());

        $storeId = $source->getStoreId();

        if ($source->getPaymentFee() == 0) {
            $logger->info('PAYMENTFEE: Payment fee is 0, skipping');
            return $this;
        }

        $paymentFeeTitle = $this->helper->getTitle($storeId);

        $paymentFeeExclTax = $source->getPaymentFee();
        $basePaymentFeeExclTax = $source->getBasePaymentFee();
        $paymentFeeExclTaxTotal = [
            'code' => 'payment_fee',
            'strong' => false,
            'value' => $paymentFeeExclTax,
            'base_value' => $basePaymentFeeExclTax,
            'label' => $paymentFeeTitle,
        ];

        $paymentFeeInclTax = $paymentFeeExclTax + $source->getPaymentFeeTax();
        $basePaymentFeeInclTax = $basePaymentFeeExclTax + $source->getBasePaymentFeeTax();
        $paymentFeeInclTaxTotal = [
            'code' => 'payment_fee_incl_tax',
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
}
