<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('onestepcheckout/onestepcheckout')}`(
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `sales_order_id` int(11) unsigned NOT NULL,
	`deliverydate_asa` varchar(255) default '',
	`deliverydate_date` varchar(15) default '',
	`deliverydate_time` varchar(55) default '',
	`status` smallint(6) default '0',
	`created_time` datetime NULL,
	`update_time` datetime NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();