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
 
class Aitoc_Aitloyalty_Helper_Legal extends Mage_Core_Helper_Abstract
{
    protected $_hasLoyaltyFeatures = false;
    protected $_isNotifyRestrictedFeatures = false;

    public function setHasLoyaltyFeatures()
    {
        $this->_hasLoyaltyFeatures = true;

        return $this;
    }

    public function getHasLoyaltyFeatures()
    {
        return $this->_hasLoyaltyFeatures;
    }

    public function setIsNotifyRestrictedFeatures()
    {
        $this->_isNotifyRestrictedFeatures = true;

        return $this;
    }

    public function getIsNotifyRestrictedFeatures()
    {
        return $this->_isNotifyRestrictedFeatures;
    }

    public function notifyRestrictedFeatures()
    {
        if ($this->getIsNotifyRestrictedFeatures())
        {
            $session = Mage::getSingleton('core/session');

            /* @var $session Mage_Core_Model_Session */

            $session->addWarning($this->__('Aitoc Loyalty Program functionality is disabled for some websites you specified'));
        }
    }
}