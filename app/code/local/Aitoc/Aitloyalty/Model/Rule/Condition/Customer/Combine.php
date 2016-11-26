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

class Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Combine');
    }
    
    public function loadArray($arr, $key='conditions')
    {
        if (!Mage::helper('aitloyalty')->isModuleEnabled('Aitoc_Aitloyalty')) {
            return $this;
        }
        parent::loadArray($arr, $key);
        return $this;
    }
    public function getNewChildSelectOptions()
    {
    	$cAttributes[] = array('value' => 'Aitoc_Aitloyalty_Model_Rule_Condition_Customer|amount_during_period', 'label' => 'Amount spent during period');
    	$cAttributes[] = array('value' => 'Aitoc_Aitloyalty_Model_Rule_Condition_Customer|membership_period',    'label' => 'Period of membership');
    	$cAttributes[] = array('value' => 'Aitoc_Aitloyalty_Model_Rule_Condition_Customer|amount_average',       'label' => 'Average order amount during period');

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Combine', 'label'=>Mage::helper('catalog')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalog')->__('Customer Data'), 'value'=>$cAttributes),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all    = $this->getAggregator() === 'all';
        $true   = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($object);

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }
    
}