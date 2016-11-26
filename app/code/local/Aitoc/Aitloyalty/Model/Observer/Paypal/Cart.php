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
class Aitoc_Aitloyalty_Model_Observer_Paypal_Cart
    extends Aitoc_Aitloyalty_Model_Observer_Abstract
{
    private function _init($event)
    {
        $cart = $event->getPaypalCart();
        $this->setCart($cart);
    }
    
    public function process(Varien_Event_Observer $event)
    {
        $this->_init($event);
        $discount = $this->_getDiscount();
        if($discount > 0)
        {
            $this->_setSurcharge($discount);
            $this->_changeOriginalDiscount();
        }
        return; 
    }
    
    private function _getDiscount()
    {
        $salesEntity = $this->getCart()->getSalesEntity();
        if ($salesEntity instanceof Mage_Sales_Model_Order) 
        {
            $discount = $salesEntity->getBaseDiscountAmount();
        }                    
        else 
        {
            $address = $salesEntity->getIsVirtual() ?
                $salesEntity->getBillingAddress() : $salesEntity->getShippingAddress();
            $discount = $address->getBaseDiscountAmount(); 
        }
        return $discount;
    }
    
    private function _setSurcharge($value)
    {
        $this->getCart()->addItem(Mage::helper('paypal')->__('Surcharge'), 1, (float)$value,'surcharge');  
    }
    
    private function _changeOriginalDiscount()
    {
        $cart = $this->getCart();
        $name = $this->_getDiscountConstant();
        $value = -($this->_getDiscount());
        $cart->updateTotal($name, $value);
    }
    
    private function _getDiscountConstant()
    {
        return Mage_Paypal_Model_Cart::TOTAL_DISCOUNT;
    }
}