<?php
/*------------------------------------------------------------------------
 # SM Super Categories - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/


class Sm_Supercategories_Block_List extends Mage_Catalog_Block_Product_Abstract
{
	protected $_config = null;
	protected $_storeId = null;

	public function __construct($attr){
		parent::__construct($attr);
		$this->_config = $this->_getCfg($attr);
		if(!$this->_getConfig('active',1)) return;
		$this->_storeId    = Mage::app()->getStore()->getId();
	}

	public function _getCfg($attr = null)
	{
		// get default config.xml
		$defaults = array();
		$def_cfgs = Mage::getConfig()
			->loadModulesConfiguration('config.xml')
			->getNode('default/supercategories_cfg')->asArray();
		if(empty($def_cfgs)) return;
		$groups = array();
		foreach($def_cfgs as  $def_key => $def_cfg){
			$groups[] = $def_key;
			foreach($def_cfg as $_def_key =>  $cfg){
				$defaults[$_def_key] = $cfg;
			}
		}

		// get configs after change
		$_configs = (array)Mage::getStoreConfig("supercategories_cfg");
		if(empty($_configs)) return;
		$cfgs = array();

		foreach($groups as $group){
			$_cfgs = Mage::getStoreConfig('supercategories_cfg/'. $group.'');
			foreach($_cfgs as $_key =>  $_cfg){
				$cfgs[$_key] = $_cfg;
			}
		}

		// get output config
		$configs = array();
		foreach($defaults as $key => $def){
			if(isset($defaults[$key])){
				$configs[$key] = $cfgs[$key];
			}else{
				unset($cfgs[$key]);
			}
		}
		//var_dump($attr);
		$this->_config =  ($attr != null) ? array_merge($configs,$attr) : $configs;
		return $this->_config;
	}

	public function _getConfig($name = null, $value_def =null){
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)){
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}


	public function _setConfig($name, $value = null){
		if ( is_null( $this->_config ) ) $this->_getCfg();
		if (is_array($name)){
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name) && isset( $this->_config[$name] ) )
		{
			$this->_config[$name] = $value;
		}
		return true;
	}

	protected function _toHtml(){
		if(!$this->_getConfig('active',1)) return;
		$isAjax = Mage::app()->getRequest()->isAjax();
		if( $isAjax ){
			$template_file = 'sm/supercategories/default_items.php';
		} //else {
			//$template_file = "sm/supercategories/default.phtml";
		//}
		//$this->setTemplate($template_file);
		//Zend_Debug::dump(get_class($this), 'get_class');die;
		return parent::_toHtml();
	}

	public function _getProductMedia(){
		$items = $this->_getConfig('product_additem');
		$items = unserialize($items);
		if(empty($items)) return;
		return $items;
	}
	public function getListCriterionFilter($fieldproduct)
	{
		$isAjax = Mage::app()->getRequest()->isAjax();
		if( $isAjax ){
			$catids = $this->getRequest()->getPost('category_select');
		}else{
			$catids = $this->_getConfig('product_category');
		}
		if($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		if(empty($_catids))  return;
		$cats = $this->_getCatinfor($_catids, true);
		$list = array();
		$include = (int)$this->_getConfig('child_category_products',1);
		$level = (int)$this->_getConfig('max_depth',1);
		$__catids = ($include && $level > 0) ? $this->_childCategory($_catids, true, 0 , $level) : $_catids;
		$filters = explode(',',$this->_getConfig('filter_order_by'));
		$articles_filter = array();
		$filter_preload = $this->_getConfig('field_preload');
		if($fieldproduct) $filter_preload = $fieldproduct;
		if (empty($filters)) return;
		if (!in_array($filter_preload, $filters)) {
			foreach ($filters as $filter) {
				$filter_preload = $filter;
				break;
			}
		}
		$order_attr  = Mage::getModel('supercategories/system_config_source_orderBy');
		$order_label = $order_attr->toOptionArray(true);
		foreach ($filters as $filter) {
			$aritles = new stdClass();
			$aritles->count = self::_countProducts($__catids, $filter);
			$aritles->id = $filter;
			foreach ($order_label as $title){
				if ( $filter == $title['value'] ){
					$aritles->title = $title['label'];
				}
			}
			array_unshift($articles_filter, $aritles);
		}
		foreach ($articles_filter as $filter) {
			if ($filter->count > 0) {
				if ($filter->id == $filter_preload) {
					$filter->sel = 'sel';
					$filter->child = self::_getProductInfor($__catids, $filter_preload);
				}
				$list[$filter->id] = $filter;
			}
		}
		return $list;
	}
	public function getListCategoriesFilter($categoryId = null,$start=0)
	{
		/*Get Categories infor*/
		$catids = $this->_getConfig('product_category');
		if($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		if(empty($_catids))  return;
		$cats = $this->_getCatinfor($_catids, true);
		$list = array();
		foreach( $cats as $cat){
			$child_list = array();
			$item['id']    = $cat->id;
			$item['name']  = $cat->name;
			$item['link']  = $cat->link;
			$item['image'] = $cat->image;
			$cat_info = Mage::getModel('catalog/category')->load($cat->id);			 
			$cat_child = $cat_info->getChildren();
			if( $cat_child != null ){
				$cat_child = explode(',',$cat_child); 
				foreach( $cat_child as $subCatid){ 
					$_sub_cat = Mage::getModel('catalog/category')->load($subCatid);
					if( $_sub_cat->getIsActive() ) {
						$child['id']	   = $_sub_cat->getId();
						$child['link']     = $_sub_cat->getURL();
						$child['name']     = $_sub_cat->getName();
						$child['image'] = $_sub_cat->getImageUrl();
						$child_list[] = $child;
					}					
					$item['child'] = $child_list;				
				}			
			}						
			$list[] = $item;
		}
		return $list;
	}

	public function getLabel($filter)
	{
		switch ($filter) {
			case 'name' :
				return $this->__('Name');
			case 'entity_id' :
				return $this->__('Id');
			case 'position' :
				return $this->__('Position');
			case 'created_at' :
				return $this->__('Date Created');
			case 'price' :
				return $this->__('Price');
			case 'lastest_product' :
				return $this->__('Lastest Product');
			case 'top_rating' :
				return $this->__('Top Rating');
			case 'most_reviewed' :
				return $this->__('Most Reviews');
			case 'most_viewed' :
				return $this->__('Most Reviews');
			case 'best_sales' :
				return $this->__('Most Selling');
		}
	}

	public function _getProductInfor($_catids){
		!is_array($_catids) && settype($_catids, 'array');
		if(!empty($_catids)) {
			$inlucde = (int)$this->_getConfig('child_category_products',1);
			$level = (int)$this->_getConfig('max_depth',1);
			$_catids = ($inlucde && $level > 0) ? $this->_childCategory($_catids, true, 0 , $level) : $_catids;
			$products = $this->_getProductsBasic($_catids);
			if($products != null ) {
				$_products = $products->getItems();
				if(!empty($_products )) {
					$helper = Mage::helper('supercategories/data');
					foreach($_products as $_product){
						$_product->setStoreId($this->_storeId);
						$_product->id = $_product->getId();
						$_product->title = $_product->getName();
						//$_image = $helper->getProductImage($_product,$this->_getConfig());
						//$_product->_image = $_image;
						$_product->_image = (string)Mage::helper('catalog/image')->init($_product, 'image')->resize($this->_getConfig('product_image_width'), $this->_getConfig('product_image_height'));
						$_product->_description = $helper->_cleanText($_product->getDescription());
						$_product->_description =  $helper->_trimEncode($_product->_description != '') ? $_product->description : $helper->_cleanText($_product->getShortDescription());
						$_product->_description = $helper->_trimEncode($_product->_description != '') ? $helper->truncate($_product->_description,$this->_getConfig('product_description_maxlength')) : '';
						$_product->link = $_product->getProductUrl();
						$_product->review = $this->getReviewsSummaryHtml($_product, true, true);
					}
					return 	$_products;
				}
			}

		}
		return null;
	}

	/*
	 * Check Categories is Active ?
	 */
	private function _getCatActive($catids = null,  $orderby = true)
	{
		if(is_null($catids))
		{
			$catids = $this->_getConfig('product_category');
		}
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY) ;
		if(empty($catids)) return;
		$catidsall = array();
		$categoryIds = array('in'=> $catids);
		$categories = Mage::getModel('catalog/category')
			->getCollection()
			->addAttributeToSelect('*')
			->setStoreId($this->_storeId)
			->addAttributeToFilter('entity_id',  $categoryIds)
			->addIsActiveFilter();

		if ( $orderby )
		{
			$attribute = $this->_getConfig('category_order_by','name');// name | position | entry_id | random
			$dir = $this->_getConfig('category_order_dir','ASC');
			switch($attribute){
				case 'name':
				case 'position':
				case 'entry_id':
					$categories->addAttributeToSort( $attribute , $dir );
					break;
				case 'random':
					$categories->getSelect()->order(new Zend_Db_Expr('RAND()'));
					break;
				default:
			}
		}

		$_catids = array();
		if ( empty($categories) )  return;
		foreach($categories as $category)
		{
			$_catids[] = $category->getId();
		}

		return $_catids;
	}

	/*
	 * array $catids
	 * bool $allcat = true return with parentid else return only childId
	 * int $limitCat = 0 return unlimit else return limit
	 * int $levels =  1 
	 * return $catids
	 */

	private function _childCategory($catids, $allcat = true, $limitCat = 0, $levels = 0){
		!is_array($catids) && settype($catids, 'array');
		$additional_catids = array();
		if(!empty($catids)){

			foreach($catids as $catid)
			{
				$category_model = Mage::getModel('catalog/category');
				$_category = $category_model->load($catid);
				$levelCat = $_category->getLevel();
				$subcategories = $category_model->getCollection()
					->addAttributeToSelect('*')
					->addFieldToFilter('parent_id', $catid)
					->addIsActiveFilter()
					->addAttributeToSort('position', 'ASC')
					->setPageSize($limitCat)->load();
				foreach($subcategories  as  $each_subcat){
					$condition = ($each_subcat->getLevel() -  $levelCat <= $levels)  ;
					if($condition)
					{
						$additional_catids[] = $each_subcat->getId();
					}
				}
			}

			$catids = $allcat ? array_unique( array_merge( $catids, $additional_catids ) ) :  array_unique( $additional_catids )  ;
		}

		return $catids;
	}

	public function _getCatinfor($catids, $orderby = null){
		!is_array($catids) && settype($catids, 'array');
		$list = array();
		if(!empty($catids))
		{
			$helper = Mage::helper('supercategories/data');
			foreach($catids as $catid){
				$category_model = Mage::getModel('catalog/category');
				$category = $category_model->load($catid);
				$category->title = $category->getName();
				$category->id = $category->entity_id;
				$include = (int)$this->_getConfig('child_category_products',1);
				$level = (int)$this->_getConfig('max_depth',1);
				$__catids = ($include && $level > 0) ? $this->_childCategory($category->id, true, 0 , $level) : $category->id;
				$category->count = $this->_countProducts($__catids);
				$category->link = $category->getUrl();
				$category->_description = $helper->_cleanText($category->getDescription());
				$category->_description =  $helper->_trimEncode($category->description != '') ? $helper->truncate($category->_description,$this->_getConfig('category_title_maxlength',5)) : 
				$category->image = $category->getImageUrl(); 
//				$products = $this->_getProductInfor($category->id);
//				if($products != null){
//					$category->child = $products;
				$list[$category->id] = (object)$category->getData();
//				}
			}
		}
		return $list;
	}

	/*
	 * return countProduct;
	 */

	protected function _countProducts($catids) {
		!is_array($catids) && settype($catids, 'array');
		$countProduct = $this->_getProductsBasic($catids, true);
		return $countProduct;
	}

	public function _getList () {
		$catids = $this->_getConfig('product_category');
		if($catids == null) return;
		$_catids = $this->_getCatActive($catids);
		if(empty($_catids))  return;
		return $this->_getCatinfor($_catids, true);
		$list = array();

		return $products;

	}

	public function _getProductsIDs(){
		$catids = $this->_getConfig('product_ids');
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY) ;
		if(empty($catids))  return;
		$products = array();
		$products = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*');
		$products->addIdFilter($catids);
		$products->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $catids).')'));
		return $products;
	}

	public function _getProductsBasic($catids, $countProduct = false){
		$collection = array();
		$productIds = array();
		!is_array($catids) && settype($catids, 'array');
		$isAjax = Mage::app()->getRequest()->isAjax();
		if( $isAjax ){
			$catids = $this->getRequest()->getPost('category_select');
		}else{
			$catids = $this->_getConfig('product_category');
		}
		if(!empty($catids))
		{
		//var_dump($catids);
			$attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
			$categoryIds = array('in'=> $catids);
			$collection = Mage::getModel('catalog/category')->load($catids)->getProductCollection()
			//$collection = Mage::getModel('catalog/product')
				//->getCollection()
				->addAttributeToSelect($attributes)
				->addAttributeToSelect('featured')
				->addAttributeToSelect('*')
				->addAttributeToSelect('category_id')
				->addMinimalPrice()
				->addFinalPrice()
				->addTaxPercents()
				->addTierPriceData()
				->addUrlRewrite()
				->setStoreId($this->_storeId);
				//->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
				//->addAttributeToFilter('category_id', $categoryIds);
			if( $this->_getFeaturedProduct($collection) == false) return null;
			$this->_getFeaturedProduct($collection);
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
			$this->_addViewsCount($collection)	;
			$this->_addOrderedCount($collection);
			$this->_addReviewsCount($collection);
			$collection->getSelect()->group('entity_id')->distinct(true);
			$this->_getOrder($collection);
			$collection->clear();
			if($countProduct) return $collection->count();
			$_limit = (int)$this->_getConfig('product_limitation',5);
			$_limit = $_limit <= 0 ? 0 : $_limit;
			$start = (int)$this->getRequest()->getPost('ajax_reslisting_start');
			if(!$start) $start = 0;
			if($_limit >= 0)  {
				$collection->getSelect()->limit($_limit, $start);
			}
		}
		return $collection;
	}

	/*
	 *	Get Featured Product
	 */
	private function _getFeaturedProduct(& $collection){
		$filter = (int)$this->_getConfig('product_featured',0);
		$attributeModel = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'featured');
		switch($filter){
			// Show All
			case '0':
				break;
			// None Featured	
			case '1':
				if($attributeModel->usesSource()){
					$collection->addAttributeToFilter('featured',array('null'=>1),'left');
				}
				break;
			// Only Featured	
			case '2':
				if($attributeModel->usesSource()){
					$collection->addAttributeToFilter('featured',array('eq' => 1));
				}else{
					return false;
				}
				break;
		}
		return $collection;
	}

	/*
	 *	Get Lastest Product
	 */
	private function _getLastestProduct($collection, $dir){
		$todayStartOfDayDate  = Mage::app()->getLocale()->date()
			->setTime('00:00:00')
			->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

		$todayEndOfDayDate  = Mage::app()->getLocale()->date()
			->setTime('23:59:59')
			->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

		$collection = $this->_addProductAttributesAndPrices($collection)
			->addStoreFilter()
			->addAttributeToFilter('news_from_date',
				array('or'=> array(
					0 => array('date' => true, 'to' => $todayEndOfDayDate),
					1 => array('is' => new Zend_Db_Expr('null'))
				)), 'left')
			->addAttributeToFilter('news_to_date',
				array('or'=> array(
					0 => array('date' => true, 'from' => $todayStartOfDayDate),
					1 => array('is' => new Zend_Db_Expr('null'))
				)), 'left')
			->addAttributeToSort('news_from_date', $dir)
			->addAttributeToSort('entity_id', $dir)
		;
		return $collection;
	}

	/*
	 *	Get Order
	 */
	private function _getOrder($collection){
		$params = Mage::app()->getRequest()->getParams();
		$is_ajax = Mage::app()->getRequest()->getParam('is_ajax_listing_tabs');
		$attribute = (string)$this->_getConfig("field_preload");
		if( $is_ajax ){
			$cat_id = $params['categoryid'];
			$attribute = (string)$cat_id;
		}
		
		$dir 		= (string)$this->_getConfig('product_order_dir','ASC');
		switch ($attribute){
			//case 'position':
			case 'entity_id':
			case 'name':
			case 'created_at':
				$collection->setOrder($attribute, $dir);
				break;
			case 'price':
				$collection->getSelect()->order('final_price '.$dir.'');
				break;
			case 'random':
				$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
				break;
			case 'lastest_product':
				$this->_getLastestProduct($collection, $dir);
				break;
			case 'top_rating':
				$collection->getSelect()->order('num_rating_summary '.$dir.'');
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('num_reviews_count '.$dir.'');
				break;
			case 'most_viewed':
				$collection->getSelect()->order('num_view_counts '.$dir.'');
				break;
			case 'best_sellers':
				$collection->getSelect()->order('ordered_qty '.$dir.'');
				break;
			default:
		}
		return $collection;
	}



	private function _addOrderedCount(& $collection){
		$order_item_table = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
		$select = Mage::getSingleton('core/resource')->getConnection('core_read')
			->select()
			->from($order_item_table, array('product_id', 'ordered_qty' => 'SUM(`qty_ordered`)'))
			->group('product_id');

		$collection->getSelect()
			->joinLeft( array ('bs' => $select),
				'bs.product_id = e.entity_id');
	}

	// add views_count
	private function _addViewsCount(& $collection){
		$reports_event_table		= Mage::getSingleton('core/resource')->getTableName('reports/event');
		$select = Mage::getSingleton('core/resource')->getConnection('core_read')
			->select()
			->from($reports_event_table, array('*', 'num_view_counts' => 'COUNT(`event_id`)'))
			->where('event_type_id = 1')
			->group('object_id');
		$collection->getSelect()
			->joinLeft( array ('mv' => $select),
				'mv.object_id = e.entity_id');

	}

	// add reviews_count and rating_summary
	private function _addReviewsCount(& $collection ){
		$review_summary_table = Mage::getSingleton('core/resource')->getTableName('review/review_aggregate');
		$collection->getSelect()
			->joinLeft(
				array("ra" => $review_summary_table),
				"e.entity_id = ra.entity_pk_value AND ra.store_id=" . $this->_storeId,
				array(
					'num_reviews_count'  => "ra.reviews_count",
					'num_rating_summary' => "ra.rating_summary"
				)
			);
	}
}
