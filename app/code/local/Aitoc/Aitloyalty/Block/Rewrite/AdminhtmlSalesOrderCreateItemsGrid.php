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

/* AITOC static rewrite inserts start */
/* $meta=%default,Aitoc_Aitgroupedoptions% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitgroupedoptions')){
    class Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlSalesOrderCreateItemsGrid_Aittmp extends Aitoc_Aitgroupedoptions_Block_Rewrite_AdminhtmlSalesOrderCreateItemsGrid {} 
 }else{
    /* default extends start */
    class Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlSalesOrderCreateItemsGrid_Aittmp extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlSalesOrderCreateItemsGrid extends Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlSalesOrderCreateItemsGrid_Aittmp
{
	protected function _afterToHtml($html)
	{
		$html = str_replace('<th class="no-link">' . Mage::helper('sales')->__('Discount') . '</th>', '<th class="no-link">' . Mage::helper('sales')->__('Discount/Surcharge') . '</th>', $html);
		return $html;
	}
}