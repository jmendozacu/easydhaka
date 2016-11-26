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
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Aitoc_Aitloyalty_Model_Rewrite_Currency extends Mage_Directory_Model_Currency
{
    public function formatPrecision($price, $precision, $options=array(), $includeContainer = true, $addBrackets = false)
    { 
        
        if (!isset($options['precision'])) {
            $options['precision'] = $precision;
        }
        if ($includeContainer) {
            return '<span class="price">' . ($addBrackets ? '[' : '') . $this->formatTxt($price, $options) . ($addBrackets ? ']' : '') . '</span>';
        }
        return $this->formatTxt($price, $options);
    }    
}
?>