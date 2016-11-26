<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
$helper = Mage::helper('supercategories/data');
$isAjax = Mage::app()->getRequest()->isAjax();
if ($isAjax) {
	$catid = $this->getRequest()->getPost('categoryid');
	$start = (int)$this->getRequest()->getPost('ajax_reslisting_start');
	$list = $this->getListCriterionFilter($catid);
	$child_items = $list[$catid]->child;
}
$post     = Mage::app()->getRequest()->getPost();

if( Mage::getSingleton('cms/page')->getIdentifier() == 'home-left'  && 
Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms' ) {  
	$class_listing = 'col-lg-4 col-md-4';
} else{  
	$class_listing = 'col-lg-3 col-md-3';
} 	
if( $post ){
	$cms_page = $post['cms_page'];
	$is_ajax  = $post['is_ajax_listing_tabs'];
	if( $cms_page == 'home-left' ) {  
		$class_listing = 'col-lg-4 col-md-4';
	}
}else{
	$is_ajax  = "";
	$cat_id   = "";
	$order_id = "";
	$count    = "";	
}

$small_image_config = array(
	'type' => $this->_getConfig('imgcfg_type'),
	'width' => $this->_getConfig('imgcfg_width'),
	'height' => $this->_getConfig('imgcfg_height'),
	'quality' => 90,
	'function' => ($this->_getConfig('imgcfg_function') == 'none') ? null : 'resize',
	'function_mode' => ($this->_getConfig('imgcfg_function') == 'none') ? null : substr($this->_getConfig('imgcfg_function'), 7),
	'transparency' => $this->_getConfig('imgcfg_transparency', 1) ? true : false,
	'background' => $this->_getConfig('imgcfg_background'));
if (!empty($child_items)) {
	$k = $this->getRequest()->getPost('ajax_reslisting_start', 0);
	foreach ($child_items as $item) {
		$_product = Mage::getModel('catalog/product')->load($item['id']); 
					
		$now = date("Y-m-d H:m:s");
		$newsFrom= $_product->getNewsFromDate();
		$newsTo= $_product->getNewsToDate();   
		
		$specialprice = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialPrice();
			$price = Mage::getModel('catalog/product')->load($_product->getId())->getPrice();
			if ($specialprice == '' ) {
				$store_id = Mage::app()->getStore()->getStoreId();
				$discounted_price = Mage::getResourceModel('catalogrule/rule')->getRulePrice( 
								Mage::app()->getLocale()->storeTimeStamp($store_id), 
								Mage::app()->getStore($store_id)->getWebsiteId(), 
								Mage::getSingleton('customer/session')->getCustomerGroupId(), 
								$_product->getId());
				$specialprice = $discounted_price;
				
			}
			
		$k++; ?>
		
		<div class="ltabs-item new-ltabs-item item respl-item <?php echo $class_listing;?> col-sm-6">
			<div class="item-inner">
				<?php
				if ( $this->_getConfig('product_image_display', 1) == 1 ) {
					?>
					<div class="w-image-box">
						<span class="hover-background"></span>
						<div class="item-image">
							<a href="<?php echo $item->link ?>" class="product-image rspl-image">
								<img alt="<?php echo $item->title ?>" title="<?php echo $item->title; ?>"  src="<?php echo $item->_image; ?>"/>
							</a>
						</div>
						<div class="label-wrapper">
						<?php if ($newsFrom !="" && $now>=$newsFrom && ($now<=$newsTo || $newsTo=="")){?>
							<div class="new-product">
								<?php echo $this->__('New'); ?>
							</div>
						<?php }?>
						
						<?php if ( $specialprice ){ ?>
							<div class="sale-product">
								<?php echo $this->__('Sale'); ?>
							</div>
						<?php }?>
						</div>
					</div>
				<?php
				}?>
				
				<div class="item-info">
				<?php if ($this->_getConfig('product_title_display', 1) == 1) {
					?>
					<div class="item-title">
						<a href="<?php echo $item->link ?>" <?php echo $helper::parseTarget($this->_getConfig('product_links_target', '_self')) ?>
						   title="<?php echo $item->title ?>">
							<?php echo $helper->truncate($item->title, $this->_getConfig('product_title_maxlength', 25)); ?>
						</a>
					</div>
				<?php } ?>
				<?php if ((int)$this->_getConfig('product_price_display', 1)) {
					?>
					<div class="item-price">
						<div class="sale-price">
							<?php echo $this->getPriceHtml($item, true); ?>
						</div>
					</div>
				<?php }?>
				<?php if ((int)$this->_getConfig('product_reviews_count', 1)) {
					?>
					<div class="item-review">
						<?php echo $this->getReviewsSummaryHtml($item, true, true);?>
					</div>
				<?php }?>
				
				<div class="actions">
					<?php if ((int)$this->_getConfig('product_addcart_display', 1)) { ?>
					<?php if($item->isSaleable()){ ?>				
							<button class="button btn-cart" title="<?php echo $this->__('Add to Cart') ?>" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')">
								<?php echo $this->__('Add to Cart') ?>
							</button>
					<?php }}?>
					<?php if ((int)$this->_getConfig('product_addwishlist_display', 1) || (int)$this->_getConfig('product_addcompare_display', 1)) :?>	
					<ul class="add-to-links">
						<?php if ($this->helper('wishlist')->isAllow() && (int)$this->_getConfig('product_addwishlist_display', 1)) : ?>
							<li><a title="<?php echo $this->__('Add to Wishlist') ?>" href="<?php echo $this->helper('wishlist')->getAddUrl($item) ?>" class="link-wishlist"><?php echo $this->__('Add to Wishlist') ?></a></li>
						<?php endif; ?>
						<?php 
						if( (int)$this->_getConfig('product_addcompare_display', 1) ):
						if( $_compareUrl = $this->getAddToCompareUrl($item) ): ?>
							<li><a title="<?php echo $this->__('Add to Compare') ?>" href="<?php echo $_compareUrl ?>" class="link-compare"><?php echo $this->__('Add to Compare') ?></a></li>
						<?php endif; 
						endif;
						?>
					</ul>
					<?php endif;?>	
				</div>
				
				<?php if ($this->_getConfig('product_description_display', 1) == 1 && $helper::_trimEncode($item->description) != '') { ?>
					<div class="item-desc">
						<?php echo $helper->truncate($item->description, $this->_getConfig('product_description_maxlength', 200)); ?>
					</div>
				<?php } ?>
				<?php if ((int)$this->_getConfig('product_readmore_display', 1)) { ?>
					<div class="item-readmore">
						<a href="<?php echo $item->link; ?>"
						   title="<?php echo $item->title ?>" <?php echo $helper->parseTarget($this->_getConfig('product_links_target', '_self')); ?> >
							<?php echo $this->_getConfig('product_readmore_text', 'Detail'); ?>
						</a>
					</div>
				<?php } ?>		
				</div>
			</div>
		</div>

		
	<?php
	}
}?>

