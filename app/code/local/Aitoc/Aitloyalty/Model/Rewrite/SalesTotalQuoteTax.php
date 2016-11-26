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
/* AITOC static rewrite inserts start */
/* $meta=%default,Aitoc_Aitgiftwrap% */
if(Mage::helper('core')->isModuleEnabled('Aitoc_Aitgiftwrap')){
    class Aitoc_Aitloyalty_Model_Rewrite_SalesTotalQuoteTax_Aittmp extends Aitoc_Aitgiftwrap_Model_Rewrite_SalesTotalQuoteTax {} 
 }else{
    /* default extends start */
    class Aitoc_Aitloyalty_Model_Rewrite_SalesTotalQuoteTax_Aittmp extends Mage_Tax_Model_Sales_Total_Quote_Tax {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitloyalty_Model_Rewrite_SalesTotalQuoteTax extends Aitoc_Aitloyalty_Model_Rewrite_SalesTotalQuoteTax_Aittmp
{
    protected function _aggregateTaxPerRate($item, $rate, &$taxGroups, $taxId = null, $recalculateRowTotalInclTax = false)
    {
        if(Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion(">=1.8.1.0"))
        {
            $inclTax = $item->getIsPriceInclTax();
            $rateKey = ($taxId == null) ? (string)$rate : $taxId;
            $taxSubtotal = $subtotal = $item->getTaxableAmount();
            $baseTaxSubtotal = $baseSubtotal = $item->getBaseTaxableAmount();

            $isWeeeEnabled = $this->_weeeHelper->isEnabled();
            $isWeeeTaxable = $this->_weeeHelper->isTaxable();

            if (!isset($taxGroups[$rateKey]['totals'])) {
                $taxGroups[$rateKey]['totals'] = array();
                $taxGroups[$rateKey]['base_totals'] = array();
                $taxGroups[$rateKey]['weee_tax'] = array();
                $taxGroups[$rateKey]['base_weee_tax'] = array();
            }

            $hiddenTax = null;
            $baseHiddenTax = null;
            $weeeTax = null;
            $baseWeeeTax = null;
            $discount = 0;
            $rowTaxBeforeDiscount = 0;
            $baseRowTaxBeforeDiscount = 0;
            $weeeRowTaxBeforeDiscount = 0;
            $baseWeeeRowTaxBeforeDiscount = 0;


            switch ($this->_helper->getCalculationSequence($this->_store)) {
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                    $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);

                    if ($isWeeeEnabled && $isWeeeTaxable) {
                        $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                        $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                        $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                        $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                        $taxGroups[$rateKey]['weee_tax'][] = $this->_deltaRound($weeeRowTaxBeforeDiscount,
                            $rateKey, $inclTax);
                        $taxGroups[$rateKey]['base_weee_tax'][] = $this->_deltaRound($baseWeeeRowTaxBeforeDiscount,
                            $rateKey, $inclTax);
                    }
                    $taxBeforeDiscountRounded = $rowTax = $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax);
                    $baseTaxBeforeDiscountRounded = $baseRowTax = $this->_deltaRound($baseRowTaxBeforeDiscount,
                        $rateKey, $inclTax, 'base');
                    $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                    $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
                    break;
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                    if ($this->_helper->applyTaxOnOriginalPrice($this->_store)) {
                        $discount = $item->getOriginalDiscountAmount();
                        $baseDiscount = $item->getBaseOriginalDiscountAmount();
                    } else {
                        $discount = $item->getDiscountAmount();
                        $baseDiscount = $item->getBaseDiscountAmount();
                    }

                    //AITOC discount FIX
                    if($inclTax)
                    {
                        //use for surcharge only
                        if($discount < 0)
                        {
                            $discountTaxAmount = $discount * $rate / 100;
                            $discount += $discountTaxAmount;
                        }
                        if($baseDiscount < 0)
                        {
                            $baseDiscountTaxAmount = $discount * $rate / 100;
                            $baseDiscount += $baseDiscountTaxAmount;
                        }
                    }
                    //AITOC discount FIX

                    //We remove weee discount from discount if weee is not taxed
                    if ($isWeeeEnabled) {
                        $discount = $discount - $item->getWeeeDiscount();
                        $baseDiscount = $baseDiscount - $item->getBaseWeeeDiscount();
                    }
                    $taxSubtotal = max($subtotal - $discount, 0);
                    $baseTaxSubtotal = max($baseSubtotal - $baseDiscount, 0);

                    $rowTax = $this->_calculator->calcTaxAmount($taxSubtotal, $rate, $inclTax, false);
                    $baseRowTax = $this->_calculator->calcTaxAmount($baseTaxSubtotal, $rate, $inclTax, false);

                    if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                        $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                        $rowTax += $weeeTax;
                        $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                        $baseRowTax += $baseWeeeTax;
                        $taxGroups[$rateKey]['weee_tax'][] = $weeeTax;
                        $taxGroups[$rateKey]['base_weee_tax'][] = $baseWeeeTax;
                    }

                    $rowTax = $this->_deltaRound($rowTax, $rateKey, $inclTax);
                    $baseRowTax = $this->_deltaRound($baseRowTax, $rateKey, $inclTax, 'base');

                    if ($inclTax && !empty($discount)) {
                        //AITOC surcharrge fix
                        if($discount > 0)
                        {
                            $hiddenTax      = $item->getRowTotalInclTax() - $item->getRowTotal() - $rowTax;
                        }
                        if($baseDiscount > 0)
                        {
                            $baseHiddenTax  = $item->getBaseRowTotalInclTax() - $item->getBaseRowTotal() - $baseRowTax;
                        }
                        //AITOC surcharrge fix
                    }

                    $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                    $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));

                    //Calculate the Row taxes before discount
                    $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $subtotal,
                        $rate,
                        $inclTax,
                        false
                    );
                    $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $baseSubtotal,
                        $rate,
                        $inclTax,
                        false
                    );


                    if ($isWeeeTaxable) {
                        $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                        $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                        $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                        $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                    }

                    $taxBeforeDiscountRounded = max(
                        0,
                        $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount')
                    );
                    $baseTaxBeforeDiscountRounded = max(
                        0,
                        $this->_deltaRound($baseRowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount_base')
                    );

                    if (!$item->getNoDiscount()) {
                        if ($item->getWeeeTaxApplied()) {
                            $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                                $taxBeforeDiscountRounded - max(0, $rowTax));
                        }
                    }

                    if ($inclTax && $discount > 0) {
                        $roundedHiddenTax = $taxBeforeDiscountRounded - max(0, $rowTax);
                        $baseRoundedHiddenTax = $baseTaxBeforeDiscountRounded - max(0, $baseRowTax);
                        $this->_hiddenTaxes[] = array(
                            'rate_key' => $rateKey,
                            'qty' => 1,
                            'item' => $item,
                            'value' => $roundedHiddenTax,
                            'base_value' => $baseRoundedHiddenTax,
                            'incl_tax' => $inclTax,
                        );
                    }
                    break;
            }

            $rowTotalInclTax = $item->getRowTotalInclTax();
            if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
                if ($this->_config->priceIncludesTax($this->_store)) {
                    $item->setRowTotalInclTax($subtotal);
                    $item->setBaseRowTotalInclTax($baseSubtotal);
                } else {
                    $item->setRowTotalInclTax(
                        $item->getRowTotalInclTax() + $taxBeforeDiscountRounded - $weeeRowTaxBeforeDiscount);
                    $item->setBaseRowTotalInclTax(
                        $item->getBaseRowTotalInclTax()
                        + $baseTaxBeforeDiscountRounded
                        - $baseWeeeRowTaxBeforeDiscount);
                }
            }

            $taxGroups[$rateKey]['totals'][] = max(0, $taxSubtotal);
            $taxGroups[$rateKey]['base_totals'][] = max(0, $baseTaxSubtotal);
            $taxGroups[$rateKey]['tax'][] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'][] = max(0, $baseRowTax);
        }
        elseif(Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion(">=1.4.1.1") && Aitoc_Aitsys_Abstract_Service::get()->isMagentoVersion("<1.8.1.0"))
        {
            $inclTax        = $item->getIsPriceInclTax();
            $rateKey        = (string) $rate;
            $subtotal       = $item->getTaxableAmount() + $item->getExtraRowTaxableAmount();
            $baseSubtotal   = $item->getBaseTaxableAmount() + $item->getBaseExtraRowTaxableAmount();
            $item->setTaxPercent($rate);

            if (!isset($taxGroups[$rateKey]['totals'])) {
                $taxGroups[$rateKey]['totals'] = array();
                $taxGroups[$rateKey]['base_totals'] = array();
            }

            $hiddenTax     = null;
            $baseHiddenTax = null;
            switch ($this->_helper->getCalculationSequence($this->_store)) {
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    $rowTax             = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                    $baseRowTax         = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);
                    break;
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                    $discount       = $item->getDiscountAmount();
                    $baseDiscount   = $item->getBaseDiscountAmount();
                       //AITOC discount FIX
                    if($inclTax)
                    {
                        //use for surcharge only
                        if($discount < 0)
                        {
                            $discountTaxAmount = $discount * $rate / 100;
                            $discount += $discountTaxAmount;
                        }
                        if($baseDiscount < 0)
                        {
                            $baseDiscountTaxAmount = $discount * $rate / 100;
                            $baseDiscount += $baseDiscountTaxAmount;
                        }
                    }
                    //AITOC discount FIX
                    $subtotal       -= $discount;
                    $baseSubtotal   -= $baseDiscount;
                    $rowTax         = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                    $baseRowTax     = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);
                    break;
            }

            $rowTax     = $this->_deltaRound($rowTax, $rateKey, $inclTax);
            $baseRowTax = $this->_deltaRound($baseRowTax, $rateKey, $inclTax, 'base');
            if ($inclTax && !empty($discount)) {
                //AITOC surcharrge fix
                if($discount > 0)
                {
                    $hiddenTax      = $item->getRowTotalInclTax() - $item->getRowTotal() - $rowTax;
                }
                if($baseDiscount > 0)
                {
                    $baseHiddenTax  = $item->getBaseRowTotalInclTax() - $item->getBaseRowTotal() - $baseRowTax;
                }
                //AITOC surcharrge fix
            }

            $item->setTaxAmount(max(0, $rowTax));
            $item->setBaseTaxAmount(max(0, $baseRowTax));
            $item->setHiddenTaxAmount(max(0, $hiddenTax));
            $item->setBaseHiddenTaxAmount(max(0, $baseHiddenTax));

            $taxGroups[$rateKey]['totals'][]        = max(0, $subtotal);
            $taxGroups[$rateKey]['base_totals'][]   = max(0, $baseSubtotal);
        }
        else
        {
            $store   = $item->getStore();
            $inclTax = $this->_usePriceIncludeTax($store);

            if ($inclTax) {
                $subtotal       = $item->getTaxCalcRowTotal();
                $baseSubtotal   = $item->getBaseTaxCalcRowTotal();
            } else {
                if ($item->hasCustomPrice() && $this->_helper->applyTaxOnCustomPrice($store)) {
                    $subtotal       = $item->getRowTotal();
                    $baseSubtotal   = $item->getBaseRowTotal();
                } else {
                    $subtotal       = $item->getTotalQty()*$item->getOriginalPrice();
                    $baseSubtotal   = $item->getTotalQty()*$item->getBaseOriginalPrice();
                }
            }
            $discountAmount     = $item->getDiscountAmount();
            $baseDiscountAmount = $item->getBaseDiscountAmount();
            $qty                = $item->getTotalQty();
            $rateKey            = (string) $rate;
            /**
             * Add extra amounts which can be taxable too
             */
            $calcTotal          = $subtotal + $item->getExtraRowTaxableAmount();
            $baseCalcTotal      = $baseSubtotal + $item->getBaseExtraRowTaxableAmount();

            $item->setTaxPercent($rate);
            if (!isset($taxGroups[$rateKey]['totals'])) {
                $taxGroups[$rateKey]['totals'] = array();
            }
            if (!isset($taxGroups[$rateKey]['totals'])) {
                $taxGroups[$rateKey]['base_totals'] = array();
            }

            $calculationSequence = $this->_helper->getCalculationSequence($store);
            switch ($calculationSequence) {
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                    $rowTax             = $this->_calculator->calcTaxAmount($calcTotal, $rate, $inclTax, false);
                    $baseRowTax         = $this->_calculator->calcTaxAmount($baseCalcTotal, $rate, $inclTax, false);
                    break;
                case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    $rowTax             = $this->_calculator->calcTaxAmount($calcTotal, $rate, $inclTax, false);
                    $baseRowTax         = $this->_calculator->calcTaxAmount($baseCalcTotal, $rate, $inclTax, false);
                    $discountPrice = $inclTax ? ($subtotal/$qty) : ($subtotal+$rowTax)/$qty;
                    $baseDiscountPrice = $inclTax ? ($baseSubtotal/$qty) : ($baseSubtotal+$baseRowTax)/$qty;
                    $item->setDiscountCalculationPrice($discountPrice);
                    $item->setBaseDiscountCalculationPrice($baseDiscountPrice);
                    break;
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                       //AITOC discount FIX
                    if($inclTax)
                    {
                        //use for surcharge only
                        if($discountAmount < 0)
                        {
                            $discountTaxAmount = $discountAmount * $rate / 100;
                            $discountAmount += $discountTaxAmount;
                        }
                        if($baseDiscountAmount < 0)
                        {
                            $baseDiscountTaxAmount = $baseDiscountAmount * $rate / 100;
                            $baseDiscountAmount += $baseDiscountTaxAmount;
                        }
                    }
                    //AITOC discount FIX
                    $calcTotal          = $calcTotal-$discountAmount;
                    $baseCalcTotal      = $baseCalcTotal-$baseDiscountAmount;
                    $rowTax             = $this->_calculator->calcTaxAmount($calcTotal, $rate, $inclTax, false);
                    $baseRowTax         = $this->_calculator->calcTaxAmount($baseCalcTotal, $rate, $inclTax, false);
                    break;
            }

            /**
             * "Delta" rounding
             */
            $delta      = isset($this->_roundingDeltas[$rateKey]) ? $this->_roundingDeltas[$rateKey] : 0;
            $baseDelta  = isset($this->_baseRoundingDeltas[$rateKey]) ? $this->_baseRoundingDeltas[$rateKey] : 0;

            $rowTax     += $delta;
            $baseRowTax += $baseDelta;

            $this->_roundingDeltas[$rateKey]     = $rowTax - $this->_calculator->round($rowTax);
            $this->_baseRoundingDeltas[$rateKey] = $baseRowTax - $this->_calculator->round($baseRowTax);
            $rowTax     = $this->_calculator->round($rowTax);
            $baseRowTax = $this->_calculator->round($baseRowTax);

            /**
             * Renew item amounts in case if we are working with price include tax
             */
            if ($inclTax) {
                $unitTax = $this->_calculator->round($rowTax/$qty);
                $baseUnitTax = $this->_calculator->round($baseRowTax/$qty);
                if ($item->hasCustomPrice()) {
                    $item->setCustomPrice($item->getPriceInclTax()-$unitTax);
                    $item->setBaseCustomPrice($item->getBasePriceInclTax()-$baseUnitTax);
                } else {
                    $item->setOriginalPrice($item->getPriceInclTax()-$unitTax);
                    $item->setPrice($item->getBasePriceInclTax()-$baseUnitTax);
                    $item->setBasePrice($item->getBasePriceInclTax()-$baseUnitTax);
                }
                $item->setRowTotal($item->getRowTotalInclTax()-$rowTax);
                $item->setBaseRowTotal($item->getBaseRowTotalInclTax()-$baseRowTax);
            }

            $item->setTaxAmount($rowTax);
            $item->setBaseTaxAmount($baseRowTax);

            $taxGroups[$rateKey]['totals'][]        = $calcTotal;
            $taxGroups[$rateKey]['base_totals'][]   = $baseCalcTotal;
        }
        return $this;
    }
}