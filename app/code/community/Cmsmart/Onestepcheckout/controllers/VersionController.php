<?php

class Cmsmart_Onestepcheckout_VersionController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $version = Mage::getConfig()->getModuleConfig("Cmsmart_Onestepcheckout")->version;
        echo 'Cmsmart Onestepcheckout Version: ' . $version;
        return;
    }
}