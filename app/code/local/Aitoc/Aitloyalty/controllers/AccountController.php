<?php
/**
 * Loyalty Program
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitloyalty
 * @version      2.3.20
 * @license:     SgbtEEmO4TN5qVIe4RyMXvFvrV2LxMVBSCZx9T3Gjw
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

require_once 'Mage/Customer/controllers/AccountController.php';

class Aitoc_Aitloyalty_AccountController extends Mage_Customer_AccountController
{
    public function promostatsAction()
    {
        $iStoreId = Mage::app()->getStore()->getId();
        $iSiteId  = Mage::app()->getWebsite()->getId();

        /* {#AITOC_COMMENT_END#}
        $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitloyalty')->getLicense()->getPerformer();
        $ruler     = $performer->getRuler();
        if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
        {
            return $this->_redirect('customer/account/');
        }
        {#AITOC_COMMENT_START#} */

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        
        $this->getLayout()->getBlock('head')->setTitle($this->__('Available Specials'));
        
        $this->renderLayout();
    }

}