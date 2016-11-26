<?php
class HTZ_Vendor_Model_Observer
{

	public function addOrderItem($observer){
		$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if(!$flag){
			$order 		= $observer->getEvent()->getOrder();
			$orderId 	= $order->getId();
			$storeId	= Mage::app()->getStore()->getStoreId();
			$arrUserId		= array();
			$vendor			= array();
			
		
			
			
			foreach($order->getAllItems() as $item){
				$productId		= $item->getProductId();			
				$vendorEmail 	= Mage::getModel('catalog/product')->load($productId)->getAttributeText('vendor_user');
				if($vendorEmail){
					$user = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('email',$vendorEmail)->getData();
					if(count($user)>0){
						$vendorStore = explode(',', $user[0]['store_ids']);
						if($storeId==0 || in_array($storeId, $vendorStore)){
							$vendor[$productId]['firstname'] 	= $user[0]['firstname'];
							$vendor[$productId]['lastname'] 	= $user[0]['lastname'];
							$vendor[$productId]['email'] 		= $vendorEmail;
							$vendor[$productId]['user_id'] 		= $user[0]['user_id'];
							$arrUserId[] 						= $user[0]['user_id'];		
						}	
					}
				}
			}
			
			if(count($arrUserId)>0){
				$connection 	= Mage::getSingleton('core/resource')->getConnection('core_write');
				$tableName		= Mage::getConfig()->getTablePrefix().'htz_vendor_sales_order_item';
				$pUserId		= addslashes(implode(',', array_unique($arrUserId)));
				$vendorInfo		= addslashes(serialize($vendor));
				$query			= 'INSERT INTO `'.$tableName.'`(`order_id`,`vendor_ids`,`vendor_items`)
									VALUES(
									'.$orderId.',
									"'.$pUserId.'",
									"'.$vendorInfo.'")';
				
				$connection->query($query);
			}
		}	
	}

	public function login($observer){ 
		$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
		$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
		$live 	= Mage::getStoreConfig('vendor/settings/livekey');
		$flag 	= true;
		if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
			$flag = false;
		}  
		if(!$flag){
			if(Mage::getSingleton('core/session')->getVendorFlag()){
				Mage::getSingleton('core/session')->unsVendorFlag();			
				$url = Mage::helper('adminhtml')->getUrl('adminhtml/system_account/index/');
				header( 'Location:' . $url); exit;
			}
		}	
	}
}