<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 */

$this->startSetup();

Mage::helper('ampgrid')->addNoticeIndex();

$this->run("
    alter table `{$this->getTable('ampgrid/qty_sold')}` 
    drop foreign key FK_AMPGRID_QTY_SOLD_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID;
  
");

$this->endSetup(); 