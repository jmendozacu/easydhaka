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
 * @copyright  Copyright (c) 2010 AITOC, Inc. 
 */
class Aitoc_Aitloyalty_Model_System_Config_Backend_Stores extends Aitoc_Aitsys_Model_System_Config_Backend_Stores
{

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $collection = Mage::getModel('salesrule/rule')->getCollection();
        if(version_compare(Mage::getVersion(), '1.7.0.0', '>='))
		{
			$select  = $collection
				->getSelect()
				->Where("main_table.conditions_serialized like ? ",'%Aitoc%')
				->Where("main_table.is_active=1"); 
        }
		else
		{
		
			$select  = $collection
				->getSelect()
				->reset(Zend_Db_Select::COLUMNS)           
				->columns(array('website_ids' => 'main_table.website_ids'))
				->Where("main_table.conditions_serialized like ? ",'%Aitoc%')
				->Where("main_table.is_active=1"); 
		}
        $website_ids_array = array();
        foreach($collection->getItems() as $item)
        {
            $website_ids = $item->getData('website_ids');
            $website_ids_array = $website_ids_array + explode(',', $website_ids);
        }
        $websites = Mage::app()->getWebsites($website_ids_array);
        $store_ids_array = array();
        $stores = array();
           
        foreach($websites as $website)
        {
            $stores = $stores + $website->getStores();
            $store_ids_array = $store_ids_array + $website->getStoreIds();
        }
        $licensedStores = explode(',', Mage::getStoreConfig('aitsys/modules/Aitoc_Aitloyalty'));
        $willBeLicensedStores = $this->getValue();
        if(!is_array($willBeLicensedStores))
        {
            $willBeLicensedStores = array();
        }
        $disablingStores = array_diff($licensedStores,$willBeLicensedStores);
        
        $storesRulesCanBeDisabled = array_intersect($disablingStores, $disablingStores);
        $disabledStores = array();
        foreach($stores as $store)
        {
            $store_id = $store->getData('store_id');
            if($store->getData('is_active') == 0)
            {
                $disabledStores[] = $store_id;
            }    
            
        }
        $resultMessageWarningStores = array_diff($storesRulesCanBeDisabled,$disabledStores);
        if(count($resultMessageWarningStores))
        {
            $str_warning_arr = array();
            foreach($resultMessageWarningStores as $store_id)
            {
                $store = Mage::app()->getGroup($store_id)->getName();
                if(!in_array($store, $str_warning_arr))
                {
                    $str_warning_arr[] = $store;
                }    
            }
            $str_warning = implode(', ',$str_warning_arr);
            if(strlen($str_warning)>0)
            {
                $session = Mage::getSingleton('core/session');
                /* @var $session Mage_Core_Model_Session */
                $session->addWarning(
                    Mage::helper('aitloyalty')->__('Aitoc Loyalty Program functionality has been disabled for the following store(s):').' '.$str_warning
                );
            }
        }
    }
}