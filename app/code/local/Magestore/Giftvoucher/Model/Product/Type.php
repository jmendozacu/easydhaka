<?php

class Magestore_Giftvoucher_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract {

    protected $_canConfigure = true;

    public function prepareForCart(Varien_Object $buyRequest, $product = null) {
        if (version_compare(Mage::getVersion(), '1.5.0', '>='))
            return parent::prepareForCart($buyRequest, $product);
        if (is_null($product))
            $product = $this->getProduct();
        $result = parent::prepareForCart($buyRequest, $product);
        if (is_string($result))
            return $result;
        reset($result);
        $product = current($result);
        $result = $this->_prepareGiftVoucher($buyRequest, $product);
        return $result;
    }

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode) {
        if (version_compare(Mage::getVersion(), '1.5.0', '<'))
            return parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_null($product))
            $product = $this->getProduct();
        if (!$buyRequest->getData('send_friend')) {
            $fields = array('recipient_name', 'recipient_email', 'message', 'day_to_send', 'recipient_ship', 'recipient_address', 'notify_success');
            foreach ($fields as $field) {
                if ($buyRequest->getData($field))
                    $buyRequest->unsetData($field);
            }
        }
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_string($result))
            return $result;
        reset($result);
        $product = current($result);
        $result = $this->_prepareGiftVoucher($buyRequest, $product);
        return $result;
    }

    protected function _prepareGiftVoucher(Varien_Object $buyRequest, $product) {
        $store = Mage::app()->getStore();
        $amount = $buyRequest->getAmount();
        $fnPrice = 0;
        if ($amount) {
            $giftAmount = Mage::helper('giftvoucher/giftproduct')->getGiftValue($product);
            switch ($giftAmount['type']) {
                case 'range':
                    if ($amount < $giftAmount['from'])
                        $amount = $giftAmount['from'];
                    if ($amount > $giftAmount['to'])
                        $amount = $giftAmount['to'];
                    $fnPrice = $amount;
                    if ($giftAmount['gift_price_type'] == 'percent') {
                        $fnPrice = $fnPrice * $giftAmount['gift_price_options'] / 100;
                    }
                    break;
                case 'dropdown':
                    if (!in_array($amount, $giftAmount['options']))
                        $amount = $giftAmount['options'][0];
                    $fnPrices = array_combine($giftAmount['options'], $giftAmount['prices']);
                    $fnPrice = $fnPrices[$amount];
                    break;
                case 'static':
                    if ($amount != $giftAmount['value'])
                        $amount = $giftAmount['value'];
                    $fnPrice = $giftAmount['gift_price'];
                    break;
                default:
                    return Mage::helper('giftvoucher')->__('Please enter gift card information');
            }
        } else
            return Mage::helper('giftvoucher')->__('Please enter gift card information');

        $buyRequest->setAmount($amount);
        $product->addCustomOption('price_amount', $fnPrice);

        foreach (Mage::helper('giftvoucher')->getFullGiftVoucherOptions() as $key => $label) {
            if ($value = $buyRequest->getData($key))
                $product->addCustomOption($key, $value);
        }
        if (!Mage::registry('haitv_product_' . $product->getId()))
            Mage::register('haitv_product_' . $product->getId(), $product);
        return array($product);
    }

    public function isVirtual($product = null) {
        if (is_null($product))
            $product = $this->getProduct();
        if (!Mage::helper('giftvoucher')->getInterfaceConfig('postoffice', $product->getStoreId()))
            return true;

        $productOption = $this->getProduct($product)->getCustomOption('recipient_ship');
        if (!$productOption)
            return true;
        else
            return false;

//        $item = Mage::getModel('checkout/session')->getQuote()->getItemByProduct($product);
//        if (!$item)
//            return false;
//
//        $options = array();
//        foreach ($item->getOptions() as $option)
//            $options[$option->getCode()] = $option->getValue();
//        if (empty($options['recipient_ship']))
//            return true;
//
//        return false;
    }

    public function hasRequiredOptions($product = null) {
        return true;
        //if ($this->getProduct($product)->getPrice() == 0)
        //	return true;
        //return false;
    }

    public function canConfigure($product = null) {
        return TRUE;
    }

}
