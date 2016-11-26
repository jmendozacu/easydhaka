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

class Aitoc_Aitloyalty_Block_Rewrite_AdminhtmlPromoQuoteEditTabs extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tabs
{
	
    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('salesrule')->__('Rule Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_quote_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('salesrule')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_quote_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions_section', array(
            'label'     => Mage::helper('salesrule')->__('Actions'),
            'content'   => $this->getLayout()->createBlock('adminhtml/promo_quote_edit_tab_actions')->toHtml(),
        ));
        
        $this->addTab('aitoc_display_section', array(
            'label'     => Mage::helper('salesrule')->__('Display Options'),
            'content'   => $this->getLayout()->createBlock('aitloyalty/quote_options')->toHtml(),
        ));

        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
    }

}