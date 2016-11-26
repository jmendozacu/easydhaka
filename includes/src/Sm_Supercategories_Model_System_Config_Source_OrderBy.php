<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Supercategories_Model_System_Config_Source_OrderBy
{
	public function toOptionArray()
	{
		return array(
			array('value'	=> 	'name', 			'label' => Mage::helper('supercategories')->__('Name')),
			array('value'	=> 	'entity_id',		'label' => Mage::helper('supercategories')->__('Id')),
			//array('value'	=> 	'position',		'label' => Mage::helper('supercategories')->__('Position')),
			array('value'	=> 	'created_at', 		'label' => Mage::helper('supercategories')->__('Created Date')),
			array('value'	=> 	'price', 			'label' => Mage::helper('supercategories')->__('Price')),
			array('value'	=> 	'lastest_product', 	'label' => Mage::helper('supercategories')->__('Lastest Product')),
			array('value'	=> 	'top_rating', 		'label' => Mage::helper('supercategories')->__('Top Rating')),
			array('value'	=> 	'most_reviewed',	'label' => Mage::helper('supercategories')->__('Most Reviews')),
			array('value'	=> 	'most_viewed',		'label' => Mage::helper('supercategories')->__('Most Viewed')),
			array('value'	=> 	'best_sales',		'label' => Mage::helper('supercategories')->__('Most Selling')),
		);
	}
}
