<?php

class Best4Mage_FrontendConfigurableProductMatrix_Model_System_Config_Source_Secondposition extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
		return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Use Default')),
			array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Side By Side'))
        );
	}
	
	public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}