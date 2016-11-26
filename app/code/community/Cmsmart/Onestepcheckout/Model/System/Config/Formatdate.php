<?php 
class Cmsmart_OneStepCheckOut_Model_System_Config_Formatdate
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'m/d/Y', 'label'=>Mage::helper('adminhtml')->__('mm/dd/yyyy')),
            array('value'=>'d/m/Y', 'label'=>Mage::helper('adminhtml')->__('dd/mm/yyyy')),
        );
    }

}