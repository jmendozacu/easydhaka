<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
$live 	= Mage::getStoreConfig('vendor/settings/livekey');
$flag 	= true;
if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
	$flag = false;
}  
if($flag){
	class HTZ_Vendor_Block_Adminhtml_Catalog_Product_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
	{
	}
} else {
	 
	class HTZ_Vendor_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
	{

		public function __construct()
		{
			parent::__construct();
			$this->setId('productGrid');
			$this->setDefaultSort('entity_id');
			$this->setDefaultDir('DESC');
			$this->setSaveParametersInSession(true);
			$this->setUseAjax(true);
			$this->setVarNameFilter('product_filter');

		}

		protected function _getStore()
		{
			$storeId = (int) $this->getRequest()->getParam('store', 0);
			return Mage::app()->getStore($storeId);
		}

		protected function _prepareCollection()
		{
		   
			$store = $this->_getStore();
			$collection = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToSelect('sku')
				->addAttributeToSelect('name')
				->addAttributeToSelect('vendor_user')
				->addAttributeToSelect('htz_vendor_product')
				->addAttributeToSelect('attribute_set_id')
				->addAttributeToSelect('type_id');

				
			$userType 	= Mage::helper('vendor')->getUserType();
			$dFlag 		= false;
			if($userType == '2'){
				$collection->addIdFilter($this->getVendorProductIds());
			} elseif($userType == '1'){
				$collection->addWebsiteFilter(Mage::helper('vendor')->getUserWebsiteIds());
			}
				
			if(!$store->getId() && !$this->vendorDefaultEnable() && ($userType == '2' || $userType == '1')){
				$userStore	= Mage::getModel('core/store')->load((int)$this->vendorDefaultStore());
				$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
				$collection->addStoreFilter($userStore);
				$collection->joinAttribute(
					'name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$adminStore
				);
				$collection->joinAttribute(
					'custom_name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$userStore->getId()
				);
				$collection->joinAttribute(
					'status',
					'catalog_product/status',
					'entity_id',
					null,
					'inner',
					$userStore->getId()
				);
				$collection->joinAttribute(
					'visibility',
					'catalog_product/visibility',
					'entity_id',
					null,
					'inner',
					$userStore->getId()
				);
				$collection->joinAttribute(
					'price',
					'catalog_product/price',
					'entity_id',
					null,
					'left',
					$userStore->getId()
				);
			}	
				
			if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
				$collection->joinField('qty',
					'cataloginventory/stock_item',
					'qty',
					'product_id=entity_id',
					'{{table}}.stock_id=1',
					'left');
			}
			
			if ($store->getId()) {
				//$collection->setStoreId($store->getId());
				$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
				$collection->addStoreFilter($store);
				$collection->joinAttribute(
					'name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$adminStore
				);
				$collection->joinAttribute(
					'custom_name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$store->getId()
				);
				$collection->joinAttribute(
					'status',
					'catalog_product/status',
					'entity_id',
					null,
					'inner',
					$store->getId()
				);
				$collection->joinAttribute(
					'visibility',
					'catalog_product/visibility',
					'entity_id',
					null,
					'inner',
					$store->getId()
				);
				$collection->joinAttribute(
					'price',
					'catalog_product/price',
					'entity_id',
					null,
					'left',
					$store->getId()
				);
			}
			else {
				if(($userType != '2' && $userType != '1') || ($this->vendorDefaultEnable() && ($userType == '2' || $userType == '1'))){
					$collection->addAttributeToSelect('price');
					$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
					$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
				}
			}

			$this->setCollection($collection);

			parent::_prepareCollection();
			$this->getCollection()->addWebsiteNamesToResult();
			return $this;
		}

		protected function getVendorProductIds()
		{
			$user 			= Mage::getSingleton('admin/session')->getUser();
			$attributeId 	= $user->getEmail();
			$productIds 	= array();		
			$storeIds 		= explode(',', $user->getStoreIds());			
			foreach($storeIds as $storeId){
				$collection = Mage::getModel('catalog/product')->getCollection()
																->addAttributeToSelect('vendor_user')
																->addStoreFilter(Mage::getModel('core/store')->load((int)$storeId))
																->addAttributeToFilter('vendor_user',array('eq'=>Mage::getResourceModel('catalog/product')->getAttribute('vendor_user')->getSource()->getOptionId($attributeId)));
				if(count($collection)>0){
					foreach($collection as $_collection){
						$productIds[] = $_collection->getId();
					}
				}
			}
			
			if(count($productIds)>0){
				return array_unique($productIds);
			} else {
				return array(0);
			}
			
		}

		protected function _addColumnFilterToCollection($column)
		{
			if ($this->getCollection()) {
				if ($column->getId() == 'websites') {
					$this->getCollection()->joinField('websites',
						'catalog/product_website',
						'website_id',
						'product_id=entity_id',
						null,
						'left');
				}
			}
			return parent::_addColumnFilterToCollection($column);
		}

		protected function _prepareColumns()
		{
			$userType	= Mage::helper('vendor')->getUserType();
			
			$this->addColumn('entity_id',
				array(
					'header'=> Mage::helper('catalog')->__('ID'),
					'width' => '50px',
					'type'  => 'number',
					'index' => 'entity_id',
			));
			
			if(($userType != '2' && $userType != '1') || ($this->vendorDefaultEnable() && ($userType == '2' || $userType == '1'))){
				$this->addColumn('name',
					array(
						'header'=> Mage::helper('catalog')->__('Name'),
						'index' => 'name',
				));
			}	
			
			$store = $this->_getStore();
			if ($store->getId()) {
				$this->addColumn('custom_name',
					array(
						'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
						'index' => 'custom_name',
				));
			} elseif(!$this->vendorDefaultEnable() && ($userType == '2' || $userType == '1')){
				$userStore = Mage::getModel('core/store')->load((int)$this->vendorDefaultStore());
				$this->addColumn('custom_name',
					array(
						'header'=> Mage::helper('catalog')->__('Name in %s', $userStore->getName()),
						'index' => 'custom_name',
				));
			}

			$this->addColumn('type',
				array(
					'header'=> Mage::helper('catalog')->__('Type'),
					'width' => '60px',
					'index' => 'type_id',
					'type'  => 'options',
					'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
			));

			$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
				->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
				->load()
				->toOptionHash();

			$this->addColumn('set_name',
				array(
					'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
					'width' => '100px',
					'index' => 'attribute_set_id',
					'type'  => 'options',
					'options' => $sets,
			));

			$this->addColumn('sku',
				array(
					'header'=> Mage::helper('catalog')->__('SKU'),
					'width' => '80px',
					'index' => 'sku',
			));
			
			$options 		= Mage::getSingleton('eav/config')->getAttribute('catalog_product','vendor_user')->getSource()->getAllOptions(false);
			$vendorOption 	= array();
			foreach($options as $option){$vendorOption[$option['label']] = $option['value'];}
			ksort($vendorOption);
			$vendorOption 	= array_flip($vendorOption);
			
			if($userType != '2'){
				$this->addColumn('vendor_user',
					array(
						'header'	=> Mage::helper('vendor')->__('Vendor User'),
						'width' 	=> '70px',
						'index' 	=> 'vendor_user',
						'type'  	=> 'options',
						'options' 	=> $vendorOption,
				));
				
				$this->addColumn('htz_vendor_product',
					array(
						'header'	=> Mage::helper('vendor')->__('Vendor Product Active'),
						'width' 	=> '70px',
						'index' 	=> 'htz_vendor_product',
						'type'		=> 'options',
						'options' 	=> array('1' => 'Yes', '0' => 'No'),
				));
			}
			
			$store = $this->_getStore();
			$this->addColumn('price',
				array(
					'header'=> Mage::helper('catalog')->__('Price'),
					'type'  => 'price',
					'currency_code' => $store->getBaseCurrency()->getCode(),
					'index' => 'price',
			));

			if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
				$this->addColumn('qty',
					array(
						'header'=> Mage::helper('catalog')->__('Qty'),
						'width' => '100px',
						'type'  => 'number',
						'index' => 'qty',
				));
			}

			$this->addColumn('visibility',
				array(
					'header'=> Mage::helper('catalog')->__('Visibility'),
					'width' => '70px',
					'index' => 'visibility',
					'type'  => 'options',
					'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
			));

			$this->addColumn('status',
				array(
					'header'=> Mage::helper('catalog')->__('Status'),
					'width' => '70px',
					'index' => 'status',
					'type'  => 'options',
					'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
			));

			$websiteIds = Mage::getModel('core/website')->getCollection()->toOptionHash();
			
			if($userType == '2' || $userType == '1'){
				$vendorWebsiteIds 	= Mage::helper('vendor')->getUserWebsiteIds();
				$userWebsiteIds 	= array();
				foreach($websiteIds as $key=>$value){
					if(in_array($key, $vendorWebsiteIds)){
						$userWebsiteIds[$key] = $value;
					}
				}
			} else {
				$userWebsiteIds = $websiteIds;
			}
			
			if (!Mage::app()->isSingleStoreMode()) {
				$this->addColumn('websites',
					array(
						'header'=> Mage::helper('catalog')->__('Websites'),
						'width' => '100px',
						'sortable'  => false,
						'index'     => 'websites',
						'type'      => 'options',
						'options'   => $userWebsiteIds,
				));
			}

		   $this->addColumn('stores',
				array(
					'header'    => Mage::helper('catalog')->__('Action'),
					'width'     => '50px',
					'type'      => 'action',
					'getter'    => 'getId',                
					'filter'    => false,
					'sortable'  => false,
					'index'     => 'stores',
					'renderer'	=> new HTZ_Vendor_Block_Adminhtml_Catalog_Product_Renderer_Action(),
			));
			   
			if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
				$this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
			}

			return parent::_prepareColumns();
		}

		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('entity_id');
			$this->getMassactionBlock()->setFormFieldName('product');

			$this->getMassactionBlock()->addItem('delete', array(
				 'label'=> Mage::helper('catalog')->__('Delete'),
				 'url'  => $this->getUrl('*/*/massDelete'),
				 'confirm' => Mage::helper('catalog')->__('Are you sure?')
			));

			$statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

			array_unshift($statuses, array('label'=>'', 'value'=>''));
			$this->getMassactionBlock()->addItem('status', array(
				 'label'=> Mage::helper('catalog')->__('Change status'),
				 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
				 'additional' => array(
						'visibility' => array(
							 'name' => 'status',
							 'type' => 'select',
							 'class' => 'required-entry',
							 'label' => Mage::helper('catalog')->__('Status'),
							 'values' => $statuses
						 )
				 )
			));

			if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
				$this->getMassactionBlock()->addItem('attributes', array(
					'label' => Mage::helper('catalog')->__('Update Attributes'),
					'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
				));
			}

			Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
			return $this;
		}

		public function getGridUrl()
		{
			return $this->getUrl('*/*/grid', array('_current'=>true));
		}

		public function getRowUrl($row)
		{
			$userType = Mage::helper('vendor')->getUserType();
			if($userType == '2' || $userType == '1'){
				$storeId = ($this->getRequest()->getParam('store')) ? $this->getRequest()->getParam('store') : $this->vendorDefaultStore();
			} else {
				$storeId = $this->getRequest()->getParam('store');
			}
			
			return $this->getUrl('*/*/edit', array(
				'store'=>$storeId,
				'id'=>$row->getId())
			);
		}
		
		protected function vendorDefaultEnable()
		{
			$_storeIds 			= Mage::helper('vendor')->getUserStoreIds();
			$storeCollection 	= Mage::getModel('core/store')->getCollection();
			$sCol				= array();
			foreach($storeCollection as $_storeCollection){ $sCol[] = $_storeCollection->getId(); }
			$adminStore 		= array_diff($sCol, $_storeIds);
			$dFlag 				= true;
			if(count($adminStore)>0){ $dFlag = false; }
			return $dFlag;
		}
		
		protected function vendorDefaultStore()
		{
			$pStoreId 			= $this->getRequest()->getParam('store'); 
			if($pStoreId || $this->vendorDefaultEnable()){
				return $pStoreId;
			} else {
				$_storeIds 			= Mage::helper('vendor')->getUserStoreIds();
				$storeCollection 	= Mage::getModel('core/store')->getCollection();
				$sCol				= array();
				foreach($storeCollection as $_storeCollection){ $sCol[] = $_storeCollection->getId(); }
				
				$storeIds 		= array_intersect($sCol, $_storeIds);
				$vendorWebsite 	= array(); 
				$vendorGroup 	= array(); 
				$vendorStore 	= array(); 
				$vendorStoreId 	= array(); 
				foreach($storeIds as $storeId){
					$store = Mage::getModel('core/store')->load((int)$storeId);
					$vendorWebsite[] 	= Mage::getModel('core/website')->load($store->getWebsiteId())->getName();
					$vendorGroup[] 		= Mage::getModel('core/store_group')->load($store->getGroupId())->getName();
					$vendorStore[] 		= $store->getName();
					$vendorStoreId[] 	= $storeId;
					unset($store);	
				}
				
				array_multisort($vendorWebsite, $vendorGroup, $vendorStore, $vendorStoreId);
				
				if(count($vendorStoreId)>0){
					return $vendorStoreId[0];
				}				
			}
		}
	}	
}