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

/* AITOC static rewrite inserts start */
/* $meta=%default,Aitoc_Aitindividpromo% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitindividpromo')){
    class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesRuleValidator_Aittmp extends Aitoc_Aitindividpromo_Model_Rewrite_FrontSalesRuleValidator {} 
 }else{
    /* default extends start */
    class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesRuleValidator_Aittmp extends Mage_SalesRule_Model_Validator {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesRuleValidator extends Aitoc_Aitloyalty_Model_Rewrite_FrontSalesRuleValidator_Aittmp
{
    private static $_isUseCustomActions;

    protected function _canProcessRule($rule, $address) {
        if(!Mage::registry('aitFrontSalesRuleValidator'))
        {
            Mage::register('aitFrontSalesRuleValidator', $this);
        }
        
        return parent::_canProcessRule($rule, $address) && 
                !(!self::_isUseCustomActions() && in_array($rule->getSimpleAction(), array('by_percent_surcharge', 'by_fixed_surcharge', 'cart_fixed_surcharge')));
    }

    /**
     * Check if rule can be applied for custom actions
     *
     * @return  bool
     */
    protected static function _isUseCustomActions()
    {
        if (null === self::$_isUseCustomActions)
        {
            self::$_isUseCustomActions = true;
            $iStoreId = Mage::app()->getStore()->getId();
            $iSiteId  = Mage::app()->getWebsite()->getId();
            /* {#AITOC_COMMENT_END#}
            $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitloyalty')->getLicense()->getPerformer();
            $ruler     = $performer->getRuler();
            if (!($ruler->checkRule('store', $iStoreId, 'store') || $ruler->checkRule('store', $iSiteId, 'website')))
            {
                self::$_isUseCustomActions = false;
            }
            {#AITOC_COMMENT_START#} */
        }
        return self::$_isUseCustomActions;
    }
    
    // create publiñ
    public function ait_addDiscountDescription($address, $rule)
    {
       return $this->_addDiscountDescription($address, $rule);
    }
    
    // create publiñ
    public function ait_getItemPrice($item)
    {
       return $this->_getItemPrice($item);
    }
    
    // create publiñ
    public function ait_getItemBasePrice($item)
    {
       return $this->_getItemBasePrice($item);
    }
    
}