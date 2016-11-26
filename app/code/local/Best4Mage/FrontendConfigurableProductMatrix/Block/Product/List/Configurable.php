<?php 
class Best4Mage_FrontendConfigurableProductMatrix_Block_Product_List_Configurable extends Mage_Core_Block_Template{
	
	protected $_product = null;
	
	protected $_isSimple = false;
	protected $_isShowStock = false;
	
	public function __construct(){
		 
	}	
	public function setProduct($product){
		$this->_product = Mage::getModel('catalog/product')->load($product->getId());
		$this->_isSimple = Mage::helper('frontendconfigurableproductmatrix')->isSimplePriceEnabled($this->_product);
		$this->_isShowStock = Mage::helper('frontendconfigurableproductmatrix')->isShowStock($this->_product);
		return $this;
	} 

	public function getProduct(){
		return $this->_product;
	}
	
	public function isSimple()
	{
		return $this->_isSimple;	
	}
	
	public function isShowStock()
	{
		return $this->_isShowStock;	
	}
	
	public function generateSwatches($atid, $atvId, $altText, $attributeCode, $imag = '')
	{
		$_option_vals = array();
		$adminLbl = $html = $imagHtml = '';
		if($imag != '')
		{
			$imagHtml = ' onClick="replaceSimpleMediaImages(this,\''.$imag.'\');"';
		}
		if(!$this->hasData('options_'.$atid))
		{
			$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')->setPositionOrder('asc')->setAttributeFilter($atid)->setStoreFilter(0)->load();
			
			foreach( $_collection->toOptionArray() as $_cur_option ) {
				$_option_vals[$_cur_option['value']] = $_cur_option['label'];
			}
			$this->setData('options_'.$atid, $_option_vals);
		} else {
			$_option_vals = $this->getData('options_'.$atid);
		}
		
		if(array_key_exists($atvId, $_option_vals)){
			$adminLbl = Mage::helper('acsp')->getSwatchColorByOpId($atvId);
			$altText = $_option_vals[$atvId];
			$swatchsize = Mage::helper('acsp')->getSwatchSize($attributeCode);
			$sizes = explode("x", $swatchsize);
			$width = $sizes[0];
			$height = $sizes[1];
			$swatchimage = Mage::helper('acsp')->getSwatchUrl($atvId);
			if($swatchimage != '')
			{
				$html = '<img src="'.$swatchimage.'" id="swatch'.$atvId.'" class="swatch" alt="'.$altText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'"'.$imagHtml.'/>';
			}
			elseif($adminLbl)
			{
				$multuColor = explode('::',$adminLbl);
				if(count($multuColor)==1){
					$html = '<div id="swatch'.$atvId.'" title="'.$altText.'" class="swatch" style="background-color:'.$adminLbl.'; width:'.$width.'px; height:'.$height.'px;"'.$imagHtml.'></div>';
				} else {
					$html = '<div id="swatch'.$atvId.'" title="'.$altText.'" class="swatch" style="width:'.(count($multuColor)*($width+2)).'px;box-sizing: border-box;"'.$imagHtml.'>';
					foreach($multuColor as $mColor){
						$html .= '<div style="background-color:'.$mColor.'; width:'.$width.'px; height:'.$height.'px;float:left;"></div>';
					}
					$html .= '</div>';
				}
			}
			else
			{
				$html = '<img src="'.Mage::helper('acsp')->getSwatchUrl('empty').'" id="swatch'.$atvId.'" class="swatch" alt="'.$altText.'" width="'.$width.'px" height="'.$height.'px" title="'.$altText.'"'.$imagHtml.'/>';
			}
		}
		return $html;
	}
	 
}