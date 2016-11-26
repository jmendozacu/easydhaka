<?php

class Wyomind_Estimateddeliverydate_Model_Observer {

    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

    public function saveConfig($observer) {
        $attributes = explode(",", Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings/base_attribute"));
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $resource->getConnection('core_write');
        $tableEal = Mage::getSingleton("core/resource")->getTableName("eav_attribute_leadtime");
        $sql = array();
        foreach ($attributes as $attribute) {


            $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attribute);
            $values = $attribute->getSource()->getAllOptions();
            foreach ($values as $value) {

                if ((string) $value["value"] != '') {
                    $value_ids[] = $value['value'];
                    $sql[] = "SELECT @value:=IF(COUNT(value)<1,0,value) FROM $tableEal WHERE attribute_id=" . $attribute['attribute_id'] . " AND value_id=" . $value['value'] . ";";
                    $sql[] = "REPLACE INTO $tableEal (attribute_id,value_id,value) VALUES (" . $attribute['attribute_id'] . "," . $value['value'] . ",@value);";
                }
            }
        }
        $sql[] = "SELECT NULL;";
        $sql[] = "DELETE FROM $tableEal WHERE attribute_id NOT IN ('" . implode("','", $attributes) . "')";
        $sql[] = "SELECT NULL;";
        $sql[] = "DELETE FROM $tableEal WHERE value_id NOT IN ('" . implode("','", $value_ids) . "')";
        $commit = true;


        foreach ($sql as $k => $s) {

            try {
                if ($k % 2 == 0) {
                    $read->fetchAll($s);
                } else {
                    $write->exec($s);
                }
            } catch (Mage_Core_Exception $e) {
                $commit = false;
            } catch (Exception $e) {

                $commit = false;
            }
        }
        if (!$commit) {
            Mage::getSingleton("core/session")->addError(Mage::helper("estimateddeliverydate")->__("Error while processing. Rollback happened."));
            $write->rollback();
            return false;
        } else {
            $write->commit();
            Mage::getSingleton("core/session")->addSuccess(Mage::helper("estimateddeliverydate")->__("Leadtime/Attribute updated. Go to <a href='" . Mage::helper("adminhtml")->getUrl("estimateddeliverydate/adminhtml_manageleadtimes/index/") . "'>Catalog > Attributes > Manage Leadtime/Attribute</a>."));
        }



        /*  Liste des  attributs disponible dans la bdd */
    }

    public function saveProductTabData(Varien_Event_Observer $observer) {

        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = $observer->getEvent()->getProduct();

            try {
                /**
                 * Perform any actions you want here
                 *
                 */
                $storeId = Mage::app()->getRequest()->getParam('store');
                @$data = $this->_getRequest()->getPost();
                (@$data["backorders_leadtime"]) ? $backordersLeadtime = @$data["backorders_leadtime"] : $backordersLeadtime = null;
                (@$data["use_config_backorders_leadtime"]) ? $useConfigBackordersLeadtime = @$data["use_config_backorders_leadtime"] : $useConfigBackordersLeadtime = 0;
                (@$data["use_base_backorders_leadtime"]) ? $useBaseBackordersLeadtime = @$data["use_base_backorders_leadtime"] : $useBaseBackordersLeadtime = false;

                (@$data["orders_leadtime"]) ? $ordersLeadtime = @$data["orders_leadtime"] : $ordersLeadtime = null;
                (@$data["use_config_orders_leadtime"]) ? $useConfigOrdersLeadtime = @$data["use_config_orders_leadtime"] : $useConfigOrdersLeadtime = 0;
                (@$data["use_base_orders_leadtime"]) ? $useBaseOrdersLeadtime = @$data["use_base_orders_leadtime"] : $useBaseOrdersLeadtime = false;



                if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $product->setStoreId($storeId);
                }


                if ($useConfigBackordersLeadtime != 1) {
                    $useConfigBackordersLeadtime = 0;
                }
                if ($useBaseBackordersLeadtime != 1 && ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID)) {
                    $useBaseBackordersLeadtime = 0;
                }

                $product->setData("backorders_leadtime", $backordersLeadtime);
                $product->setData("use_config_backorders_leadtime", $useConfigBackordersLeadtime);
                if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $product->setData('use_base_backorders_leadtime', $useBaseBackordersLeadtime);
                }


                if ($useConfigOrdersLeadtime != 1) {
                    $useConfigOrdersLeadtime = 0;
                }
                if ($useBaseOrdersLeadtime != 1 && ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID)) {
                    $useBaseOrdersLeadtime = 0;
                }

                $product->setData("orders_leadtime", $ordersLeadtime);
                $product->setData("use_config_orders_leadtime", $useConfigOrdersLeadtime);
                if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $product->setData('use_base_orders_leadtime', $useBaseOrdersLeadtime);
                }

                /* echo "ID:" . $storeId . "<br>";
                  echo '$backordersLeadtime=' . $backordersLeadtime . "<br>";
                  echo '$useConfigBackordersLeadtime=' . $useConfigBackordersLeadtime . "<br>";
                  echo '$useBaseBackordersLeadtime=' . $useBaseBackordersLeadtime . "<br>";
                  echo "ID:" . $storeId . "<br>";
                  echo '$ordersLeadtime=' . $ordersLeadtime . "<br>";
                  echo '$useConfigOrdersLeadtime=' . $useConfigOrdersLeadtime . "<br>";
                  echo '$useBaseOrdersLeadtime=' . $useBaseOrdersLeadtime . "<br>";
                  die();
                 */
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    /**
     * Retrieve the product model
     *
     * @return Mage_Catalog_Model_Product $product
     */
    public function getProduct() {
        return Mage::registry('product');
    }

    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest() {
        return Mage::app()->getRequest();
    }

    public function addComment($observer) {
        $order = $observer->getEvent()->getOrder();
        $order->addStatusHistoryComment(strip_tags(Mage::helper("estimateddeliverydate/data")->getCartMessage()), false);
        $this->orderUpdate($observer);
    }

    public function orderUpdate($observer) {
        $order = $observer->getEvent()->getOrder();
        $order->setEstimatedDeliveryDate(Mage::helper("estimateddeliverydate/data")->getEstimatedDeliveryDate($order));
        $order->save();
    }

}
