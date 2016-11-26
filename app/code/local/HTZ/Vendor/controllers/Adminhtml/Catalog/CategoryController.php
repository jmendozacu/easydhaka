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
 * Catalog category controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once('Mage/Adminhtml/controllers/Catalog/CategoryController.php'); 
class HTZ_Vendor_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    public function saveAction()
    {
		$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if($flag){
			return parent::saveAction();
		}
	
        if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        if ($data = $this->getRequest()->getPost()) {
            $category->addData($data['general']);
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    }
                    else {
                        $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }

            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            if ($useConfig = $this->getRequest()->getPost('use_config')) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            if ($category->getId() && isset($data['general']['url_key_create_redirect']))
            // && $category->getOrigData('url_key') != $category->getData('url_key')
            {
                $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()) {
                $products = array();
                parse_str($data['category_products'], $products);
                $category->setPostedProducts($products);
            }

            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest()
            ));

            /**
             * Proceed with $_POST['use_config']
             * set into category model for proccessing through validation
             */
            $category->setData("use_post_data_config", $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
                        }
                        else {
                            Mage::throwException($error);
                        }
                    }
                }

                /**
                 * Unset $_POST['use_config'] before save
                 */
                $category->unsetData('use_post_data_config');

                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been saved.'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }
		
		$vendorId 	= $this->getRequest()->getParam('vendor_category');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tableName	= Mage::getConfig()->getTablePrefix().'htz_vendor_category';
		$categoryId	= $category->getId();
		$query		= "SELECT `id` FROM $tableName
						WHERE `category_id`=$categoryId";
		$row		= $connection->query($query);
		$result		= $row->fetch(PDO::FETCH_ASSOC);
		if(is_array($result)){
			$query		= "UPDATE $tableName SET `vendor_id`=$vendorId WHERE `category_id`=$categoryId";
			$connection->query($query);
		} else {
			$query		= "INSERT INTO $tableName(`category_id`,`vendor_id`) VALUES($categoryId, $vendorId)";
			$connection->query($query);
		}
		
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }

    
    public function deleteAction()
    {
        $base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if($flag){
			return parent::deleteAction();
		}
		
		if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $category = Mage::getModel('catalog/category')->load($id);
                Mage::dispatchEvent('catalog_controller_category_delete', array('category'=>$category));

                Mage::getSingleton('admin/session')->setDeletedPath($category->getPath());

                $category->delete();
				
				$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
				$tableName	= Mage::getConfig()->getTablePrefix().'htz_vendor_category';
				$query		= "DELETE FROM $tableName WHERE `category_id`=$id";
				$connection->query($query);
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been deleted.'));
            }
            catch (Mage_Core_Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('An error occurred while trying to delete the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }

}