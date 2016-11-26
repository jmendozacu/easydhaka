<?php
/**
 * Loyalty Program
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitloyalty
 * @version      2.3.20
 * @license:     SgbtEEmO4TN5qVIe4RyMXvFvrV2LxMVBSCZx9T3Gjw
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitloyalty_Block_Rewrite_Front_Sales_Order_Invoice_Totals extends Mage_Sales_Block_Order_Invoice_Totals
{
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = array();
        $this->_totals['subtotal'] = new Varien_Object(array(
            'code'  => 'subtotal',
            'value' => $source->getSubtotal(),
            'label' => $this->__('Subtotal')
        ));


        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((float) $source->getShippingAmount() || $source->getShippingDescription()))
        {
            $this->_totals['shipping'] = new Varien_Object(array(
                'code'  => 'shipping',
                'field' => 'shipping_amount',
                'value' => $this->getSource()->getShippingAmount(),
                'label' => $this->__('Shipping & Handling')
            ));
        }

        /**
         * Add discount
         */
        if (((float)$this->getSource()->getDiscountAmount()) != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = $this->__('Discount (%s)', $source->getDiscountDescription());
            } else {
                $discountLabel = $this->__('Discount');
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code'  => 'discount',
                'field' => 'discount_amount',
                'value' => $source->getDiscountAmount(),
                'label' => $discountLabel
            ));
        }
        $amount = (float) ( Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion("<1.8.0.0") ? -1 : 1 ) * $this->getSource()->getDiscountAmount();
        $part = Mage::helper('aitloyalty/discount')->getTitlePart($amount);
        if ($amount != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = $this->helper('sales')->__($part.' (%s)', $this->getSource()->getDiscountDescription());
            } else {
                $discountLabel = $this->helper('sales')->__($part);
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code'      => 'discount',
                'field' => 'discount_amount',
                'value'     => ( Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion("<1.8.0.0") ? -1 : 1 ) * $source->getDiscountAmount(),
                'label'     => $discountLabel
            ));
        }

        $this->_totals['grand_total'] = new Varien_Object(array(
            'code'  => 'grand_total',
            'field'  => 'grand_total',
            'strong'=> true,
            'value' => $source->getGrandTotal(),
            'label' => $this->__('Grand Total')
        ));

        /**
         * Base grandtotal
         */
        if ($this->getOrder()->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new Varien_Object(array(
                'code'  => 'base_grandtotal',
                'value' => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
                'label' => $this->__('Grand Total to be Charged'),
                'is_formated' => true,
            ));
        }
        return $this;
    }
}