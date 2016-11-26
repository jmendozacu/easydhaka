<?php

$installer = $this;

$installer->startSetup();

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Enable Configurable Products Matrix',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'boolean',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_enable',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_enable'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Select Matrix Template',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'select',
	'source'		=> 'frontendconfigurableproductmatrix/system_config_source_matrixtemplate',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_template',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_template'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Set Template Position',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'select',
	'source'		=> 'frontendconfigurableproductmatrix/system_config_source_matrixposition',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_template_position',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_template_position'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Show Matrix Link',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'boolean',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_show_link',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_show_link'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}

$data= array (
	'attribute_set'	=> 'Default',
	'group' 		=> 'Product Matrix',
	'label'    		=> 'Show Only Checkbox',
	'visible'     	=> true,
	'global' 		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'type'     		=> 'int',
	'input'    		=> 'boolean',
	'system'   		=> true,
	'required' 		=> false,
	'user_defined' 	=> 1,
	'apply_to' 		=> 'configurable',
);

$installer->addAttribute('catalog_product','fcpm_checkbox',$data);

if($attributeId = $installer->getAttributeId('catalog_product', 'fcpm_checkbox'))
{
	$installer->updateTableRow('catalog/eav_attribute',
		'attribute_id', $attributeId,
		'apply_to', 'configurable'
	);
}


$installer->endSetup();