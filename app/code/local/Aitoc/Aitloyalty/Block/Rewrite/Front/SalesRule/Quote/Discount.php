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
 *
 * @copyright  Copyright (c) 2011 AITOC, Inc.
 * @package    Aitoc_Aitloyalty
 * @author lyskovets
 */
class Aitoc_Aitloyalty_Model_Rewrite_Front_SalesRule_Quote_Discount
    extends Mage_SalesRule_Model_Quote_Discount
{
     /**
     * Add discount/surcharge total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    { 
        $amount = $address->getDiscountAmount();
        $part = Mage::helper('aitloyalty/discount')->getTitlePart($amount);
        if ($amount!=0) {
            $description = $address->getDiscountDescription();
            if ($description) {
                $title = Mage::helper('sales')->__($part.' (%s)', $description);
            } else {
                $title = Mage::helper('sales')->__($part);
            }
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }

}