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
/* $meta=%default,Aitoc_Aitcheckoutfields,Aitoc_Aitconfcheckout% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitconfcheckout')){
    class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesOrderPdfInvoice_Aittmp extends Aitoc_Aitconfcheckout_Model_Rewrite_AdminSalesPdfInvoice {} 
 }elseif(Mage::helper('core')->isModuleEnabled('Aitoc_Aitcheckoutfields')){
    class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesOrderPdfInvoice_Aittmp extends Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesPdfInvoice {} 
 }else{
    /* default extends start */
    class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesOrderPdfInvoice_Aittmp extends Mage_Sales_Model_Order_Pdf_Invoice {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitloyalty_Model_Rewrite_FrontSalesOrderPdfInvoice extends Aitoc_Aitloyalty_Model_Rewrite_FrontSalesOrderPdfInvoice_Aittmp
{
    protected function insertTotals($page, $source){
        $order = $source->getOrder();
//        $font = $this->_setFontBold($page);

        $totals = $this->_getTotalsList($source);

        $lineBlock = array(
            'lines'  => array(),
            'height' => 15
        );
        foreach ($totals as $total) {
            $amount = $source->getDataUsingMethod($total['source_field']);
            $displayZero = (isset($total['display_zero']) ? $total['display_zero'] : 0);

            if ($amount != 0 || $displayZero) {
// AITOC modifications 
                if ('discount_amount' == $total['source_field'])
                {
                    $amount = 0 - $amount;
                    if ($amount > 0)
                    {
                        $total['title'] = $total['title_positive'];
                    }
                }
// end of AITOC modifications 

                $amount = $order->formatPriceTxt($amount);

                if (isset($total['amount_prefix']) && $total['amount_prefix']) {
                    $amount = "{$total['amount_prefix']}{$amount}";
                }
                
                $fontSize = (isset($total['font_size']) ? $total['font_size'] : 7);
                //$page->setFont($font, $fontSize);

                $label = Mage::helper('sales')->__($total['title']) . ':';

                $lineBlock['lines'][] = array(
                    array(
                        'text'      => $label,
                        'feed'      => 475,
                        'align'     => 'right',
                        'font_size' => $fontSize,
                        'font'      => 'bold'
                    ),
                    array(
                        'text'      => $amount,
                        'feed'      => 565,
                        'align'     => 'right',
                        'font_size' => $fontSize,
                        'font'      => 'bold'
                    ),
                );

//                $page->drawText($label, 475-$this->widthForStringUsingFontSize($label, $font, $fontSize), $this->y, 'UTF-8');
//                $page->drawText($amount, 565-$this->widthForStringUsingFontSize($amount, $font, $fontSize), $this->y, 'UTF-8');
//                $this->y -=15;
            }
        }

//        echo '<pre>';
//        var_dump($lineBlock);

        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }
}