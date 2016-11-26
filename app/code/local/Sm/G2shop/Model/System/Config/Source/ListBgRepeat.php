<?php
/*------------------------------------------------------------------------
 # SM Zen - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_G2shop_Model_System_Config_Source_ListBgRepeat
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'repeat', 'label'=>Mage::helper('g2shop')->__('repeat')),
			array('value'=>'repeat-x', 'label'=>Mage::helper('g2shop')->__('repeat-x')),
			array('value'=>'repeat-y', 'label'=>Mage::helper('g2shop')->__('repeat-y')),
			array('value'=>'no-repeat', 'label'=>Mage::helper('g2shop')->__('no-repeat'))
		);
	}
}
