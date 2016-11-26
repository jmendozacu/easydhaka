<?php
require_once '../../app/Mage.php';
Mage::app();
Mage::getSingleton('core/session', array('name'=>'adminhtml'));
$session = Mage::getSingleton('adminhtml/session')->start();
if($data = Mage::getSingleton('core/session')->getVendorAdminLogin()){
	if($session->isLoggedIn()){
		$session->unsetAll();
        $session->getCookie()->delete($adminSession->getSessionName());
		$session->login($data['email'],$data['password']);		
	} else {
		$session->login($data['email'],$data['password']);		
	}
}
header('Location:'.Mage::getBaseUrl());
exit;