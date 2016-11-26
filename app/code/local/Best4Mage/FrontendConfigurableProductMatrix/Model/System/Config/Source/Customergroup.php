<?php

class Best4Mage_FrontendConfigurableProductMatrix_Model_System_Config_Source_Customergroup extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
		return Mage::getResourceModel('customer/group_collection')->loadData()->toOptionArray();
	}
	
	public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}