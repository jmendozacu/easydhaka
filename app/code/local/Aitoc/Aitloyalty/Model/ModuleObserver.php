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

class Aitoc_Aitloyalty_Model_ModuleObserver
{
    public function __construct()
    {
    }
    
    public function onAitocModuleDisableBefore($observer)
    {
    	if ('Aitoc_Aitloyalty' == $observer->getAitocmodulename())
    	{
    		$oInstaller = $observer->getObject();
	        /* @var $oInstaller Aitoc_Aitsys_Model_Aitsys */
	        
    		$oDb     = Mage::getModel('sales_entity/order')->getReadConnection();
	        /* @var $oDb Varien_Db_Adapter_Pdo_Mysql */
	        $oSelect = $oDb->select();
	        /* @var $oSelect Varien_Db_Select */
    		
	        $oSelect->from(array('salesrule' => Mage::getSingleton('core/resource')->getTableName('salesrule')),
                          array(
                              'name'    => 'salesrule.name',
                              'rule_id' => 'salesrule.rule_id',
                          )
                      )
                    ->where('( (salesrule.conditions_serialized LIKE "%Aitoc_Aitloyalty%") OR (salesrule.simple_action LIKE "%surcharge%") )')
                    ->where('salesrule.is_active = "1"')
            ;
    		$aRules = $oDb->fetchAll($oSelect);
    		
    		if (count($aRules))
    		{
    			$oInstaller->addCustomError('Please disable or delete conditions provided with Loyalty Program extension from the following rules:');
    		}
    		foreach ($aRules as $aRule)
    		{
    			$oInstaller->addCustomError($aRule['name'] . ' (ID: ' . $aRule['rule_id'] . ')');
    		}
    	}
    }
    
}