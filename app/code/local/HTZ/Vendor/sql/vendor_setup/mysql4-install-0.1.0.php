<?php
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$uTableName	= Mage::getConfig()->getTablePrefix().'admin_user'; 
$query 		= "SELECT * FROM $uTableName";
$result		= $connection->query($query);
$row		= $result->fetch(PDO::FETCH_ASSOC);
if(array_key_exists('user_type',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `user_type`";
	$connection->query($query);
} 

if(array_key_exists('gender',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `gender`";
	$connection->query($query);
}

if(array_key_exists('company',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `company`";
	$connection->query($query);
}

if(array_key_exists('telephone',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `telephone`";
	$connection->query($query);
}

if(array_key_exists('url',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `url`";
	$connection->query($query);
}

if(array_key_exists('street',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `street`";
	$connection->query($query);
}

if(array_key_exists('city',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `city`";
	$connection->query($query);
}

if(array_key_exists('postcode',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `postcode`";
	$connection->query($query);
}

if(array_key_exists('region',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `region`";
	$connection->query($query);
}

if(array_key_exists('country',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `country`";
	$connection->query($query);
}

if(array_key_exists('store_ids',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `store_ids`";
	$connection->query($query);
} 

if(array_key_exists('category_ids',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `category_ids`";
	$connection->query($query);
}

if(array_key_exists('attribute_value_id',$row)){
	$query = "ALTER TABLE $uTableName DROP COLUMN `attribute_value_id`";
	$connection->query($query);
} 

$query = "ALTER TABLE $uTableName 
		ADD COLUMN `user_type` INT(2) NOT NULL DEFAULT 0,
		ADD COLUMN `gender` VARCHAR(30) NOT NULL DEFAULT '',
		ADD COLUMN `company` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `telephone` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `url` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `street` TEXT NOT NULL DEFAULT '',
		ADD COLUMN `city` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `postcode` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `region` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `country` VARCHAR(255) NOT NULL DEFAULT '',
		ADD COLUMN `store_ids` TEXT NOT NULL DEFAULT '',
		ADD COLUMN `category_ids` TEXT NOT NULL DEFAULT '',
		ADD COLUMN `attribute_value_id` INT(11) NOT NULL DEFAULT 0
	";
	
$connection->query($query);

$rTableName	= Mage::getConfig()->getTablePrefix().'admin_role'; 
$query 		= "SELECT * FROM $rTableName";
$result		= $connection->query($query);
$row		= $result->fetch(PDO::FETCH_ASSOC);
if(array_key_exists('user_type',$row)){
	$query = "ALTER TABLE $rTableName DROP COLUMN `user_type`";
	$connection->query($query);
}

$query = "ALTER TABLE $rTableName 
		ADD COLUMN `user_type` VARCHAR(100) NOT NULL DEFAULT ''
	";
$connection->query($query);
	
$query 		= "INSERT INTO $rTableName(`parent_id`,`tree_level`,`sort_order`,`role_type`,`user_id`,`role_name`,`user_type`) 
				VALUES(0,1,0,'G',0,'Vendor Role','vendor_user')
				";
$connection->query($query);

$roleId 	= $connection->lastInsertId();

$rlTableName= Mage::getConfig()->getTablePrefix().'admin_rule'; 
$query 		= "INSERT INTO $rlTableName(`role_id`,`resource_id`,`privileges`,`assert_id`,`role_type`,`permission`) 
				VALUES
				($roleId,'admin/sales',NULL,0,'G','allow'),
				($roleId,'admin/sales/order',NULL,0,'G','allow'),
				($roleId,'admin/sales/order/actions',NULL,0,'G','allow'),
				($roleId,'admin/sales/order/actions/view',NULL,0,'G','allow'),
				($roleId,'admin/sales/invoice',NULL,0,'G','allow'),
				($roleId,'admin/sales/shipment',NULL,0,'G','allow'),				
				($roleId,'admin/sales/billing_agreement',NULL,0,'G','deny'),				
				($roleId,'admin/sales/billing_agreement/actions',NULL,0,'G','deny'),				
				($roleId,'admin/sales/billing_agreement/actions/manage',NULL,0,'G','deny'),				
				($roleId,'admin/sales/billing_agreement/actions/use',NULL,0,'G','deny'),				
				($roleId,'admin/sales/billing_agreement/actions/view',NULL,0,'G','deny'),				
				($roleId,'admin/sales/checkoutagreement',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/cancel',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/capture',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/comment',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/create',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/creditmemo',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/edit',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/email',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/emails',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/hold',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/invoice',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/reorder',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/review_payment',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/ship',NULL,0,'G','deny'),				
				($roleId,'admin/sales/order/actions/unhold',NULL,0,'G','deny'),				
				($roleId,'admin/sales/recurring_profile',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax/classes_customer',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax/classes_product',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax/import_export',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax/rates',NULL,0,'G','deny'),				
				($roleId,'admin/sales/tax/rules',NULL,0,'G','deny'),				
				($roleId,'admin/sales/transactions',NULL,0,'G','deny'),				
				($roleId,'admin/sales/transactions/fetch',NULL,0,'G','deny'),				
				($roleId,'admin/system/config/sales',NULL,0,'G','deny'),				
				($roleId,'admin/system/config/sales_email',NULL,0,'G','deny'),				
				($roleId,'admin/system/config/sales_pdf',NULL,0,'G','deny'),				
				($roleId,'admin/catalog',NULL,0,'G','allow'),				
				($roleId,'admin/catalog/products',NULL,0,'G','allow'),				
				($roleId,'admin/catalog/categories',NULL,0,'G','allow'),				
				($roleId,'admin/catalog/reviews_ratings/reviews/all',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/reviews_ratings/reviews/pending',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/reviews_ratings/ratings',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/tag',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/tag/all',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/tag/pending',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/sitemap',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/reviews_ratings/reviews',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/reviews_ratings',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/search',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/urlrewrite',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/update_attributes',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/attributes/sets',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/attributes/attributes',NULL,0,'G','deny'),				
				($roleId,'admin/catalog/attributes',NULL,0,'G','deny'),				
				($roleId,'admin/vendor',NULL,0,'G','allow'),				
				($roleId,'admin/vendor/settings',NULL,0,'G','deny'),				
				($roleId,'admin/vendor/manage',NULL,0,'G','deny'),				
				($roleId,'admin/vendor/info',NULL,0,'G','allow'),
				($roleId,'admin/system/myaccount',NULL,0,'G','allow')				
				";
$connection->query($query);

$attrInstaller 		= new Mage_Eav_Model_Entity_Setup;
$attributeLabel 	= "Vendor User";
$attributeCode 		= "vendor_user";

$eOptions 			= Mage::getSingleton('eav/config')->getAttribute('catalog_product','vendor_user')->getSource()->getAllOptions(false);
if(count($eOptions)>0){
	foreach($eOptions as $option){
		$remove['delete'][$option['value']]=true;
		$remove['value'][$option['value']]=true;
	}
	$aSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
	$aSetup->addAttributeOption($remove);
}


$attrInstaller->startSetup();

$attrInstaller->removeAttribute('catalog_product', $attributeCode);
$attrInstaller->addAttribute('catalog_product', $attributeCode, array(
	'group'							=> $attributeLabel,                                                    
    'type'       					=> 'varchar',
	'input'         				=> 'select',
    'backend'           			=> '',
    'frontend'          			=> '',
    'label'             			=> $attributeLabel,                                                    
    'class'             			=> '',
    'source'            			=> 'eav/entity_attribute_source_table',
    'global'            			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           			=> true,    
    'required'          			=> false,
    'user_defined'      			=> true,
    'default'           			=> '',
    'searchable'       				=> true,
    'filterable'        			=> true,
    'comparable'        			=> true,
    'visible'      					=> true,
    'visible_on_front'  			=> true,
    'visible_in_advanced_search'   	=> true,                                                 
    'unique'            			=> false,
    'apply_to'          			=> '',
    'is_configurable'   			=> false
));

$attrInstaller->endSetup();


$installer = $this;
$installer->startSetup();
$installer->run("

	DROP TABLE IF EXISTS {$this->getTable('htz_vendor_category')};
	CREATE TABLE {$this->getTable('htz_vendor_category')} (
		`id` int(11) unsigned NOT NULL auto_increment,
		`category_id` int(11) NULL,	
		`vendor_id` int(11) NULL,		
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS {$this->getTable('htz_vendor_sales_order_item')};	
	CREATE TABLE {$this->getTable('htz_vendor_sales_order_item')} (
		`id` int(11) unsigned NOT NULL auto_increment,
		`order_id` int(11) NULL,	
		`vendor_ids` text NOT NULL default '',
		`vendor_items` longtext NOT NULL default '',	
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	DROP TABLE IF EXISTS {$this->getTable('htz_admin_user')};
	CREATE TABLE {$this->getTable('htz_admin_user')} (
		`user_id` int(11) NULL,
		`user_password` text NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
");

$installer->endSetup();
