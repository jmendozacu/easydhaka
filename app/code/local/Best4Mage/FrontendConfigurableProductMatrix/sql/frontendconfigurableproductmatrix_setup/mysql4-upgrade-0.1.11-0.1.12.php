<?php

$installer = $this;

$installer->startSetup();

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Show Layout',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'select',
	'source'		=> 'frontendconfigurableproductmatrix/system_config_source_secondposition',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_second_layout',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_second_layout'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$installer->endSetup();