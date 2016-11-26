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

class Aitoc_Aitloyalty_Block_Quote_Options extends Mage_Adminhtml_Block_Widget_Form
{
	
	public function getFormHtml()
	{
		$sHtml = parent::getFormHtml();
		
		$sHtml .= '<script type="text/javascript">aitloyalty_ActionOnRuleDisplayChange();</script>';
		return $sHtml;
	}
	
    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_quote_rule');

        //$form = new Varien_Data_Form(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('display_fieldset', array('legend'=>Mage::helper('salesrule')->__('Using the form below you can enable, disable and configure display of the rule in customers\' account')));

		$oDb     = Mage::getModel('sales_entity/order')->getReadConnection();
        $oSelect = $oDb->select();
		
        $oSelect->from(array('salesrule' => Mage::getSingleton('core/resource')->getTableName('aitoc_salesrule_display')), '*')
                ->where('salesrule.rule_id = "' . $model->getRuleId() . '"')
        ;
		
        $aDisplayInfo = $oDb->fetchRow($oSelect);
		
		if ($aDisplayInfo)
		{
		    $model->setAitloyaltyCustomerDisplayEnable('1');
		    $model->setAitloyaltyCustomerDisplayCoupon($aDisplayInfo['coupone_enable']);

            $oSelect = $oDb->select();
        	
            $oSelect->from(array('salesrule' => Mage::getSingleton('core/resource')->getTableName('aitoc_salesrule_display_title')), array('store_id', 'value'))
                    ->where('salesrule.rule_id = "' . $model->getRuleId() . '"')
            ;
        	
            $aDisplayTitles = $oDb->fetchPairs($oSelect);
            
            $model->setAitloyaltyCustomerDisplayTitles($aDisplayTitles);
            
#            d($aDisplayInfo, 1);
		    
		    
		    
//		    for titles !!!!!!!!!!!!111
//		    $this->htmlEscape(
		    
		}
    		
        $fieldset->addField('aitloyalty_customer_display_enable', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Display the rule in customers\' account'),
            'title'     => Mage::helper('salesrule')->__('Display the rule in customers\' account'),
            'name'      => 'aitloyalty_customer_display_enable',
            'onchange'  => 'aitloyalty_ActionOnRuleDisplayChange()',
            'options'   => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));
        
        $fieldset->addField('aitloyalty_customer_display_coupon', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Display the coupon code in customers\' account (if applicable)'),
            'title'     => Mage::helper('salesrule')->__('Display the coupon code in customers\' account (if applicable)'),
            'name'      => 'aitloyalty_customer_display_coupon',
            'options'   => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));
        
        $fieldset->addField('aitloyalty_customer_display_title', 'text', array(
            'name'  => 'aitloyalty_customer_display_title',
            'label' => Mage::helper('salesrule')->__(''),
            'title' => Mage::helper('salesrule')->__(''),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('aitloyalty/quote_titles'));
        
        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return $this;
    }

}