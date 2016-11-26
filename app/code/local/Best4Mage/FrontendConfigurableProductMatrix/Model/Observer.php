<?php

class Best4Mage_FrontendConfigurableProductMatrix_Model_Observer
{
	private $_product = false;
	private $_position = false;
	
	public function insertCustomBlock($observer)
    {
		$_action = $observer->getAction();
		$_matix4Customer = Mage::helper('frontendconfigurableproductmatrix')->isCustomerShowMatrix();
		if($_action->getFullActionName() != 'catalog_product_view' || !$_matix4Customer) return;
		$_product = $this->getProduct();
		if(empty($_product) || $_product == null) return;
		
		if($_product->getTypeId() != 'configurable') return;
		
		$_name = $this->getPosition();
		
		if(($_name == 3 || $_name == 1) && Mage::helper('frontendconfigurableproductmatrix')->isInEasyTab($_product))
			$layout = '<reference name="product.info.tabs">
			<action method="addTab" translate="title" module="frontendconfigurableproductmatrix"><alias>matrix_tabbed</alias><title>Product Matrix</title><block>frontendconfigurableproductmatrix/product_view_matrix</block><template>frontendconfigurableproductmatrix/tab.phtml</template></action>
		</reference><reference name="product.info"><action method="unsetChild"><name>matrix</name></action></reference>';
		else
			$layout = '';
		
		if($layout != '') $observer->getLayout()->getUpdate()->addUpdate($layout);
    }
	
	public function updateProductOptionContainer(Varien_Event_Observer $observer){
        if($this->isAdmin()) return;
		$_product = $observer->getData('product');
		$_posMain = Mage::helper('frontendconfigurableproductmatrix')->getMatrixPosition($_product);
		$_matix4Customer = Mage::helper('frontendconfigurableproductmatrix')->isCustomerShowMatrix();
        $position = Mage::helper('frontendconfigurableproductmatrix')->getSecondMatrixPosition($_product);
		if((Mage::helper('frontendconfigurableproductmatrix')->isSecondMatrix($_product) && $position == 4) || ($_posMain == 2 && $_matix4Customer)) $_product->setData('options_container','container2');
		elseif($_posMain == 0 && $_matix4Customer) $_product->setData('options_container','container1');
    }
	
	public function getProduct()
	{
		if($this->_product === false)
		{
			$this->_product = Mage::registry('product');
		}
		return $this->_product;
	}
	
	public function getPosition()
	{
		if($this->_position === false)
		{
			$this->_position = Mage::helper('frontendconfigurableproductmatrix')->getMatrixPosition($this->_product);
		}
		return $this->_position;
	}
	
	public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }
}