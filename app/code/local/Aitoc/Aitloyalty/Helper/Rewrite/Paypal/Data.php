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
class Aitoc_Aitloyalty_Helper_Rewrite_Paypal_Data
    extends Mage_Paypal_Helper_Data
{
    protected $_salesEntity;
    protected $_items;
    protected $_totals;
    protected $_discountAmount;
    /**
     * Get line items and totals from sales quote or order
     *
     * PayPal calculates grand total by this formula:
     * sum(item_base_price * qty) + subtotal + shipping + shipping_discount
     * where subtotal doesn't include anything, shipping_discount is negative
     * the items discount should go as separate cart line item with negative amount
     * the shipping_discount is outlined in PayPal API docs, but ignored for some reason. Hence commented out.
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $salesEntity
     * @return array (array of $items, array of totals, $discountTotal, $shippingTotal)
     */
    public function prepareLineItems(Mage_Core_Model_Abstract $salesEntity, $discountTotalAsItem = true, $shippingTotalAsItem = false)
    {
        $this->_salesEntity = $salesEntity;
        foreach ($salesEntity->getAllItems() as $item) {
            if (!$item->getParentItem()) {
                $this->_items[] = new Varien_Object($this->_prepareLineItemFields($salesEntity, $item));
            }
        }
        $this->_discountAmount = 0; // this amount always includes the shipping discount
        $shippingDescription = '';
        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            $this->_discountAmount = abs(1 * $salesEntity->getBaseDiscountAmount());
            $shippingDescription = $salesEntity->getShippingDescription();
            $this->_totals = array(
                'subtotal' => $this->_getSubtotal(),
                'tax'      => $salesEntity->getBaseTaxAmount(),
                'shipping' => $salesEntity->getBaseShippingAmount(),
                'discount' => $this->_discountAmount,
//                'shipping_discount' => -1 * abs($salesEntity->getBaseShippingDiscountAmount()),
            );
        } else {
            $address = $salesEntity->getIsVirtual() ? $salesEntity->getBillingAddress() : $salesEntity->getShippingAddress();
            $this->_discountAmount = abs(1 * $address->getBaseDiscountAmount());
            $shippingDescription = $address->getShippingDescription();
            $this->_totals = array (
                'subtotal' => $this->_getSubtotal(),
                'tax'      => $address->getBaseTaxAmount(),
                'shipping' => $address->getBaseShippingAmount(),
                'discount' => $this->_discountAmount,
//                'shipping_discount' => -1 * abs($address->getBaseShippingDiscountAmount()),
            );
        }
        //add surcharge if available
        $this->_setSurcharge();
        // discount total as line item (negative)
        if ($discountTotalAsItem && $this->_discountAmount) {
            $this->_items[] = new Varien_Object(array(
                'name'   => Mage::helper('paypal')->__('Discount'),
                'qty'    => 1,
                'amount' => -1.00 * $this->_discountAmount,
            ));
        }
        // shipping total as line item
        if ($shippingTotalAsItem && (!$salesEntity->getIsVirtual()) && (float)$this->_totals['shipping']) {
            $this->_items[] = new Varien_Object(array(
                'id'     => Mage::helper('paypal')->__('Shipping'),
                'name'   => $shippingDescription,
                'qty'    => 1,
                'amount' => (float)$this->_totals['shipping'],
            ));
        }
        return array($this->_items, $this->_totals, $this->_discountAmount, $this->_totals['shipping']);
    }

    private function _setSurcharge()
    {
        $discount = $this->_getOriginalDiscount();
        if($discount > 0)
        {
            $this->_addSurchargeItem($discount);
            $this->_deleteDiscount();
        }
    }
    
    private function _addSurchargeItem($value)
    {
        $this->_items[] = new Varien_Object(array(
                'name'   => Mage::helper('paypal')->__('Surcharge'),
                'qty'    => 1,
                'amount' => abs($value),
            ));
    }
    
    private function _getOriginalDiscount()
    {
        if ($this->_salesEntity instanceof Mage_Sales_Model_Order) 
        {
            $discount = $this->_salesEntity->getBaseDiscountAmount();
        }                    
        else 
        {
            $address = $this->_salesEntity->getIsVirtual() ?
                $this->_salesEntity->getBillingAddress() : $this->_salesEntity->getShippingAddress();
            $discount = $address->getBaseDiscountAmount(); 
        }
        return $discount;
    }
    
    private function _deleteDiscount()
    {
        $amount = -($this->_getOriginalDiscount());
        $this->_updateTotals('discount', $amount);
        $this->_discountAmount = 0;
    }
    
    private function _updateTotals($code, $amount)
    {
        if (isset($this->_totals[$code])) 
        {
            $this->_totals[$code] += $amount;
        }   
    }
    
    private function _getSubtotal()
    {
        $discount = $this->_getOriginalDiscount();
        $subtotal = $this->_salesEntity->getBaseSubtotal() + $discount;
        return $subtotal;
    }
}