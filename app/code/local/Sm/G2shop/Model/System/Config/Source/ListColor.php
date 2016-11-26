<?php
/*------------------------------------------------------------------------
 # SM Zen - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_G2shop_Model_System_Config_Source_ListColor
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'lavender', 'label'=>Mage::helper('g2shop')->__('Lavender')),
		array('value'=>'orange', 'label'=>Mage::helper('g2shop')->__('Orange')),
		array('value'=>'grapefruit', 'label'=>Mage::helper('g2shop')->__('Grapefruit')),
		array('value'=>'green', 'label'=>Mage::helper('g2shop')->__('Green')),
		array('value'=>'blue', 'label'=>Mage::helper('g2shop')->__('Blue')),
		array('value'=>'yellow', 'label'=>Mage::helper('g2shop')->__('Yellow')),
		array('value'=>'pinkrose', 'label'=>Mage::helper('g2shop')->__('Pinkrose')),
		array('value'=>'deepgrass', 'label'=>Mage::helper('g2shop')->__('Deepgrass')),
		array('value'=>'green_yellow', 'label'=>Mage::helper('g2shop')->__('Green - Yellow')),
		array('value'=>'lavender_pink', 'label'=>Mage::helper('g2shop')->__('Lavender - Pink')),
		array('value'=>'green_cyan', 'label'=>Mage::helper('g2shop')->__('Green - Cyan')),
		array('value'=>'cyan_red', 'label'=>Mage::helper('g2shop')->__('Cyan - Red'))
		);
	}
}
