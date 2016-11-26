<?php
class HTZ_Vendor_Block_Adminhtml_Catalog_Product_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	public function render(Varien_Object $row){
		$grid 	= new HTZ_Vendor_Block_Adminhtml_Catalog_Product_Grid();
		$url 	= $grid->getRowUrl($row);
		return "<a href='$url'>".Mage::helper('catalog')->__('Edit')."</a>";
	}

}