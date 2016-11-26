<?php
class Cmsmart_OneStepCheckOut_Block_Adminhtml_Onestepcheckout_Sales_Order_View_Tab_Info_Deliverydate extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function _getOrderId()
    {
        return Mage::registry('current_order')->getId();
    }
    public function _getDeliverydateData($_orderId)
    {
        return Mage::getModel('onestepcheckout/onestepcheckout')->getCollection()->addFieldToFilter('sales_order_id',$_orderId)->getFirstItem();
    }

}