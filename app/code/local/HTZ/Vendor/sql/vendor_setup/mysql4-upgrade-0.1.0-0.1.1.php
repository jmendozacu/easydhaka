<?php
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');

$attrInstaller 		= new Mage_Eav_Model_Entity_Setup;
$attributeLabel 	= "Vendor User";
$attributeCode 		= "vendor_user";

$vPattributeLabel 	= "Vendor Product Active";
$vPattributeCode 	= "htz_vendor_product";
$eVPOptions 		= Mage::getSingleton('eav/config')->getAttribute('catalog_product',$vPattributeCode)->getSource()->getAllOptions(false);
if(count($eVPOptions)>0){
	foreach($eVPOptions as $option){
		$removeVP['delete'][$option['value']]=true;
		$removeVP['value'][$option['value']]=true;
	}
	$aVPSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
	$aVPSetup->addAttributeOption($removeVP);
}

$attrInstaller->startSetup();

$attrInstaller->removeAttribute('catalog_product', $vPattributeCode);
$attrInstaller->addAttribute('catalog_product', $vPattributeCode, array(
		'group'							=> $attributeLabel, 
		'label'             			=> $vPattributeLabel, 		
		'type'              			=> 'int',
        'input'             			=> 'boolean',
        'default'           			=> '0',
        'class'             			=> '',
        'backend'           			=> '',
        'frontend'          			=> '',
        'source'            			=> 'eav/entity_attribute_source_table',
		'global'            			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'              			=> 1,
		'required'             			=> 0,
		'user_defined'         			=> 1,
		'searchable'          			=> 0,
		'filterable'           			=> 0,
		'comparable'           			=> 0,
		'visible_on_front'     			=> 0,
		'visible_in_advanced_search'    => 0,
		'unique'               			=> 0,
		'default'            			=> 0
));

$attrInstaller->endSetup();

