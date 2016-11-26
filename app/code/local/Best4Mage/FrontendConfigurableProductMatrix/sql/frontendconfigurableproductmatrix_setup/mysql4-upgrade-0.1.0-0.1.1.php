<?php

$installer = $this;

$installer->startSetup();

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Show Row Total',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'boolean',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_show_rowtotal',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_show_rowtotal'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Show Grand Total',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'boolean',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_show_grandtotal',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_show_grandtotal'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}


$installer->endSetup();