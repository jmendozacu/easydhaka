<?php
class HTZ_Vendor_Block_Links extends Mage_Page_Block_Template_Links_Block
{
    /**
     * Position in link list
     * @var int
     */
    protected $_position = 1;

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if (!$flag && Mage::getStoreConfig('vendor/settings/vendor_link')) {
            $text = 'Vendor';
            $this->_label = $text;
            $this->_title = $text;
            $this->_url = $this->getUrl('vendor');
            return parent::_toHtml();
        }
        return '';
    }

}
