<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Supercategories_Model_System_Config_Source_ListFieldProducts
{
	public function toOptionArray()
	{
		return array(
		array('value'	=>		'name',		'label'=>Mage::helper('supercategories')->__('Name')),
		array('value'	=>		'lastest_product',		'label'=>Mage::helper('supercategories')->__('Lasted Product')),
		array('value'	=>		'top_rating',		'label'=>Mage::helper('supercategories')->__('Top rating')),
		array('value'	=>		'best_sales',		'label'=>Mage::helper('supercategories')->__('Best selling')),
		);
	}
}
