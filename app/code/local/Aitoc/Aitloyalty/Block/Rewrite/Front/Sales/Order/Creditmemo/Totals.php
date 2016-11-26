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
class Aitoc_Aitloyalty_Block_Rewrite_Front_Sales_Order_Creditmemo_Totals extends Mage_Sales_Block_Order_Creditmemo_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal('base_grandtotal');
        if ((float) $this->getSource()->getAdjustmentPositive()) {
            $total = new Varien_Object(array(
                'code'  => 'adjustment_positive',
                'value' => $this->getSource()->getAdjustmentPositive(),
                'label' => $this->__('Adjustment Refund')
            ));
            $this->addTotal($total);
        }
        if ((float) $this->getSource()->getAdjustmentNegative()) {
            $total = new Varien_Object(array(
                'code'  => 'adjustment_negative',
                'value' => $this->getSource()->getAdjustmentNegative(),
                'label' => $this->__('Adjustment Fee')
            ));
            $this->addTotal($total);
        }

        /**
         * Add discount
         */
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
                'value'     => ( Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion("<1.8.0.0") ? -1 : 1 ) * $this->getSource()->getDiscountAmount(),
                'base_value'=> ( Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion("<1.8.0.0") ? -1 : 1 ) * $this->getSource()->getBaseDiscountAmount(),
                'label'     => $discountLabel
            ));
        }

        return $this;
    }
}