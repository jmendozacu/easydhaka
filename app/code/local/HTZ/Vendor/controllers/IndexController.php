<?php
class HTZ_Vendor_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction(){
	
		$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if($flag){
			return $this->_redirect('/*/*');
		}
	
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function validateAction(){
		$email 	= $this->getRequest()->getParam('email');
												
		if($email){
			$user 	= Mage::getModel('admin/user')->getCollection()
												->addFieldToFilter('email', array('eq'=>$email))
												->count();
				if($user>0){
					echo 'notavailable';
				} else {
					echo 'available';
				}
			return false;	
		} else {
			return $this->_redirect('/*/*');
		}
	}

	public function saveAction(){ 
		if ($data = $this->getRequest()->getPost()) {
			
			$helper	= Mage::helper('vendor');
			$flag 	= false;
			
			if(!$data['firstname']){ 	$flag = true; $error[] = $helper->__("Firstname field missing."); }
			if(!$data['lastname']){ 	$flag = true; $error[] = $helper->__("Lastname field missing."); }
			if(!$data['email']){		$flag = true; $error[] = $helper->__("Email field missing."); } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){$flag = true; $error[] = $helper->__("Invalid email format.");}
			if(!$data['gender']){ 		$flag = true; $error[] = $helper->__("Gender field missing."); }
			if(!$data['telephone']){ 	$flag = true; $error[] = $helper->__("Telephone field missing."); }
			if(!$data['street']){ 		$flag = true; $error[] = $helper->__("Street field missing."); }
			if(!$data['city']){ 		$flag = true; $error[] = $helper->__("City field missing."); }
			if(!$data['postcode']){ 	$flag = true; $error[] = $helper->__("Postcode field missing."); }
			if(!$data['region']){ 		$flag = true; $error[] = $helper->__("State field missing."); }
			if(!$data['country']){ 		$flag = true; $error[] = $helper->__("Country field missing."); }
			if(!$data['password']){ 	$flag = true; $error[] = $helper->__("Password field missing."); }
			
			if($flag){
				foreach($error as $_error){
					Mage::getSingleton('core/session')->addError($_error);
				}
				return $this->_redirect('*/*/');
			}
			
			$userEmail 	= Mage::getModel('admin/user')->getCollection()
												->addFieldToFilter('email', array('eq'=>$data['email']))
												->count();
												
			if($userEmail>0){
				$_error = $helper->__("Email address already exists.");
				Mage::getSingleton('core/session')->addError($_error);
				return $this->_redirect('*/*/');
			}
			
			$data['username'] 	= $data['email'];
			$data['user_type'] 	= 2;
			$data['store_ids'] 	= Mage::app()->getStore()->getStoreId();
			
			/*
			$rootCategory = Mage::app()->getStore()->getRootCategoryId();
			if($rootCategory){
				$data['category_ids'] 	= Mage::getModel('catalog/category')->load($rootCategory)->getAllChildren();	
			}
			*/
			
			if(Mage::getStoreConfig('vendor/settings/vendor_permission')){
				$data['is_active'] 		= 1;
			} else {
				$data['is_active'] 		= 0;
			}
						
			$model 				= Mage::getModel('admin/user');
           
            $model->setData($data);

            if ($model->hasNewPassword() && $model->getNewPassword() === '') {
                $model->unsNewPassword();
            }
            if ($model->hasPasswordConfirmation() && $model->getPasswordConfirmation() === '') {
                $model->unsPasswordConfirmation();
            }
			
            try {
                $model->setUsername($data['email']);
				$model->setUserType(2);
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
				
				$uRoles		= '';
				$arrRole 	= Mage::getModel('admin/role')->getCollection()->addFieldToFilter('user_type', array('eq'=>'vendor_user'));
				if(count($arrRole)>0){
					foreach($arrRole as $_arrRole){
						$uRoles = array($_arrRole->getRoleId());
					}
				}
				
                if ( $uRoles ) {
                    if ( 1 == sizeof($uRoles) ) {
                        $model->setRoleIds($uRoles)
                            ->setRoleUserId($model->getUserId())
                            ->saveRelations();
                    } else if ( sizeof($uRoles) > 1 ) {
                        $rs = array();
                        $rs[0] = $uRoles[0];
                        $model->setRoleIds( $rs )->setRoleUserId( $model->getUserId() )->saveRelations();
                    }
                }
				
				
				Mage::helper('vendor')->vendorAttributeValue();
				
				$userId 	= $model->getId();
				$password 	= $data['password'];
				$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
				$tableName	= Mage::getConfig()->getTablePrefix().'htz_admin_user';
				$query		= "INSERT INTO $tableName(`user_id`,`user_password`) VALUES($userId,'$password')";
				$connection->query($query);
				
				Mage::helper('vendor')->sendEmail($model->getId(),'registration');
								
				if(Mage::getStoreConfig('vendor/settings/vendor_permission')){
					$this->_adminhtmlSession($data['email'], $data['password']);
					return $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('adminhtml'));
				} else {
					Mage::getSingleton('core/session')->addSuccess($this->__('Your account information has been successfully submitted. You can access your account once admin will approve your account.'));
					return $this->_redirect('*/*/');
				}              
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return $this->_redirect('*/*/');
            }
        }
        return $this->_redirect('*/*/');
    }
	
	protected function _switchSession($namespace, $id = null) {
		session_write_close();
		$GLOBALS['_SESSION'] = null;
		$session = Mage::getSingleton('core/session');
		if ($id) {
			$session->setSessionId($id);
		}
		$session->start($namespace);
	}
	
	protected function _adminhtmlSession($username, $password) {
		$switchSessionName 	= 'adminhtml';
		$currentSessionId 	= Mage::getSingleton('core/session')->getSessionId();
		$currentSessionName = Mage::getSingleton('core/session')->getSessionName();
		if ($currentSessionId && $currentSessionName && isset($_COOKIE[$currentSessionName])) {
			$switchSessionId = $_COOKIE[$switchSessionName];
			Mage::getSingleton('core/session')->setVendorFlag(1);
			$this->_switchSession($switchSessionName, $switchSessionId);
			$session = Mage::getModel('admin/session');
			Mage::getSingleton('adminhtml/session')->addSuccess('Thank you for registering.');
			if($session->isLoggedIn()){
				$session->unsetAll();
				$session->getCookie()->delete($session->getSessionName());
				$session->login($username, $password);
			} else {
				$session->login($username, $password);
			}
			$this->_switchSession($currentSessionName, $currentSessionId);
		}
	}
	
}
