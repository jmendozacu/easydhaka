<?php
/*------------------------------------------------------------------------
 # SM Zen - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_G2shop_Model_System_Config_Source_ListBodyFont
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'Arial', 'label'=>Mage::helper('g2shop')->__('Arial')),
			array('value'=>'Open Sans Regular', 'label'=>Mage::helper('g2shop')->__('Open Sans')),
			array('value'=>'Arial Black', 'label'=>Mage::helper('g2shop')->__('Arial-black')),
			array('value'=>'Courier New', 'label'=>Mage::helper('g2shop')->__('Courier New')),
			array('value'=>'Georgia', 'label'=>Mage::helper('g2shop')->__('Georgia')),
			array('value'=>'Impact', 'label'=>Mage::helper('g2shop')->__('Impact')),
			array('value'=>'Lucida Console', 'label'=>Mage::helper('g2shop')->__('Lucida-console')),
			array('value'=>'Lucida Grande', 'label'=>Mage::helper('g2shop')->__('Lucida-grande')),
			array('value'=>'Palatino', 'label'=>Mage::helper('g2shop')->__('Palatino')),
			array('value'=>'Tahoma', 'label'=>Mage::helper('g2shop')->__('Tahoma')),
			array('value'=>'Times New Roman', 'label'=>Mage::helper('g2shop')->__('Times New Roman')),	
			array('value'=>'Trebuchet', 'label'=>Mage::helper('g2shop')->__('Trebuchet')),	
			array('value'=>'Verdana', 'label'=>Mage::helper('g2shop')->__('Verdana'))		
		);
	}
}
