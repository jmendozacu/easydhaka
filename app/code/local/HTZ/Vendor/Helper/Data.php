<?php
class HTZ_Vendor_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getUserType()
	{
		return Mage::getSingleton('admin/session')->getUser()->getUserType();
	}
	
	public function getUserId()
	{
		return Mage::getSingleton('admin/session')->getUser()->getUserId();
	}
	
	public function getVendorOrderIds()
	{
		$vendorOrderIds = array();
		if(Mage::helper('vendor')->getUserType()=='2'){
			$userId		= Mage::helper('vendor')->getUserId();
			$tableName 	= Mage::getConfig()->getTablePrefix().'htz_vendor_sales_order_item';
			$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
			$query		= "SELECT `order_id`,`vendor_ids` FROM $tableName";
			$rows		= $connection->query($query);
			$result		= $rows->fetchAll(PDO::FETCH_ASSOC);	
			
			if(count($result)>0){				
				foreach($result as $row){
					if(in_array($userId, explode(',', $row['vendor_ids']))){
						$vendorOrderIds[] = $row['order_id'];
					}
				}
			}
		}
		
		return $vendorOrderIds;
	}
	
	public function checkVendorOrder($orderId,$productId)
	{
		$flag = false;
		if($orderId && $productId){			
			$tableName 	= Mage::getConfig()->getTablePrefix().'htz_vendor_sales_order_item';
			$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
			$query		= "SELECT `vendor_items` FROM $tableName
							WHERE `order_id`=$orderId";
							
			$rows		= $connection->query($query);
			$result		= $rows->fetch(PDO::FETCH_ASSOC);	
					
			if(count($result)>0){
				$userId			= Mage::helper('vendor')->getUserId();	
				$vendorItems	= unserialize($result['vendor_items']);				
				if(array_key_exists($productId,$vendorItems)){
					if(Mage::helper('vendor')->getUserType()=='2'){
						if($vendorItems[$productId]['user_id']==$userId){return true;}
					} else {
						return $vendorItems[$productId];
					}
				}
			}
		}
		
		return $flag;
	}
	
	public function getUserWebsiteIds()
	{
		$storeIds 	= explode(',', Mage::getSingleton('admin/session')->getUser()->getStoreIds());
		$websiteIds	= array();
		if(count($storeIds)>0){
			foreach($storeIds as $storeId){
				$websiteIds[] = Mage::getModel('core/store')->load((int)$storeId)->getWebsiteId();
			}
			return array_unique($websiteIds);
		}
		return $websiteIds;
	}
	
	public function getUserGroupIds()
	{
		$storeIds 	= explode(',', Mage::getSingleton('admin/session')->getUser()->getStoreIds());
		$groupIds	= array();
		if(count($storeIds)>0){
			foreach($storeIds as $storeId){
				$groupIds[] = Mage::getModel('core/store')->load((int)$storeId)->getGroupId();
			}
			return array_unique($groupIds);
		}
		return $groupIds;
	}
	
	public function getUserStoreIds()
	{
		return explode(',', Mage::getSingleton('admin/session')->getUser()->getStoreIds());
	}
	
	public function getUserCategoryIds()
	{
		$rootCategoryIds = array();
		foreach($this->getUserStoreIds() as $store){
			$rootCategoryIds[] = Mage::app()->getStore($store)->getRootCategoryId();
		}
		
		$userRootCategoryIds = array_unique($rootCategoryIds);
		$userCategoryIds = array();
		foreach($userRootCategoryIds as $categoryId){
			$userCategoryIds = array_merge($userCategoryIds, explode(',', Mage::getModel('catalog/category')->load($categoryId)->getAllChildren()));
		}
		
		return array_unique($userCategoryIds);
		
	}
	
	public function getVendorCategoryIds()
	{
		$vendor		= Mage::getSingleton('admin/session')->getUser();
		$vendorId	= $vendor->getUserId();
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tableName	= Mage::getConfig()->getTablePrefix().'htz_vendor_category';
		$query		= "SELECT `category_id` FROM $tableName WHERE `vendor_id`=$vendorId";
		$row		= $connection->query($query);
		$result		= $row->fetchAll(PDO::FETCH_ASSOC);
		$vendorCategoryIds = explode(',', $vendor->getCategoryIds());
		if(is_array($result)){
			foreach($result as $_result){$vendorCategoryIds[] = $_result['category_id'];}
			if(count($vendorCategoryIds)>0){$vendorCategoryIds = array_unique($vendorCategoryIds);}
		}
		
		return array_intersect($vendorCategoryIds, $this->getUserCategoryIds());
		
	}
		
	public function vendorAttributeValue()
    {
		
		$options 			= Mage::getSingleton('eav/config')->getAttribute('catalog_product','vendor_user')->getSource()->getAllOptions(false);
		
		if(count($options)>0){
			$userCollection		= Mage::getModel('admin/user')->getCollection()
											->addFieldToFilter('user_type','2')
											->addFieldToFilter('attribute_value_id',0);
		} else {
			$userCollection		= Mage::getModel('admin/user')->getCollection()
											->addFieldToFilter('user_type','2');
		}
		
		$vendorAttributeId 	= Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', 'vendor_user')->getAttributeId();
			 	
		// Add attribute value [start]
		if(count($userCollection)>0){
			foreach($userCollection as $user){
				$optionValue = $user->getEmail();
				Mage::getModel('eav/entity_attribute')->load($vendorAttributeId)
														->setData('option', array('value' => array('option' => array($optionValue))))
														->save();
				
				$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
																		->setAttributeFilter($vendorAttributeId)
																		->setStoreFilter(0)
																		->load();

				foreach ($collection as $option){
					if($option->getValue()==$optionValue){
						Mage::getModel('admin/user')->load($user->getId())->setAttributeValueId($option->getOptionId())->save();
					}
				}
			}
		}
		// Add attribute value [end]
		
		// Update attribute value [start]
		$rOptions 			= Mage::getResourceModel('eav/entity_attribute_option_collection')
																		->setAttributeFilter($vendorAttributeId)
																		->setStoreFilter(0)
																		->load();
																		
		if(count($rOptions)>0){
			$uOptions = array();
			$iUserCollection	= Mage::getModel('admin/user')->getCollection()
											->addFieldToFilter('user_type','2')
											->addFieldToFilter('attribute_value_id',array('eq'=>0));
			foreach($rOptions as $option){
				// Insert attribute value [start]
				if(count($iUserCollection)>0){
					foreach($iUserCollection as $user){
						if($option->getValue()==$user->getEmail()){
							Mage::getModel('admin/user')->load($user->getId())->setAttributeValueId($option->getOptionId())->save();
						}
					}
				}
				// Insert attribute value [end]
				$uOptions[$option->getOptionId()] = $option->getValue();
			}
		
			$rUserCollection	= Mage::getModel('admin/user')->getCollection()
											->addFieldToFilter('user_type','2')
											->addFieldToFilter('attribute_value_id',array('neq'=>0));
			
			if(count($rUserCollection)>0){
				foreach($rUserCollection as $user){
					foreach($uOptions as $key=>$value){
						// update rename attribute value [start]
						if($user->getAttributeValueId()==$key && $user->getEmail()!=$value){
							$updateModel 	= Mage::getModel('catalog/resource_eav_attribute')->load($vendorAttributeId);
							$values 		= array($key => array("0"=>$user->getEmail()));
							$data['option']['value'] = $values;
							$updateModel->addData($data)->save();
						}
						// update rename attribute value [end]
					}
				}
			}
			
			//Remove attribute value [start]
			$uUserCollection	= Mage::getModel('admin/user')->getCollection()
												->addFieldToFilter('user_type',array('neq'=>'2'))
												->addFieldToFilter('attribute_value_id',array('neq'=>0));
				
			if(count($uUserCollection)>0){
				$deleteOption = array();
				foreach($uUserCollection as $user){
					foreach($uOptions as $key=>$value){
						if($user->getAttributeValueId()==$key){
							$deleteOption[]=$key;
						}
					}
					Mage::getModel('admin/user')->load($user->getId())->setAttributeValueId(0)->save();
				}
				
				if(count($deleteOption)>0){
					$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
																		->setAttributeFilter($vendorAttributeId)
																		->setStoreFilter(0)
																		->load();

					foreach ($collection as $option){
						if(in_array($option->getOptionId(),$deleteOption)){
							$option->delete();
						}
					}
				}
			}
			
			// Remove extra attribute value [start]
			$eOptions 		= Mage::getResourceModel('eav/entity_attribute_option_collection')
																		->setAttributeFilter($vendorAttributeId)
																		->setStoreFilter(0)
																		->load();
			$removeOption 	= array();
			foreach($eOptions as $option){
				$flag			= true;
				$cUserCollection	= Mage::getModel('admin/user')->getCollection()
												->addFieldToFilter('user_type',array('eq'=>'2'));
				
				if($cUserCollection>0){
					foreach($cUserCollection as $user){
						if($user->getEmail()==$option->getValue() || $user->getAttributeValueId()==$option->getOptionId()){
							$flag = false;
						}
					}
				}
				if($flag){
					$removeOption[]=$option->getOptionId();
				}
				
			}
			
			if(count($removeOption)>0){
				$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
																		->setAttributeFilter($vendorAttributeId)
																		->setStoreFilter(0)
																		->load();

				foreach ($collection as $option){
					if(in_array($option->getOptionId(),$removeOption)){
						$option->delete();
					}
				}
			}
			// Remove extra attribute value [end]
			
			// Remove attribute value [end]
			
		}
		// Update attribute value [end]
				
	}
		
	public function getStoreUsersOrderEmail($orderId)
    {
		$email = array();
		if($orderId){
			$storeId 	= Mage::getModel('sales/order')->load($orderId)->getStoreId();
			
			$collection = Mage::getModel('admin/user')->getCollection()
																->addFieldToFilter('user_type','1')
																->addFieldToFilter('is_active','1');
			
			if($collection->count()>0){
				foreach($collection as $user){
					if(in_array($storeId, explode(',', $user->getStoreIds()))){
						$email[] = $user->getEmail();
					}
				}
			}
		}
		return $email;
	}
	
	public function getVendorUsersOrderEmail($orderId)
	{
		$email = array();
		if($orderId){			
			$tableName 	= Mage::getConfig()->getTablePrefix().'htz_vendor_sales_order_item';
			$connection	= Mage::getSingleton('core/resource')->getConnection('core_write');
			$query		= "SELECT `vendor_items` FROM $tableName
							WHERE `order_id`=$orderId";
							
			$rows		= $connection->query($query);
			$result		= $rows->fetch(PDO::FETCH_ASSOC);	
					
			if(count($result)>0){
				$vendorItems	= unserialize($result['vendor_items']);	
				
				foreach($vendorItems as $vendor){
					if(Mage::getModel('admin/user')->load($vendor['user_id'])->getIsActive()=='1'){
						$email[] = $vendor['email'];
					}
				}
				$email = array_unique($email);
			}
		}
		
		return $email;
	}
	
	public function sendEmail($id,$flag)
	{
		if($id){
			
			$user 		= Mage::getModel('admin/user')->load($id);
			$tableName 	= Mage::getConfig()->getTablePrefix().'htz_admin_user'; 
			$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
			$query		= "SELECT `user_password` FROM $tableName WHERE `user_id`=$id";
			$row		= $connection->query($query);
			$result		= $row->fetch(PDO::FETCH_ASSOC);
			$var		= array();
			
			if($sEmail = Mage::getStoreConfig('trans_email/ident_general/email')){
				$senderEmail = $sEmail;
			} elseif($sEmail = Mage::getStoreConfig('vendor/settings/email')){
				$senderEmail = $sEmail;
			} else {
				$senderEmail = 'example@example.com';
			}
			
			if($sName = Mage::getStoreConfig('trans_email/ident_general/name')){
				$senderName = $sName;
			} else {
				$senderName = 'Admin';
			}
			
			if($flag=='active'){
				$emailTemplate		= Mage::getModel('core/email_template')->loadDefault('htz_vendor_active');
				$emailTemplate->setTemplateSubject('Activate account!');
			} elseif($flag=='registration'){
				$emailTemplate		= Mage::getModel('core/email_template')->loadDefault('htz_vendor_registration');
				$emailTemplate->setTemplateSubject('Create account!');
				
				if($aEmail = Mage::getStoreConfig('vendor/settings/email')){
					$adminEmailTemplate		= Mage::getModel('core/email_template')->loadDefault('htz_vendor_registration_admin');
					$adminEmailTemplate->setTemplateSubject('Register vendor user!');
					$adminEmailTemplate->setSenderName($user->getFirstname());
					$adminEmailTemplate->setSenderEmail($user->getEmail());
					$adminEmailTemplate->setType('html');
					$adminVar['fname']		= $user->getFirstname();
					$adminVar['lname']		= $user->getLastname();
					$adminVar['user_email']	= $user->getEmail();
					$adminVar['is_active']	= ($user->getIsActive()) ? 'active' : 'inactive';
					try{
						$adminEmailTemplate->send($aEmail, 'Admin', $adminVar);
					} catch(Exception $e){
			
					}
				}
				
			}
			
			$emailTemplate->setSenderName($senderName);
			$emailTemplate->setSenderEmail($senderEmail);
			$emailTemplate->setType('html');
			
			
			$var['fname']		= $user->getFirstname();
			$var['lname']		= $user->getLastname();
			$var['username']	= $user->getUsername();
			$var['password']	= $result['user_password'];
			$var['login_url']	= Mage::helper('adminhtml')->getUrl('adminhtml');
			try{
				if($flag=='active' || $flag=='registration'){
					$emailTemplate->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), $var);
				}
			} catch(Exception $e){
			
			}
		}
	}
		
}