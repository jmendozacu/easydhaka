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
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesQuoteAddressTotalDiscount extends Mage_Sales_Model_Quote_Address_Total_Discount
{
   
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getDiscountAmount();
        if ($amount!=0) {
            if ($amount > 0)
            {
                $title = Mage::helper('sales')->__('Discount');
            } else 
            {
                $title = Mage::helper('sales')->__('Surcharge');
            }
            if ($code = $address->getCouponCode()) {
                if ($amount > 0)
                {
                    $title = Mage::helper('sales')->__('Discount (%s)', $code);
                } else 
                {
                    $title = Mage::helper('sales')->__('Surcharge (%s)', $code);
                }
            }
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>$title,
                'value'=>-$amount
            ));
        }
        return $this;
    }

}