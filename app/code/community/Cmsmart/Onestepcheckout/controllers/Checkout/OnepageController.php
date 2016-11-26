<?php
$cmsmart_av_class = false;

if (!Mage::helper('onestepcheckout')->isEnable()) {
    // check if Cmsmart AddressValidation exists
    $path = Mage::getBaseDir('app') . DS . 'code' . DS . 'local' . DS;
    $file = 'Cmsmart/AddressVerification/controllers/OnepageController.php';
    // load Cmsmart Onestepcheckout class
    if (file_exists($path . $file)) {
        // check if Cmsmart AV enabled
        if (Mage::helper('addressverification')->isAddressVerificationEnabled()) {
            $cmsmart_av_class = true;
        }
    }
}

if (!$cmsmart_av_class) {
    require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

    class Cmsmart_Onestepcheckout_Checkout_OnepageController extends Mage_Checkout_OnepageController
    {

        /**
         * Checkout page
         */
        public function indexAction()
        {
            $scheme = Mage::app()->getRequest()->getScheme();
            if ($scheme == 'http') {
                $secure = false;
            } else {
                $secure = true;
            }

            if (Mage::helper('onestepcheckout')->isEnable()) {
                $this->_redirect('onepage', array(
                    '_secure' => $secure
                ));
                return;
            } else {
                parent::indexAction();
            }
        }
    }
} else {
    require_once Mage::getModuleDir('controllers', 'Cmsmart_AddressVerification') . DS . 'OnepageController.php';

    class Cmsmart_Onestepcheckout_Checkout_OnepageController extends Cmsmart_AddressVerification_OnepageController
    {
    }
}
