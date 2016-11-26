<?php
/*------------------------------------------------------------------------
 # SM Zen - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_G2shop_Model_System_Config_Source_ListGoogleFont
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'', 'label'=>Mage::helper('g2shop')->__('No select')),
			array('value'=>'TitilliumWeb Bold', 'label'=>Mage::helper('g2shop')->__('TitilliumWeb Bold')),
			array('value'=>'Anton', 'label'=>Mage::helper('g2shop')->__('Anton')),
			array('value'=>'Questrial', 'label'=>Mage::helper('g2shop')->__('Questrial')),
			array('value'=>'Kameron', 'label'=>Mage::helper('g2shop')->__('Kameron')),
			array('value'=>'Oswald', 'label'=>Mage::helper('g2shop')->__('Oswald')),
			array('value'=>'Open Sans', 'label'=>Mage::helper('g2shop')->__('Open Sans')),
			array('value'=>'BenchNine', 'label'=>Mage::helper('g2shop')->__('BenchNine')),
			array('value'=>'Droid Sans', 'label'=>Mage::helper('g2shop')->__('Droid Sans')),
			array('value'=>'Droid Serif', 'label'=>Mage::helper('g2shop')->__('Droid Serif')),
			array('value'=>'PT Sans', 'label'=>Mage::helper('g2shop')->__('PT Sans')),
			array('value'=>'Vollkorn', 'label'=>Mage::helper('g2shop')->__('Vollkorn')),
			array('value'=>'Ubuntu', 'label'=>Mage::helper('g2shop')->__('Ubuntu')),
			array('value'=>'Neucha', 'label'=>Mage::helper('g2shop')->__('Neucha')),
			array('value'=>'Cuprum', 'label'=>Mage::helper('g2shop')->__('Cuprum'))	
		);
	}
}
