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
class Aitoc_Aitloyalty_Block_Rewrite_FrontAccountNavigation extends Mage_Customer_Block_Account_Navigation
{
    public function addLink($name, $path, $label, $urlParams = array())
    {
        $isAddLink = true;

        if ('aitloyalty' == $name)
        {
            $iStoreId = Mage::app()->getStore()->getId();
            $iSiteId  = Mage::app()->getWebsite()->getId();

            /* {#AITOC_COMMENT_END#}
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitloyalty')->getLicense()->getPerformer();
            $ruler     = $performer->getRuler();
            if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
            {
                $isAddLink = false;
            }
            {#AITOC_COMMENT_START#} */
        }

        if ($isAddLink)
        {
            parent::addLink($name, $path, $label, $urlParams);
        }

        return $this;
    }
}