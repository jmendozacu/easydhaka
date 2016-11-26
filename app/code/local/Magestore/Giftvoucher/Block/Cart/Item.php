<?php

class Magestore_Giftvoucher_Block_Cart_Item extends Mage_Checkout_Block_Cart_Item_Renderer {

    public function getProductOptions() {
        $options = parent::getProductOptions();
        //Hai.Tran 28/11
        foreach (Mage::helper('giftvoucher')->getGiftVoucherOptions() as $code => $label)
            if ($option = $this->getItem()->getOptionByCode($code)) {
                if ($code == 'giftcard_template_id') {
                    $valueTemplate = Mage::getModel('giftvoucher/gifttemplate')->load($option->getValue());
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->htmlEscape($valueTemplate->getTemplateName() ? $valueTemplate->getTemplateName() : $option->getValue()),
                    );
                } else if ($code == 'amount') {
                    $options[] = array(
                        'label' => $label,
                        'value' => Mage::helper('core')->formatPrice($option->getValue()),
                    );
                } else {
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->htmlEscape($option->getValue()),
                    );
                }
            }
        return $options;
    }
    
    public function getProductThumbnail() {
        if(!Mage::helper('giftvoucher')->getInterfaceCheckoutConfig('display_image_item') || $this->getProduct()->getTypeId() != 'giftvoucher') 
            return parent::getProductThumbnail();
        $item = $this->getItem();
        if($item->getOptionByCode('giftcard_template_image')) $filename = $item->getOptionByCode('giftcard_template_image')->getValue();
        else $filename = 'default.png';
        if($item->getOptionByCode('giftcard_use_custom_image') && $item->getOptionByCode('giftcard_use_custom_image')->getValue()) {
            $urlImage = '/tmp/giftvoucher/images/' . $filename;
            $filename = 'custom/'.$filename;
        }else {
            if ($item->getOptionByCode('giftcard_template_id')) {
                $templateId = $item->getOptionByCode('giftcard_template_id')->getValue();
                $designPattern = Mage::getModel('giftvoucher/gifttemplate')->load($templateId)->getDesignPattern();
                if ($designPattern == Magestore_Giftvoucher_Model_Designpattern::PATTERN_LEFT)
                    $filename = 'left/'.$filename;
                else if ($designPattern == Magestore_Giftvoucher_Model_Designpattern::PATTERN_TOP)
                    $filename = 'top/'.$filename;
            }
            $urlImage = '/giftvoucher/template/images/' . $filename;
        }
        $imageUrl = Mage::getBaseDir('media') . str_replace("/", DS, $urlImage);
        
        if (!file_exists($imageUrl)){
                return parent::getProductThumbnail();
        }
        return $this->helper('giftvoucher')->getProductThumbnail($imageUrl, $filename, substr($urlImage, 1));
    }
}
