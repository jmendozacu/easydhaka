<?php
class Cmsmart_Onestepcheckout_Model_Mysql4_Onestepcheckout extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("onestepcheckout/onestepcheckout", "id");
    }
}