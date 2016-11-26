<?php
class Best4Mage_FrontendConfigurableProductMatrix_CartController extends Mage_Core_Controller_Front_Action
{
	protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
	
	protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }
	
	public function addAction()
    {
       
		
		$cart   = $this->_getCart();
		
        $params = $this->getRequest()->getParams();
		unset($params['product_matrix']);
		$product_matrix = $this->getRequest()->getParam('product_matrix');
		
		if (count(array_filter($product_matrix['qty'])) == 0) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml('No product selected for add to cart.'));
			$this->getResponse()->setRedirect($this->_getRefererUrl());
            return;
        }
		
		try {
			
			$filter = new Zend_Filter_LocalizedToNormalized(
				array('locale' => Mage::app()->getLocale()->getLocaleCode())
			);
			
			$related = $this->getRequest()->getParam('related_product');
			$addToCartProductCount = 0;
			
			foreach($product_matrix['qty'] as $key => $singleQty)
			{
				if($singleQty == 0) {
					$addToCartProductCount++;
					continue;
				}
				
				$product = null;
				$product = Mage::getModel ( 'catalog/product' )->setStoreId ( Mage::app()->getStore()->getId() )->load ( $params['product'] );
				if (!$product) {
					$this->_goBack();
					return;
				}
				
				$current_params = array();
				$current_params['product'] = $params['product'];
				$current_params['super_attribute'] = $product_matrix[$key]['super_attribute'];
				$current_params['qty'] = $filter->filter($singleQty);
				if(isset($params['options'])) $current_params['options'] = $params['options'];
				if(isset($params['cptp_qty'])) $current_params['cptp_qty'] = $params['cptp_qty'];
				if(isset($params['customoptionprice'])) $current_params['customoptionprice'] = $params['customoptionprice'];
				if(isset($product_matrix[$key]['customname'])) $current_params['customname'] = $product_matrix[$key]['customname'];
				if(isset($product_matrix[$key]['customthumb'])) $current_params['customthumb'] = $product_matrix[$key]['customthumb'];
				if(isset($product_matrix[$key]['customprice'])) $current_params['customprice'] = $product_matrix[$key]['customprice'];
				
				$cart->addProduct($product, $current_params);
			}
			
			if (!empty($related)) {
				$cart->addProductsByIds(explode(',', $related));
			}
			$cart->save();
			
			$this->_getSession()->setCartWasUpdated(true);
			
			if (!$this->_getSession()->getNoCartRedirect(true)) {
				if (!$cart->getQuote()->getHasError()) {
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
					$this->_getSession()->addSuccess($message);
				}
				$this->_goBack();
			}
			
		} catch (Mage_Core_Exception $e) {
			if ($this->_getSession()->getUseNotice(true)) {
				$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
			} else {
				$messages = array_unique(explode("\n", $e->getMessage()));
				foreach ($messages as $message) {
					$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
				}
			}

			$url = $this->_getSession()->getRedirectUrl(true);
			if ($url) {
				$this->getResponse()->setRedirect($url);
			} else {
				$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
			}
		} catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
			Mage::logException($e);
			$this->_goBack();
		}
		$this->_goBack();
    }
	/*public function  addallconfigAction(){ 
		if($data = $this->getRequest()->getPost()){  
		echo '<pre>';print_r($data);echo '</pre>';
		die;
			$cart   = $this->_getCart();  
			foreach($data['configqty'] as $configId => $simpleProducts){
					$configProduct = Mage::getModel('catalog/product')->load($configId);  
					foreach($simpleProducts as $simpleId => $value){
						if($value && isset($value['qty']) && $value['qty'] != ''){  
							$value['product'] = $configProduct->getId();
							echo '<pre>';print_r($value);echo '</pre>';
						}
					}
			}
		}
		// $this->_goBack();
		return;  
		 
	}*/
	
	public function  addallconfigAction(){ 
		if($data = $this->getRequest()->getPost()){  
		
		$singleProductId = $this->getRequest()->getParam('single', false);
		
		//echo '<pre>';print_r($data);echo '</pre>';
		//die;
		
		$filter = new Zend_Filter_LocalizedToNormalized(
			array('locale' => Mage::app()->getLocale()->getLocaleCode())
		);
			$cart   = $this->_getCart();  
			$sucessMessage = '';
			foreach($data['configqty'] as $configId => $simpleProducts){
				$product = null;
				
				if($singleProductId && $configId != $singleProductId) {
					continue;
				}
				
				foreach($simpleProducts as $key => $_simpleProduct)
				{
					if(!is_array($_simpleProduct) || $_simpleProduct['qty'] == 0 || $_simpleProduct['qty'] == '') {
						continue;
					}
					
					$product = null;
					$product = Mage::getModel ( 'catalog/product' )->setStoreId ( Mage::app()->getStore()->getId() )->load ($configId);
					if (!$product) {
						continue;
					}
					
					$configurable = true;
					if($key == 0){
						$configurable = false;
					}
					
					$current_params = array();
					$current_params['product'] = $configId;
					if($configurable) $current_params['super_attribute'] = $_simpleProduct['super_attribute'];
					$current_params['qty'] = $filter->filter($_simpleProduct['qty']);
					
					if(isset($simpleProducts['options'])) $current_params['options'] = $simpleProducts['options'];
					if($configurable)
					{
						if(isset($simpleProducts['cptp_qty'])) $current_params['cptp_qty'] = $simpleProducts['cptp_qty'];
						if(isset($_simpleProduct['customoptionprice'])) $current_params['customoptionprice'] = $_simpleProduct['customoptionprice'];
						if(isset($_simpleProduct['customname'])) $current_params['customname'] = $_simpleProduct['customname'];
						if(isset($_simpleProduct['customthumb'])) $current_params['customthumb'] = $_simpleProduct['customthumb'];
						if(isset($_simpleProduct['customprice'])) $current_params['customprice'] = $_simpleProduct['customprice'];
					}
					
					try
					{
						$cart->addProduct($product, $current_params);
					} 
					catch (Mage_Core_Exception $e) {
					if ($this->_getSession()->getUseNotice(true)) {
						$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
					} else {
						$messages = array_unique(explode("\n", $e->getMessage()));
						foreach ($messages as $message) {
							$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
						}
					}
					Mage::log($e->getMessage(),null,'malkesh.log');
					} catch (Exception $e) {
						$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
						Mage::logException($e);
						$this->_goBack();
					}
					
				}
				if ($product) {
					$sucessMessage .= $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName())).'<br>';
				}
				
			}
			
			
					
			$cart->save();
			$this->_getSession()->setCartWasUpdated(true);
			
			if (!$this->_getSession()->getNoCartRedirect(true) && $sucessMessage != '') {
				$this->_getSession()->addSuccess($sucessMessage);
				$this->_goBack();
			}
			
		}
		$this->_goBack();
		return;  
		 
	}
	
}
?>