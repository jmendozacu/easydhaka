<?php

class Best4Mage_FrontendConfigurableProductMatrix_Model_System_Config_Source_Matrixposition extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
		/*return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Select')),
			array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Beneath product info')),
			array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('In Others Template')),
			array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Rendering In Content'))
        );*/
		
		return array(
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Position 1')),
			array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Position 2')),
			array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Position 3')),
			array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Position 4')),
        );
	}
	
	public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}