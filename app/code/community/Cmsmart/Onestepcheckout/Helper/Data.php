<?php

class Cmsmart_Onestepcheckout_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_VAT_FRONTEND_VISIBILITY = 'customer/create_account/vat_frontend_visibility';

    const XML_PATH_SHIPPING_VISIBILITY = 'onestepcheckout/default/show_shipping';

    const XML_PATH_COMMENT = 'onestepcheckout/default/comment';



    public function isEnable()
    {
        $status = $this->getConfigBuyStore('onestepcheckout/global/status');
        return $status;
    }

    public function isDeliveryEnable()
    {
        $status = $this->getConfigBuyStore('onestepcheckout/cmsmartdeliverydate/delivery_enable');
        return $status;
    }


    public function getAttributeValidationClass($attributeCode)
    {
        $attribute = isset($this->_attributes[$attributeCode]) ? $this->_attributes[$attributeCode]
            : Mage::getSingleton('eav/config')->getAttribute('customer_address', $attributeCode);
        $class = $attribute ? $attribute->getFrontend()->getClass() : '';

        if (in_array($attributeCode, array('firstname', 'middlename', 'lastname', 'prefix', 'suffix', 'taxvat'))) {
            if ($class && !$attribute->getIsVisible()) {
                $class = ''; 
            }

            $customerAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
            $class .= $customerAttribute && $customerAttribute->getIsVisible()
                ? $customerAttribute->getFrontend()->getClass() : '';
            $class = implode(' ', array_unique(array_filter(explode(' ', $class))));
        }

        return $class;
    }

    public function isVatAttributeVisible()
    {
        return (bool)$this->getConfigBuyStore(self::XML_PATH_VAT_FRONTEND_VISIBILITY);
    }


    public function isEnterprise()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }


    public function isShowShippingForm()
    {
        return (bool)$this->getConfigBuyStore(self::XML_PATH_SHIPPING_VISIBILITY);
    }

    public function isShowComment()
    {
        return $this->getConfigBuyStore(self::XML_PATH_COMMENT);
    }


    public function getAvailablePaymentMethods()
    {
        $payment_methods = array();
        $methods = Mage::app()->getLayout()->createBlock('checkout/onepage_payment_methods')->getMethods();
        foreach ($methods as $_method) {
            $_code = $_method->getCode();
            $payment_methods[] = $_code;
        }

        return $payment_methods;
    }

    public function getSelectedPaymentMethod()
    {
        return Mage::app()->getLayout()->createBlock('checkout/onepage_payment_methods')->getSelectedMethodCode();
    }

    public function checkUpdatedPaymentMethods($methods_before, $methods_after)
    {
        $selected_method_code = $this->getSelectedPaymentMethod();
        if (!in_array($selected_method_code, $methods_after))
            $selected_method_code = false;

        $pm_changed = false;
        if (count($methods_before) != count($methods_after))
            $pm_changed = true;

        $free_available = false;
        foreach ($methods_after as $_code) {
            if ($_code == 'free')
                $free_available = $_code;
            if (!$pm_changed) {
                if (!in_array($_code, $methods_before))
                    $pm_changed = true;
            }
        }

        if ($pm_changed) {
            $use_method = $selected_method_code;
            if ($free_available)
                $use_method = $free_available;
            return $use_method;
        }
        return -1;
    }

    //onestepcheckout_cmsmartdeliverydate_weekend_option
    public function getConfigWeekendOption()
    {
        return $this->getConfigBuyStore('onestepcheckout/cmsmartdeliverydate/weekend_option');
    }

    //onestepcheckout_cmsmartdeliverydate_disableday_option
    public function getConfigDisabledayOption()
    {
        return $this->getConfigBuyStore('onestepcheckout/cmsmartdeliverydate/disableday_option');
    }

    //onestepcheckout_cmsmartdeliverydate_asa_option
    public function getConfigAsaOption()
    {
        return $this->getConfigBuyStore('onestepcheckout/cmsmartdeliverydate/asa_option');
    }


    public function getConfigBuyStore($nameConfig)
    {
        $store_id = Mage::app()->getStore()->getStoreId();
        return Mage::getStoreConfig($nameConfig, $store_id);
    }

    /**
     * set delivery time methods
     *
     * @param array $data
     * @return object - method to use
     */

    public function setDeliverydate($data)
    {
        $modelDeliverydate = Mage::getModel('onestepcheckout/onestepcheckout')->load($data["sales_order_id"], 'sales_order_id');
        if ($modelDeliverydate->getId()) {
            return $modelDeliverydate;
        }
        $modelDeliverydate->setData('sales_order_id', $data['sales_order_id']);
        $modelDeliverydate->setData('deliverydate_date', $data['deliverydate_date']);
        $modelDeliverydate->setData('deliverydate_time', $data['deliverydate_time']);
        $modelDeliverydate->save();

        return $modelDeliverydate;
    }

}