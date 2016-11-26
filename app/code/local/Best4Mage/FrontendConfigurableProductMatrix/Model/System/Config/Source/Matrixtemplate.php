<?php

class Best4Mage_FrontendConfigurableProductMatrix_Model_System_Config_Source_Matrixtemplate extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
		return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Template 1')),
			array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Template 2'))
        );
	}
	
	public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}