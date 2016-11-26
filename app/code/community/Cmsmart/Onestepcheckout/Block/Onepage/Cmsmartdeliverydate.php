<?php

class Cmsmart_Onestepcheckout_Block_Onepage_Cmsmartdeliverydate extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
	public function getDisableWeekendOption()
    {
		return $weekendOption = explode(",", Mage::helper('onestepcheckout')->getConfigWeekendOption());
	}
	public function getDisabledayOption()
    {
		return $dayOption = explode(",", Mage::helper('onestepcheckout')->getConfigDisabledayOption());
	}
	public function getAsaOption()
    {
		return Mage::helper('onestepcheckout')->getConfigAsaOption();
	}
}