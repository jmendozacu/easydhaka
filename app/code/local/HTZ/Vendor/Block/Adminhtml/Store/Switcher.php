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
 * Store switcher block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
$live 	= Mage::getStoreConfig('vendor/settings/livekey');
$flag 	= true;
if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
	$flag = false;
}  
if($flag){
	class HTZ_Vendor_Block_Adminhtml_Store_Switcher
    extends Mage_Adminhtml_Block_Store_Switcher
	{
	}
} else {

	class HTZ_Vendor_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Template
	{
		/**
		 * Key in config for store switcher hint
		 */
		const XPATH_HINT_KEY = 'store_switcher';

		/**
		 * @var array
		 */
		protected $_storeIds;

		/**
		 * Name of store variable
		 *
		 * @var string
		 */
		protected $_storeVarName = 'store';

		/**
		 * Url for store switcher hint
		 *
		 * @var string
		 */
		protected $_hintUrl;

		/**
		 * @var bool
		 */
		protected $_hasDefaultOption = true;

		public function __construct()
		{
			parent::__construct();
			$this->setTemplate('store/switcher.phtml');
			$this->setUseConfirm(true);
			$this->setUseAjax(true);
			$this->setDefaultStoreName($this->__('All Store Views'));
		}

		/**
		 * Deprecated
		 */
		public function getWebsiteCollection()
		{
			$collection = Mage::getModel('core/website')->getResourceCollection();

			$websiteIds = $this->getWebsiteIds();
			if (!is_null($websiteIds)) {
				$collection->addIdFilter($this->getWebsiteIds());
			}

			return $collection->load();
		}

		/**
		 * Get websites
		 *
		 * @return array
		 */
		public function getWebsites()
		{
			$websites = Mage::app()->getWebsites();
			if ($websiteIds = $this->getWebsiteIds()) {
				foreach ($websites as $websiteId => $website) {
					if (!in_array($websiteId, $websiteIds)) {
						unset($websites[$websiteId]);
					}
				}
			}
			
			$userType = Mage::helper('vendor')->getUserType();
			if($userType == '2' || $userType == '1'){
				$userWebsiteIds = Mage::helper('vendor')->getUserWebsiteIds();
				$_websites 		= $websites;
				foreach($_websites as $key => $value){
					if(!in_array($key, $userWebsiteIds)){
						unset($websites[$key]);
					}
				}	
			}
			
			return $websites;
		}

		/**
		 * Deprecated
		 */
		public function getGroupCollection($website)
		{
			if (!$website instanceof Mage_Core_Model_Website) {
				$website = Mage::getModel('core/website')->load($website);
			}
			return $website->getGroupCollection();
		}

		/**
		 * Get store groups for specified website
		 *
		 * @param Mage_Core_Model_Website $website
		 * @return array
		 */
		public function getStoreGroups($website)
		{
			if (!$website instanceof Mage_Core_Model_Website) {
				$website = Mage::app()->getWebsite($website);
			}
			return $website->getGroups();
		}

		/**
		 * Deprecated
		 */
		public function getStoreCollection($group)
		{
			if (!$group instanceof Mage_Core_Model_Store_Group) {
				$group = Mage::getModel('core/store_group')->load($group);
			}
			$stores = $group->getStoreCollection();
			$_storeIds = $this->getStoreIds();
			
			$userType = Mage::helper('vendor')->getUserType();
			if($userType == '2' || $userType == '1'){
				$vendorStoreIds = Mage::getSingleton('admin/session')->getUser()->getStoreIds();
				$stores->addIdFilter(explode(',', $vendorStoreIds));
			} else {			
				if (!empty($_storeIds)) {$stores->addIdFilter($_storeIds);}			
			}
			
			return $stores;
		}

		/**
		 * Get store views for specified store group
		 *
		 * @param Mage_Core_Model_Store_Group $group
		 * @return array
		 */
		public function getStores($group)
		{
			if (!$group instanceof Mage_Core_Model_Store_Group) {
				$group = Mage::app()->getGroup($group);
			}
			$stores = $group->getStores();
			if ($storeIds = $this->getStoreIds()) {
				foreach ($stores as $storeId => $store) {
					if (!in_array($storeId, $storeIds)) {
						unset($stores[$storeId]);
					}
				}
			}
			
			$userType = Mage::helper('vendor')->getUserType();
			if($userType == '2' || $userType == '1'){
				$userStoreIds 	= Mage::helper('vendor')->getUserStoreIds();
				$_stores 		= $stores;
				foreach($_stores as $key => $value){
					if(!in_array($key, $userStoreIds)){
						unset($stores[$key]);
					}
				}	
			}
					
			return $stores;
		}

		public function getSwitchUrl()
		{
			if ($url = $this->getData('switch_url')) {
				return $url;
			}
			return $this->getUrl('*/*/*', array('_current' => true, $this->_storeVarName => null));
		}

		public function setStoreVarName($varName)
		{
			$this->_storeVarName = $varName;
			return $this;
		}

		public function getStoreId()
		{
			return $this->getRequest()->getParam($this->_storeVarName);
		}

		public function setStoreIds($storeIds)
		{
			$this->_storeIds = $storeIds;
			return $this;
		}

		public function getStoreIds()
		{
			return $this->_storeIds;
		}

		public function isShow()
		{
			return !Mage::app()->isSingleStoreMode();
		}

		protected function _toHtml()
		{
			if (!Mage::app()->isSingleStoreMode()) {
				return parent::_toHtml();
			}
			return '';
		}

		/**
		 * Set/Get whether the switcher should show default option
		 *
		 * @param bool $hasDefaultOption
		 * @return bool
		 */
		public function hasDefaultOption($hasDefaultOption = null)
		{
			if (null !== $hasDefaultOption) {
				$this->_hasDefaultOption = $hasDefaultOption;
			}
			
			$userType = Mage::helper('vendor')->getUserType();
			if(($userType != '2' && $userType != '1') || ($this->vendorDefaultEnable() && ($userType == '2' || $userType == '1'))){
				return $this->_hasDefaultOption;
			}
		}
		
		private function vendorDefaultEnable()
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
		
		/**
		 * Return url for store switcher hint
		 *
		 * @return string
		 */
		public function getHintUrl()
		{
			if (null === $this->_hintUrl) {
				$this->_hintUrl = Mage::helper('core/hint')->getHintByCode(self::XPATH_HINT_KEY);
			}
			return $this->_hintUrl;
		}

		/**
		 * Return store switcher hint html
		 *
		 * @return string
		 */
		public function getHintHtml()
		{
			$html = '';
			$url = $this->getHintUrl();
			if ($url) {
				$html = '<a'
					. ' href="'. $this->escapeUrl($url) . '"'
					. ' onclick="this.target=\'_blank\'"'
					. ' title="' . $this->__('What is this?') . '"'
					. ' class="link-store-scope">'
					. $this->__('What is this?')
					. '</a>';
			}
			return $html;
		}
	}

} 
	 
	