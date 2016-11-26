<?php
class Best4Mage_FrontendConfigurableProductMatrix_Block_Product_View_Options_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select
{
    /**
     * Return html for control element
     *
     * @return string
     */
    public function getValuesHtml()
    {
        $_option = $this->getOption();
		$_product = $this->getProduct();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
		$_matrixHelper = Mage::helper('frontendconfigurableproductmatrix');
		$_isAboveSide = ($_matrixHelper->isShowFDD($_product)==0);
		$_isShowRT = $_matrixHelper->isShowRowTotal($_product);
		$_isShowGT = $_matrixHelper->isShowGrandTotal($_product);
		$_enabledDependent = $_isImagesAboveOptions = false;
		$_defQtyLabel = '';
		$optionJs = '';
		$configValue = false;
		$_advancedCustomOption = $_matrixHelper->isAdvancedCustomOptionEnabled();
		if($_advancedCustomOption){
			$coHelper = Mage::helper('customoptions');
			$_enabledDependent = $coHelper->isDependentEnabled();
			$_defQtyLabel = $coHelper->getDefaultOptionQtyLabel();
			$_displayQty = $coHelper->canDisplayQtyForOptions();
			$_outOfStockOptions = $coHelper->getOutOfStockOptions();
			$_enabledInventory = $coHelper->isInventoryEnabled();
			$_enabledSpecialPrice = $coHelper->isSpecialPriceEnabled();
			$_hideDependentOption = $coHelper->hideDependentOption();
			$_isImagesAboveOptions = $coHelper->isImagesAboveOptions();
			
			if ((version_compare(Mage::getVersion(), '1.5.0', '>=') && version_compare(Mage::getVersion(), '1.9.0', '<')) || version_compare(Mage::getVersion(), '1.10.0', '>=')) {
            	$configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());                                                    
        	}
			
			if (!Mage::app()->getStore()->isAdmin()) $optionJs .= 'changeAndUpdateOptionPrice();';
			if ($_option->getIsXQtyEnabled()) $optionJs .= ' optionSetQtyProductMatrix.setQty();';
			if ($_option->getIsDependentLQty()) $optionJs .= ' optionSetQtyProductMatrix.checkLimitQty('. $_option->getId() .', this);';
			if ($_option->getIsParentLQty()) $optionJs .= ' optionSetQtyProductMatrix.setLimitQty(this);';
		}
		
        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setData(array(
                    'id' => 'matrix_select_'.$_option->getId(),
                    'class' => $require.' matrix-custom-option'
                ));
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options['.$_option->getid().']')
                    ->addOption('', $this->__('-- Please Select --'));
            } else {
                $select->setName('options['.$_option->getid().'][]');
                $select->setClass('multiselect'.$require.' matrix-custom-option');
            }
			$imagesHtml = '';
			$showImgFlag = false;
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);
				
				if ($_value->getImages()) {
					$showImgFlag = true;
						if ($_option->getImageMode()==1 || ($_option->getImageMode()>1 && $_option->getExcludeFirstImage())) {
						$imagesHtml = '<div id="matrixcustomoptions_images_'. $_option->getId() .'" class="customoptions-images-div" style="display:none"></div>';
					}
				}
				
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . $priceStr . '',
                    array('price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false))
                );
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
			
			if ($showImgFlag) $showImgFunc = 'optionMatrixImages.showImage(this);'; else $showImgFunc = '';
			
			$extraParams .= ( $_enabledDependent && $_option->getIsDependent() ? ' disabled="disabled"' : '' );
			
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            } else {
				$extraParams .= ' onchange="'.( $_enabledDependent ? 'dependentMatrixOptions.select(this);' : '').$showImgFunc.'"';
			}
			
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }
			
			$selectHtml = $select->getHtml();
			
			if ($imagesHtml) {
				if ($_isImagesAboveOptions) $selectHtml = $imagesHtml.$selectHtml; else $selectHtml .= $imagesHtml;
			}
			
			$selectHtml = '<tr id="options-'.$_option->getId().'-list" class="options-list"><td>'.$selectHtml
				.($_advancedCustomOption ? $this->getSelectAfterQtyHtml($_option,$coHelper) : '')
				.'</td><td width="'.( $_isAboveSide ? 75 : 25 ).'" class="matrix-total-qty">0</td>'
				.($_isShowRT ? '<td width="'.( $_isAboveSide ? 65 : 50 ).'" class="matrix-option-price">'.Mage::helper('core')->currency(0,true).'</td></tr>':($_isShowGT ? ' <td width="'.( $_isAboveSide ? 65 : 50 ).'"></td>' : ''))
				.'</tr>';
			
            return $selectHtml;
        }

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
            ) {
            $require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
			$selectHtml = '';
			
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml = '<tr id="options-'.$_option->getId().'-0-list" class="options-list">'
							. '<td colspan="3"><input type="radio" id="matrix_options_' . $_option->getId() . '" class="'
                            . $class . ' matrix-custom-option" name="options[' . $_option->getId() . ']"'
                            . ( $_enabledDependent && $_option->getIsDependent() ? ' disabled="disabled"' : '' )
							. ($this->getSkipJsReloadPrice() ? ( $_enabledDependent ? ' onclick="dependentMatrixOptions.select(this)"' : '' ) : ' onclick="opConfig.reloadPrice()"')
                            . ' value="" checked="checked" price="0" /><span class="label"><label for="matrix_options_'
                            . $_option->getId() . '">' . $this->__('None') . '</label>'
							. '</span></td>'
							. '<td class="matrix-total-qty no-display">0</td>'
							. '<td class="matrix-option-price no-display">'
							. Mage::helper('core')->currency(0,true).'</td><tr>';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 0;
            foreach ($_option->getValues() as $_value) {
                $count++;
				
				$selectHtml .= '<tr id="options-'.$_option->getId().'-'.$_value->getId().'-list" class="options-list">';

                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent')
                ));

                $htmlValue = $_value->getOptionTypeId();
                if ($configValue) {                    
                    if ($arraySign) {
                        $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                    } else {
                        $checked = ($configValue == $htmlValue ? 'checked' : '');
                    }
                } else {
                    $checked = ($_value->getDefault()==1 && !$disabled) ? 'checked' : '';
                }

                $selectHtml .= '<td>' . '<input type="' . $type . '" class="' . $class . ' ' . $require
                    . ' matrix-custom-option"'
                    . ( $_enabledDependent && $_option->getIsDependent() ? ' disabled="disabled"' : '' )
					. ($this->getSkipJsReloadPrice() ? ( $_enabledDependent ? ' onclick="dependentMatrixOptions.select(this)"' : '' ) : ' onclick="opConfig.reloadPrice()"')
                    . ' name="options[' . $_option->getId() . ']' . $arraySign . '" id="matrix_options_' . $_option->getId()
                    . '_' . $count . '" value="' . $htmlValue . '" ' . $checked . ' price="'
                    . $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false) . '" />'
                    . '<span class="label"><label for="matrix_options_' . $_option->getId() . '_' . $count . '">'
                    . $_value->getTitle() . ' ' . $priceStr . '</label>';
					
				if($_advancedCustomOption) {
					$selectHtml .= ( $coHelper->isQntyInputEnabled() && $_option->getQntyInput() ? '&nbsp;&nbsp;&nbsp;<label class="label-qty"><b>'
					. $_defQtyLabel.'</b> <input type="text" class="input-text qty'. ($checked?' validate-greater-than-zero':'')
					. '" value="'.$optionValueQty.'" maxlength="12" id="options_'.$_option->getId().'_'.$_value->getOptionTypeId()
					. '_qty" name="options_'.$_option->getId().'_'.$_value->getOptionTypeId().'_qty" onchange="'. $optionJs .'"'
					. ' onKeyPress="if(event.keyCode==13){'. $optionJs .'}" '.($checked?$disabled:'disabled').'></label>'
					. '</span>' : '' );
				}
                
				if ($_option->getIsRequire()) {
                    $selectHtml .= '<script type="text/javascript">' . '$(\'options_' . $_option->getId() . '_'
                    . $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';'
                    . '$(\'options_' . $_option->getId() . '_' . $count
                    . '\').callbackFunction = \'validateOptionsCallback\';' . '</script>';
                }
                $selectHtml .= '</td><td width="'.( $_isAboveSide ? 75 : 25 ).'" class="matrix-total-qty">0</td>'
					.($_isShowRT ? '<td width="'.( $_isAboveSide ? 65 : 50 ).'" class="matrix-option-price'.($_value->getPrice(true)==0?' dont':'').'">'
					.($_value->getPrice(true)==0?'-':Mage::helper('core')->currency(0,true))
					.'</td>':($_isShowGT ? ' <td width="'.( $_isAboveSide ? 65 : 50 ).'"></td>' : ''))
					.'</tr>';
            }
            return $selectHtml;
        }
    }
	
	private function getSelectAfterQtyHtml($_option, $helper)
	{
		$html = '';
		if ($helper->isQntyInputEnabled() && $_option->getQntyInput() && $_option->getType()!=Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX && $_option->getType()!=MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH)
		$html = '<span class="qty-holder"><label class="label-qty">'
			. $helper->getDefaultOptionQtyLabel().'<input type="text" class="input-text qty '
			. (($_option->getIsRequire(true))?'validate-greater-than-zero':'validate-zero-or-greater').'" value="'
			. ($_option->getOptionQty()?$_option->getOptionQty():1).'" maxlength="12" id="options_'.$_option->getId().'_qty" name="options_'
			. $_option->getId().'_qty" onchange="changeAndUpdateOptionPrice();'.(($_option->getIsXQtyEnabled()) ? ' optionSetQtyProductMatrix.setQty();' : '')
			. (($_option->getIsDependentLQty()) ? ' optionSetQtyProductMatrix.checkLimitQty('.$_option->getId().', this);' : '').'"' 
			. ' onKeyPress="if(event.keyCode==13){changeAndUpdateOptionPrice();'.(($_option->getIsXQtyEnabled()) ? ' optionSetQtyProductMatrix.setQty();' : '')
			. (($_option->getIsDependentLQty()) ? ' optionSetQtyProductMatrix.checkLimitQty('.$_option->getId().', this);' : '').'}"></label></span>'
			. (($_option->getIsDependentLQty()) ? '<span class="limit-holder">'.$this->__('Items left:')
			. '<strong id="total_limit_'.$_option->getId().'">0</strong></span>' : '');
		return $html;
	}

}
