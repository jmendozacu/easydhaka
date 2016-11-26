<?php

class Best4Mage_FrontendConfigurableProductMatrix_Helper_Data extends Mage_Core_Helper_Abstract
{
	private function useProduct()
	{
		return intval($this->getConfig('product_level'));
	}
	
	public function isEnable($product)
	{
		if(Mage::app()->getRequest()->getControllerName() == 'cart') return 0;
		if($this->useProduct() == 1) return ($product->getFcpmEnable() == 1);
		else return ($this->getConfig('enable') == 1);
	}
	
	public function getMatrixTemplate($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmTemplate() == 1);
		else return ($this->getConfig('matrix_template') == 1);
	}
	
	public function getMatrixPosition($product)
	{
		if($this->useProduct() == 1){
			$return = $product->getFcpmTemplatePosition();
		} else {
			$return = $this->getConfig('matrix_position');
		}
		return $return;
	}
	
	public function isShowFDD($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowfdd() == 1);
		else return ($this->getConfig('showfdd') == 1);
	}
	
	public function isOnlyCheckBox($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmCheckbox() == 1);
		else return ($this->getConfig('only_checkbox') == 1);
	}
	
	public function isShowLink($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowLink() == 1);
		else return ($this->getConfig('show_link') == 1);
	}
	
	public function isShowStock($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowStock() == 1);
		else return ($this->getConfig('show_stock') == 1);
	}
	
	public function isShowRowTotal($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowRowtotal() == 1);
		else return ($this->getConfig('show_rowtotal') == 1);
	}
	
	public function isShowGrandTotal($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowGrandtotal() == 1);
		else return ($this->getConfig('show_grandtotal') == 1);
	}
	
	public function isSimplePriceEnabled($product)
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		
		if(array_key_exists('Best4Mage_ConfigurableProductsSimplePrices',$modulesArray)) {
			if($modulesArray['Best4Mage_ConfigurableProductsSimplePrices']->is('active')) {
				if($this->getConfig('product_level','cpsp','settings') == 1){
					return ($product->getCpspEnable() == 1);
				} else {
					return ($this->getConfig('enable','cpsp','settings') == 1);
				}
			}
		}
		return false;
	}
	
	public function isInEasyTab($product)
	{
		if($this->checkIfTabsUsedOrNot()){
			return true;
		}
		$tab = 'easy_tabs';
		$group = 'general';
		if($this->getConfig('enabled',$tab,$group) == 1){
			return ($this->getMatrixPosition($product) == 3);
		}
		return false;
	}
	
	public function useJquery()
	{
		return ($this->getConfig('jquery') == 1);
	}
	
	public function isShowProductImage($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmShowImage() == 1);
		else return ($this->getConfig('show_image') == 1);
	}
	
	public function isSecondMatrix($product)
	{
		if($this->useProduct() == 1) return ($product->getFcpmSecondAttribute() == 1);
		else return ($this->getConfig('second_attribute') == 1);
	}
	
	public function isCptpEnable()
	{
		return $this->getConfig('tier_base','cptp','settings');	
	}
	
	public function getIaddtocart()
	{
		return intval($this->getConfig('iaddtocart'));
	}
	
	public function isAcspEnable()
	{
		return ($this->getConfig('enabled','acsp','colorselectorplusgeneral') == 1);	
	}
	
	public function isReplacedWithSwatchs($attrId)
	{
		if($this->isAcspEnable()){
			if($this->getConfig('replace_swatch','fcpm','colorswatch') == 1){
				$swatch_attributes = array();
				$swatchattributes = $this->getConfig('colorattributes','acsp','colorselectorplusgeneral');
				$swatch_attributes = explode(",", $swatchattributes);
				if(in_array($attrId, $swatch_attributes)) return true;
			}
		}
		return false;
	}
	
	public function isCspImgSwatch()
	{
		return true;	
	}
	
	public function getSecondMatrixPosition($product)
	{
		if($this->useProduct() == 1){
			$return = $product->getFcpmSecondLayout();
		} else {
			$return = $this->getConfig('second_attribute_position');
		}
		return $return;
	}
	
	public function isCustomerShowMatrix()
	{
		$roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$availableRoleIds = explode(',',$this->getConfig('customer_roles'));
		if(in_array($roleId, $availableRoleIds)) return true;
		else return false;
	}
	
	public function isAdvancedCustomOptionEnabled()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		
		if(array_key_exists('MageWorx_CustomOptions',$modulesArray))
			if($modulesArray['MageWorx_CustomOptions']->is('active') && $this->getConfig('enabled','mageworx_catalog','customoptions') == 1)
				return true;

		return false;
	}
	
	public function checkIfTabsUsedOrNot()
	{
		$tabsObject = Mage::app()->getLayout()->getBlock('product.info.tabs');
		if($tabsObject || in_array(Mage::getSingleton('core/design_package')->getPackageName(),array('hellowired'))){
			return true;	
		} else {
			return false;	
		}
	}
		
	private function getConfig($fieldName, $fcpm = 'fcpm', $basic_options = 'basic_options')
	{
		return Mage::getStoreConfig($fcpm.'/'.$basic_options.'/'.$fieldName, Mage::app()->getStore());
	}
	
	public function getProductOptionsHtml(Mage_Catalog_Model_Product $product)
	{
		$blockOptionsHtml = null;
		if($product->getTypeId()=="simple"||$product->getTypeId()=="virtual"||$product->getTypeId()=="configurable")
		{  
			$blockOption = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Options");
			$blockOption->addOptionRenderer("default","catalog/product_view_options_type_default","frontendconfigurableproductmatrix/product/list/options/type/default.phtml");
			$blockOption->addOptionRenderer("text","catalog/product_view_options_type_text","frontendconfigurableproductmatrix/product/list/options/type/text.phtml");
			$blockOption->addOptionRenderer("file","catalog/product_view_options_type_file","frontendconfigurableproductmatrix/product/list/options/type/file.phtml");
			$blockOption->addOptionRenderer("select","frontendconfigurableproductmatrix/product_list_options_type_select","frontendconfigurableproductmatrix/product/list/options/type/select.phtml");
			$blockOption->addOptionRenderer("date","catalog/product_view_options_type_date","frontendconfigurableproductmatrix/product/list/options/type/date.phtml") ;
 
			$blockOption->setProduct($product);
			if($product->getOptions())
			{  
				foreach ($product->getOptions() as $o) 
				{     
					$blockOptionsHtml .= $blockOption->getOptionHtml($o); 
				};    
			}  
		}  
 		return $blockOptionsHtml; 
	}
}