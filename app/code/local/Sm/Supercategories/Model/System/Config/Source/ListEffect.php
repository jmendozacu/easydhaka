<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Supercategories_Model_System_Config_Source_ListEffect
{
	public function toOptionArray()
	{	
		return array(
		array('value'	=>		'slideLeft', 		'label'=>Mage::helper('supercategories')->__('Slide Left')),
		array('value'	=>		'slideRight', 	'label'=>Mage::helper('supercategories')->__('Slide Right')),
		array('value'	=>		'zoomOut', 	'label'=>Mage::helper('supercategories')->__('Zoom Out')),
		array('value'	=>		'zoomIn', 	'label'=>Mage::helper('supercategories')->__('Zoom In')),
		array('value'	=>		'flip', 	'label'=>Mage::helper('supercategories')->__('Flip')),
		array('value'	=>		'flipInX', 	'label'=>Mage::helper('supercategories')->__('Fip in Vertical')),
		array('value'	=>		'starwars', 	'label'=>Mage::helper('supercategories')->__('Star Wars')),
		array('value'	=>		'flipInY', 	'label'=>Mage::helper('supercategories')->__('Flip in Horizontal')),
		array('value'	=>		'bounceIn', 	'label'=>Mage::helper('supercategories')->__('Bounce In')),
		array('value'	=>		'fadeIn', 	'label'=>Mage::helper('supercategories')->__('Fade In')),
		array('value'	=>		'pageTop', 	'label'=>Mage::helper('supercategories')->__('Page Top')),
		);
	}
}
