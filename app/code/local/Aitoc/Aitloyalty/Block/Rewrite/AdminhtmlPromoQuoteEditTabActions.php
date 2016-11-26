<?php
/**
 * Loyalty Program
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitloyalty
 * @version      2.3.20
 * @license:     SgbtEEmO4TN5qVIe4RyMXvFvrV2LxMVBSCZx9T3Gjw
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlPromoQuoteEditTabActions extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Actions
{
	
//	protected function _prepareLayout()
//	{
//		$this->getLayout()->getBlock('head')->addJs('test.js');
//		return parent::_prepareLayout();
//	}
	
	public function getFormHtml()
	{
		$sHtml = parent::getFormHtml();
		$sHtml .= '<script type="text/javascript" src="/js/index.php?c=auto&f=,aitoc/aitloyalty/aitloyalty.js"></script>';
		return $sHtml;
	}
}