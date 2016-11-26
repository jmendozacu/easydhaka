<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml permissions user grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
$base 	= md5('htz&%a'.Mage::getStoreConfig('web/unsecure/base_url',0).'a%&zth');
$dev 	= Mage::getStoreConfig('vendor/settings/devkey');
$live 	= Mage::getStoreConfig('vendor/settings/livekey');
$flag 	= true;
if(Mage::getStoreConfig('vendor/settings/enabled') && ($base==$dev || $base==$live)){
	$flag = false;
}  
if($flag){
	class HTZ_Vendor_Block_Adminhtml_Permissions_User_Grid
    extends Mage_Adminhtml_Block_Permissions_User_Grid
	{
	}
} else {

	class HTZ_Vendor_Block_Adminhtml_Permissions_User_Grid extends Mage_Adminhtml_Block_Permissions_User_Grid
	{

		protected function _prepareColumns()
		{
			if(!Mage::getStoreConfig('vendor/settings/enabled')){
				return parent::_prepareColumns();
			}
			
			$this->addColumn('user_id', array(
				'header'    => Mage::helper('adminhtml')->__('ID'),
				'width'     => 5,
				'align'     => 'right',
				'sortable'  => true,
				'index'     => 'user_id'
			));

			$this->addColumn('username', array(
				'header'    => Mage::helper('adminhtml')->__('User Name'),
				'index'     => 'username'
			));

			$this->addColumn('firstname', array(
				'header'    => Mage::helper('adminhtml')->__('First Name'),
				'index'     => 'firstname'
			));

			$this->addColumn('lastname', array(
				'header'    => Mage::helper('adminhtml')->__('Last Name'),
				'index'     => 'lastname'
			));
			
			$this->addColumn('user_type', array(
				'header'    => Mage::helper('vendor')->__('User Type'),
				'index'     => 'user_type',
				'type'      => 'options',
				'options'   => array('0' => Mage::helper('vendor')->__('Admin User'), '1' => Mage::helper('vendor')->__('Store User'), '2' => Mage::helper('vendor')->__('Vendor User'))
			));
			
			$this->addColumn('email', array(
				'header'    => Mage::helper('adminhtml')->__('Email'),
				'width'     => 40,
				'align'     => 'left',
				'index'     => 'email'
			));

			$this->addColumn('is_active', array(
				'header'    => Mage::helper('adminhtml')->__('Status'),
				'index'     => 'is_active',
				'type'      => 'options',
				'options'   => array('1' => Mage::helper('adminhtml')->__('Active'), '0' => Mage::helper('adminhtml')->__('Inactive')),
			));

			return parent::_prepareColumns();
		}

	}
}