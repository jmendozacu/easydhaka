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
require_once('Mage/Adminhtml/controllers/Permissions/UserController.php');
class HTZ_Vendor_Adminhtml_Permissions_UserController extends Mage_Adminhtml_Permissions_UserController
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
		
		if ($data = $this->getRequest()->getPost()) {
		
			if (isset($data['stores'])) {
                $data['store_ids'] = implode(',',$data['stores']);
            } else {
				$data['store_ids'] = '';
			}
			
			if (isset($data['categories'])) {
                $data['category_ids'] = implode(',',$data['categories']);
            } else {
				$data['category_ids'] = '';
			}
            					
            $id = $this->getRequest()->getParam('user_id');
            $model = Mage::getModel('admin/user')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
			
			$activeEmailFlag = false;
			if(!$model->getIsActive() && $id && $data['is_active'] && $data['user_type']==2){
				$activeEmailFlag = true;
			}
			
            $model->setData($data);
			
            /*
             * Unsetting new password and password confirmation if they are blank
             */
            if ($model->hasNewPassword() && $model->getNewPassword() === '') {
                $model->unsNewPassword();
            }
            if ($model->hasPasswordConfirmation() && $model->getPasswordConfirmation() === '') {
                $model->unsPasswordConfirmation();
            }

            $result = $model->validate();
            if (is_array($result)) {
                Mage::getSingleton('adminhtml/session')->setUserData($data);
                foreach ($result as $message) {
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
                $this->_redirect('*/*/edit', array('_current' => true));
                return $this;
            }

            try {
                $model->setUsername($data['username']);
				$model->setUserType($data['user_type']);
				$model->setStoreIds($data['store_ids']);
				$model->setIsActive($data['is_active']);
				$model->setGender($data['gender']);
				$model->setCompany($data['company']);
				$model->setTelephone($data['telephone']);
				$model->setUrl($data['url']);
				$model->setStreet($data['street']);
				$model->setCity($data['city']);
				$model->setPostcode($data['postcode']);
				$model->setRegion($data['region']);
				$model->setCountry($data['country']);
				$model->save();
				
                if ( $uRoles = $this->getRequest()->getParam('roles', false) ) {
                    /*parse_str($uRoles, $uRoles);
                    $uRoles = array_keys($uRoles);*/
                    if ( 1 == sizeof($uRoles) ) {
                        $model->setRoleIds($uRoles)
                            ->setRoleUserId($model->getUserId())
                            ->saveRelations();
                    } else if ( sizeof($uRoles) > 1 ) {
                        //@FIXME: stupid fix of previous multi-roles logic.
                        //@TODO:  make proper DB upgrade in the future revisions.
                        $rs = array();
                        $rs[0] = $uRoles[0];
                        $model->setRoleIds( $rs )->setRoleUserId( $model->getUserId() )->saveRelations();
                    }
                }
				
				Mage::helper('vendor')->vendorAttributeValue();
												
				if($password=$data['new_password']){
					$userId 	= $model->getId();
					$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
					$tableName	= Mage::getConfig()->getTablePrefix().'htz_admin_user';
					$query		= "SELECT `user_id` FROM $tableName WHERE `user_id`=$userId";
					$row		= $connection->query($query);
					$result		= $row->fetch(PDO::FETCH_ASSOC);
					
					if($result){
						$query		= "UPDATE $tableName SET `user_password`='$password' WHERE `user_id`=$userId";
						$connection->query($query);
					} else {
						$query		= "INSERT INTO $tableName(`user_id`,`user_password`) VALUES($userId,'$password')";
						$connection->query($query);
					}
				}
				
				if($activeEmailFlag){
					Mage::helper('vendor')->sendEmail($id,'active');
				}
				
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user has been saved.'));
                Mage::getSingleton('adminhtml/session')->setUserData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setUserData($data);
                $this->_redirect('*/*/edit', array('user_id' => $model->getUserId()));
                return;
            }
        }
        $this->_redirect('*/*/');
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
		
		$currentUser = Mage::getSingleton('admin/session')->getUser();

        if ($id = $this->getRequest()->getParam('user_id')) {
            if ( $currentUser->getId() == $id ) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('You cannot delete your own account.'));
                $this->_redirect('*/*/edit', array('user_id' => $id));
                return;
            }
            try {
                $model = Mage::getModel('admin/user');
                $model->setId($id);
				
				$vendorValue 					= Mage::getModel('admin/user')->load($id)->getEmail();
				$vendorOption['attribute_id'] 	= Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', 'vendor_user')->getAttributeId();
				$options 						= Mage::getResourceModel('eav/entity_attribute_option_collection')
																						->setAttributeFilter($vendorOption['attribute_id'])
																						->setStoreFilter(0)
																						->load();
				
				foreach ($options as $option){
					if($option->getValue()==$vendorValue){
						$option->delete();
					}
				}	
				
                $model->delete();
				Mage::helper('vendor')->vendorAttributeValue();
				
				$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
				$tableName	= Mage::getConfig()->getTablePrefix().'htz_admin_user';
				$query		= "SELECT `user_id` FROM $tableName WHERE `user_id`=$id";
				$row		= $connection->query($query);
				$result		= $row->fetch(PDO::FETCH_ASSOC);
				
				if($result){
					$query		= "DELETE FROM $tableName WHERE `user_id`=$id";
					$connection->query($query);
				}
				
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find a user to delete.'));
        $this->_redirect('*/*/');
    }

}
