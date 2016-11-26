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

class Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Subselect extends Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Subselect')
            ->setValue(null);
    }

    public function loadArray($arr, $key='conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml .= '<attribute>'.$this->getAttribute().'</attribute>'
            .'<operator>'.$this->getOperator().'</operator>'
            .parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(array(
            'customer'  => $hlp->__('Customer'),
        ));
        return $this;
    }
    
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('suits'),
        ));
        return $this;
    }
    
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
            Mage::helper('salesrule')->__("If a %s %s %s of these conditions:",
              $this->getAttributeElement()->getHtml(),
              $this->getOperatorElement()->getHtml(),
//              $this->getValueElement()->getHtml(),
              $this->getAggregatorElement()->getHtml()
           );
           if ($this->getId()!='1') {
               $html.= $this->getRemoveLinkHtml();
           }
        return $html;
    }
    
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }
        
        if ('any' == $this->getAggregator())
            $bResult   = false;
        else 
            $bResult   = true;
        
        $bMeetCond = false;
            
        foreach ($this->getConditions() as $cond) {
        	if ($cond instanceof Aitoc_Aitloyalty_Model_Rule_Condition_Customer 
        	    or 
        	    $cond instanceof Aitoc_Aitloyalty_Model_Rule_Condition_Customer_Combine)
        	{
        		
        		$bMeetCond = true;
        		
        		if ('any' == $this->getAggregator())
        		{
        			// any aggregator
        		    $bResult = $bResult || ($cond->validate($object) || false);
        		} else 
        		{
        			// all aggregator
        			$bResult = $bResult && ($cond->validate($object) || false);
        		}
        	}
        }
        
        if (!$bMeetCond)
        {
        	$bResult = false;
        }

        return $bResult;
    }

}