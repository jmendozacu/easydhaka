<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Matrixsupercategories_Model_System_Config_Source_Easing
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'swing',				'label' => Mage::helper('matrixsupercategories')->__('swing')),
			array('value' => 'easeInQuad', 			'label' => Mage::helper('matrixsupercategories')->__('easeInQuad')),
			array('value' => 'easeOutQuad',			'label' => Mage::helper('matrixsupercategories')->__('easeOutQuad')),
			array('value' => 'easeInOutQuad', 		'label' => Mage::helper('matrixsupercategories')->__('easeInOutQuad')),
			array('value' => 'easeInCubic',			'label' => Mage::helper('matrixsupercategories')->__('easeInCubic')),
			array('value' => 'easeOutCubic', 		'label' => Mage::helper('matrixsupercategories')->__('easeOutCubic')),
			array('value' => 'easeInOutCubic',		'label' => Mage::helper('matrixsupercategories')->__('easeInOutCubic')),
			array('value' => 'easeInQuart', 		'label' => Mage::helper('matrixsupercategories')->__('easeInQuart')),
			array('value' => 'easeOutQuart',		'label' => Mage::helper('matrixsupercategories')->__('easeOutQuart')),
			array('value' => 'easeInOutQuart', 		'label' => Mage::helper('matrixsupercategories')->__('easeInOutQuart')),
			array('value' => 'easeInQuint',			'label' => Mage::helper('matrixsupercategories')->__('easeInQuint')),
			array('value' => 'easeOutQuint', 		'label' => Mage::helper('matrixsupercategories')->__('easeOutQuint')),
			array('value' => 'easeInOutQuint',		'label' => Mage::helper('matrixsupercategories')->__('easeInOutQuint')),
			array('value' => 'easeInSine', 			'label' => Mage::helper('matrixsupercategories')->__('easeInSine')),
			array('value' => 'easeOutSine',			'label' => Mage::helper('matrixsupercategories')->__('easeOutSine')),
			array('value' => 'easeInOutSine', 		'label' => Mage::helper('matrixsupercategories')->__('easeInOutSine')),
			array('value' => 'easeInExpo',			'label' => Mage::helper('matrixsupercategories')->__('easeInExpo')),
			array('value' => 'easeOutExpo', 		'label' => Mage::helper('matrixsupercategories')->__('easeOutExpo')),
			array('value' => 'easeInOutExpo',		'label' => Mage::helper('matrixsupercategories')->__('easeInOutExpo')),
			array('value' => 'easeInCirc', 			'label' => Mage::helper('matrixsupercategories')->__('easeInCirc')),
			array('value' => 'easeOutCirc',			'label' => Mage::helper('matrixsupercategories')->__('easeOutCirc')),
			array('value' => 'easeInOutCirc', 		'label' => Mage::helper('matrixsupercategories')->__('easeInOutCirc')),
			array('value' => 'easeInElastic',		'label' => Mage::helper('matrixsupercategories')->__('easeInElastic')),
			array('value' => 'easeOutElastic', 		'label' => Mage::helper('matrixsupercategories')->__('easeOutElastic')),
			array('value' => 'easeInOutElastic',	'label' => Mage::helper('matrixsupercategories')->__('easeInOutElastic')),
			array('value' => 'easeInBack', 			'label' => Mage::helper('matrixsupercategories')->__('easeInBack')),
			array('value' => 'easeOutBack',			'label' => Mage::helper('matrixsupercategories')->__('easeOutBack')),
			array('value' => 'easeInOutBack', 		'label' => Mage::helper('matrixsupercategories')->__('easeInOutBack')),
			array('value' => 'easeInBounce',		'label' => Mage::helper('matrixsupercategories')->__('easeInBounce')),
			array('value' => 'easeOutBounce', 		'label' => Mage::helper('matrixsupercategories')->__('easeOutBounce')),
			array('value' => 'easeInOutBounce',		'label' => Mage::helper('matrixsupercategories')->__('easeInOutBounce'))
		);
	}
}
