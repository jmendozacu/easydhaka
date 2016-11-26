<?php

class Wyomind_Estimateddeliverydate_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getLeadtime($_product, $storeId, $type) {


        $data = $this->getData($_product->getId(), $storeId, $type, $qty=1);

        if (isset($_GET['edd'])) {
            echo "<br><br>----------------getLeadTime()----------------<br>";
        }
        $v = explode('-', $data['leadtime']);
        $leadtime = array("from" => $v[0], "to" => @$v[1]);
        if (isset($_GET['edd'])) {
            echo "Base Lead time from +" . $leadtime['from'] . ", to +" . $leadtime['to'] . "<br>";
        }
        /* Additional lead times */

        if (Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings/use_base_attribute", $storeId)) {
            $additional = array("from" => 0, "to" => 0);
            foreach (explode(',', Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings/base_attribute", $storeId))as $attribute) {

                $option_id = Mage::getModel("catalog/product")->load($_product->getId())->getData(Mage::getModel("eav/entity_attribute")->load($attribute)->getAttributeCode());

                if ($option_id) {
                    $data = Mage::getModel("estimateddeliverydate/attributes")->getCollection()
                            ->addFieldToFilter('attribute_id', $attribute)
                            ->addFieldToFilter('value_id', $option_id);

                    foreach ($data as $d) {
                        $v = explode('-', $d->getValue());
                        $additional["from"]+= $v[0];
                        $additional["to"]+= $v[1];
                    }
                }
            }

            switch (Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings/base_attribute_scope", $storeId)) {
                case 1:
                    $leadtime["from"]+=$additional["from"];
                    $leadtime["to"]+=$additional["to"];
                    break;
                case 2:
                    if ($type == null) {
                        $leadtime["from"]+=$additional["from"];
                        $leadtime["to"]+=$additional["to"];
                    }
                    break;
                case 3:
                    if ($type != null) {
                        $leadtime["from"]+=$additional["from"];
                        $leadtime["to"]+=$additional["to"];
                    }
                    break;
            }
        }
        if (isset($_GET['edd'])) {
            echo "Attribute Lead time from +" . $additional['from'] . ", to +" . $additional['to'] . "<br>";
        }


        return $this->calculateNextShipping($storeId, $type . "order", $leadtime);
    }

    public function calculateNextShipping($storeId, $mode, $leadtime) {
        if (isset($_GET['edd'])) {
            echo "<br><br>----------------CalculateNextShipping()----------------<br>";
        }

        if ($mode == "backorder") {
            $m = "_bo";
        }

        $currentGmtTime = Mage::getSingleton('core/date')->gmtTimestamp();
        $cutOff = Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings" . $m . "/" . $mode . "_last_shipping_time", $storeId);
        $shippingDays = explode(',', Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings" . $m . "/" . $mode . "_shipping_days", $storeId));
        $daysoff = explode("\n", Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings/holidays", $storeId));
        $daysoff = array_map('trim', $daysoff);

        $delay['from'] = $leadtime['from'];
        $delay['to'] = $leadtime['to'];

        $x = 0;
        if (Mage::getSingleton('core/date')->date("H,i,s", $currentGmtTime) > $cutOff) {
            $delay["from"] ++;
            $delay["to"] ++;
            $x++;
            if (isset($_GET['edd'])) {
                echo "After $cutOff +1 <br>";
            }
        }
        for ($i = $x; $i <= $delay["from"]; $i++) {
            if (!in_array(Mage::getSingleton('core/date')->date("w", $currentGmtTime + 86400 * $i), $shippingDays) || in_array(Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i), $daysoff)) {
                $delay["from"] ++;

                if (isset($_GET['edd'])) {

                    if (in_array(Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i), $daysoff)) {
                        echo "DAY OFF! No Shipping on : " . Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i);
                    } else {
                        echo "No shipping on " . Mage::getSingleton('core/date')->date("l", $currentGmtTime + 86400 * $i);
                    }
                    echo " +1<br>";
                }
            }
        }
        if (isset($_GET['edd'])) {
            echo "<b>Total leadtime from " . $delay['from'] . "</b><br>";
        }
        for ($i = $x; $i <= $delay["to"]; $i++) {

            if (!in_array(Mage::getSingleton('core/date')->date("w", $currentGmtTime + 86400 * $i), $shippingDays) || in_array(Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i), $daysoff)) {
                $delay["to"] ++;

                if (isset($_GET['edd'])) {

                    if (in_array(Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i), $daysoff)) {
                        echo "DAY OFF! No Shipping on : " . Mage::getSingleton('core/date')->date("Y-m-d", $currentGmtTime + 86400 * $i);
                    } else {
                        echo "No shipping on " . Mage::getSingleton('core/date')->date("l", $currentGmtTime + 86400 * $i);
                    }
                    echo " +1<br>";
                }
            }
        }
        if (isset($_GET['edd'])) {
            echo "<b>Total leadtime " . $delay['to'] . "</b><br>";
        }

        return $delay;
    }

    public function getCountdown($storeId, $type) {
        if (isset($_GET['edd'])) {
            echo "<br><br>----------------getCountDown()----------------<br>";
        }
        $i = 0;
        $countdown = 0;
        if ($type == "back") {
            $m = "_bo";
        }

        $currentGmtTime = Mage::getSingleton('core/date')->gmtTimestamp();
        $shippingDays = explode(',', Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings" . $m . "/" . $type . "order_shipping_days", $storeId));
        $cutOff = Mage::getStoreConfig("estimateddeliverydate/settings" . $m . "/" . $type . "order_last_shipping_time", $storeId);
        if (Mage::getSingleton('core/date')->date("H,i,s", $currentGmtTime) > $cutOff) {
            $i++;
            if (isset($_GET['edd'])) {
                echo "After $cutOff +1 <br>";
            }
        }
        while (!in_array(Mage::getSingleton('core/date')->date("w", $currentGmtTime + 86400 * $i), $shippingDays)) {
            if (isset($_GET['edd'])) {
                echo "No shipping on " . Mage::getSingleton('core/date')->date("l", $currentGmtTime + 86400 * $i) . "+1" . "<br>";
            }
            $i++;
        }
        $cutOff = explode(',', $cutOff);

        $cutOffTime = Mage::getSingleton('core/date')->gmtTimestamp(Mage::getSingleton('core/date')->date("Y-m-d 00:00:00")) + $cutOff[0] * 3600 + $cutOff[1] * 60 + $cutOff[2] + 86400 * $i;

        if (isset($_GET['edd'])) {
            echo "Next shipping : " . Mage::getSingleton('core/date')->date("Y-m-d H:i:s", $cutOffTime) . "<br>";
        }

        $countdown+=($cutOffTime) - $currentGmtTime;
        return $countdown;
    }

    public function getProductMessage($_product, $storeId) {
        $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
        $type = ((int) $inventory->getQty() <= 0 ) ? "back" : null;
        if ($type == "back") {
            $m = "_bo";
        }
        if ($_product->isAvailable()) {
            $message = Mage::getStoreConfig("estimateddeliverydate/display_settings/message_in_product_page".$m, $storeId);
            $dateFormat = Mage::getStoreConfig("estimateddeliverydate/display_settings/date_format", $storeId);
            $leadtime = $this->getLeadtime($_product, $storeId, $type);

            $date['from'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * $leadtime["from"]);
            $date['from'] = $this->dateTranslate($date['from']);
            $date['to'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * $leadtime["to"]);
            $date['to'] = $this->dateTranslate($date['to']);
            $week['from'] = floor($leadtime['from'] / 7);
            $week['to'] = ceil($leadtime['to'] / 7);
            $countdown = "<span id='edd_countdown' countdown='" . $this->getCountdown($storeId, $type) . "'></span>";
            if (isset($_GET['edd'])) {
                echo "<br><br>----------------OUPUT----------------<br>";
            }
            return str_replace(array("{{leadtime_from}}", "{{leadtime_to}}", "{{date_from}}", "{{date_to}}", "{{week_from}}", "{{week_to}}", "{{countdown}}"), array($leadtime['from'], $leadtime['to'], $date['from'], $date['to'], $week['from'], $week['to'], $countdown), $message);
        }
    }

    public function getProductInCartMessage($_product,$backorderQty) {
        $type = "";
        $m = "";
        if ($backorderQty > 0) {
            $type = "back";
            $m = "_bo";
        }
        if ($_product->isAvailable() && Mage::getStoreConfig("estimateddeliverydate/display_settings/use_message_for_cart_item")) {
            $storeId = Mage::app()->getRequest()->getParam('store');
            $message = Mage::getStoreConfig("estimateddeliverydate/display_settings/message_for_cart_item".$m, $storeId);
            $dateFormat = Mage::getStoreConfig("estimateddeliverydate/display_settings/date_format", $storeId);
            $leadtime = $this->getLeadtime($_product, $storeId, $type);
            $date['from'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * $leadtime["from"]);
            $date['from'] = $this->dateTranslate($date['from']);
            $date['to'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * $leadtime["to"]);
            $date['to'] = $this->dateTranslate($date['to']);
            //$countdown = $this->getCountdown($storeId, $type);
            if (isset($_GET['edd'])) {
                echo "<br><br>----------------OUPUT----------------<br>";
            }
            return str_replace(array("{{leadtime_from}}", "{{leadtime_to}}", "{{date_from}}", "{{date_to}}"), array($leadtime['from'], $leadtime['to'], $date['from'], $date['to']), $message);
        } else {
            return "";
        }
    }

    function cmp($a, $b) {
        if ($a['from'] > $b['from']) {
            return 1;
        } elseif ($a['from'] < $b['from']) {
            return -1;
        } else {
            return 0;
        }
    }

    public function getCartMessage() {

        $session = Mage::getSingleton('checkout/session');
        $storeId = Mage::app()->getRequest()->getParam('store');
        $message = Mage::getStoreConfig("estimateddeliverydate/display_settings/message_in_cart", $storeId);
        $dateFormat = Mage::getStoreConfig("estimateddeliverydate/display_settings/date_format", $storeId);
        $items = $session->getQuote()->getAllItems();

        if (count($items)) {
            foreach ($items as $item) {
                if (isset($_GET['edd'])) {
                    echo "......................" . $item->getName() . "......................";
                }
                $_product = Mage::getModel('catalog/product')->load($item->getProductId());
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                
                $type = ((int) $inventory->getQty()-$item->getQty() < 0 && $inventory->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) ? "back" : null;
                $leadtime[] = $this->getLeadtime($_product, $storeId, $type);
            }



            usort($leadtime, array("Wyomind_Estimateddeliverydate_Helper_Data", "cmp"));
            $leadtime = array_pop($leadtime);

            $date['from'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * ($leadtime['from']));
            $date['from'] = $this->dateTranslate($date['from']);
            $date['to'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * ($leadtime['to']));
            $date['to'] = $this->dateTranslate($date['to']);
            $week['from'] = floor($leadtime['from'] / 7);
            $week['to'] = ceil($leadtime['to'] / 7);
            return "<ul class='messages'><li class='success-msg'><ul><li><span>" . str_replace(array("{{leadtime_from}}", "{{leadtime_to}}", "{{date_from}}", "{{date_to}}", "{{week_from}}", "{{week_to}}"), array($leadtime['from'], $leadtime['to'], $date['from'], $date['to'], $week['from'], $week['to']), $message) . "</span></li></ul></li></ul>";
        }
    }

    public function dateTranslate($date) {

        $longDays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $longDaysLocale = array($this->__('Monday'), $this->__('Tuesday'), $this->__('Wednesday'), $this->__('Thursday'), $this->__('Friday'), $this->__('Saturday'), $this->__('Sunday'));

        $shortDays = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
        $shortDaysLocale = array($this->__('Mon'), $this->__('Tue'), $this->__('Wed'), $this->__('Thu'), $this->__('Fri'), $this->__('Sat'), $this->__('Sun'));

        $months = array("January", 'February', 'March', 'April', 'May', 'June', 'Jully', 'August', 'September', 'October', 'November', 'December');
        $monthsLocale = array($this->__('January'), $this->__('Februar'), $this->__('March'), $this->__('April'), $this->__('May'), $this->__('June'), $this->__('Jully'), $this->__('August'), $this->__('September'), $this->__('October'), $this->__('November'), $this->__('December'));

        return str_replace(array($longDays, $shortDays, $months), array($longDaysLocale, $shortDaysLocale, $monthsLocale), $date);
    }

    public function getData($productId, $storeId = 0, $type = null) {


        $product = array();
        $productBase = Mage::getModel('catalog/product')->load($productId);
        $productStore = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

        if ($type == "back") {
            $m = "_bo";
        }
// config value
        $product['config']['leadtime'] = Mage::getStoreConfig("estimateddeliverydate/leadtimes_settings" . $m . "/default_" . $type . "orders_leadtime", $storeId);

// default values
        $product['base']['leadtime'] = $productBase->getData($type . "orders_leadtime");
        if (is_null($product['base']['leadtime'])) {
            $product['base']['leadtime'] = $product['config']['leadtime'];
        }

        $product["leadtime"] = $product['base']['leadtime'];

        $product['base']['use_config_leadtime'] = $productBase->getData("use_config_" . $type . "orders_leadtime");
        if (is_null($product['base']['use_config_leadtime'])) {
            $product['base']['use_config_leadtime'] = 1;
        }

        $product["use_config_leadtime"] = $product['base']['use_config_leadtime'];

        if ($product['base']['use_config_leadtime']) {
            $product["leadtime"] = $product['config']['leadtime'];
            $product['base']['leadtime'] = $product['config']['leadtime'];
        }


// store view values
        if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            $product['store']['leadtime'] = $productStore->getData($type . "orders_leadtime");
            if (is_null($product['store']['leadtime'])) {
                $product['store']['leadtime'] = $product['config']['leadtime'];
            }

            $product["leadtime"] = $product['store']['leadtime'];

            $product['store']['use_config_leadtime'] = $productStore->getData("use_config_" . $type . "orders_leadtime");
            if (is_null($product['store']['use_config_leadtime'])) {
                $product['store']['use_config_leadtime'] = 1;
            }

            $product["use_config_leadtime"] = $product['store']['use_config_leadtime'];
            if ($product['store']['use_config_leadtime']) {
                $product["leadtime"] = $product['config']['leadtime'];
                $product['store']['leadtime'] = $product['config']['leadtime'];
            }

            $product['store']['use_base_leadtime'] = $productStore->getData("use_base_" . $type . "orders_leadtime");

            if (is_null($product['store']['use_base_leadtime'])) {
                $product['store']['use_base_leadtime'] = 1;
            }

            $product["use_base_leadtime"] = $product['store']['use_base_leadtime'];

            if ($product['store']['use_base_leadtime']) {
                $product["leadtime"] = $product['base']['leadtime'];
                $product["use_config_leadtime"] = $product['base']['use_config_leadtime'];
            }
        }


        if (isset($_GET['edd'])) {
            echo "<br><br>----------------getData()----------------<br>";
        }
        if (isset($_GET['edd'])) {
            Zend_debug::dump($product);
        }
        return $product;
    }

    public function getEstimatedDeliveryDate($order) {

        $storeId = Mage::app()->getRequest()->getParam('store');
        $message = Mage::getStoreConfig("estimateddeliverydate/display_settings/message_in_cart", $storeId);
        $dateFormat = Mage::getStoreConfig("estimateddeliverydate/display_settings/date_format", $storeId);
        $items = $order->getAllItems();

        if (count($items)) {
            foreach ($items as $item) {
                if (isset($_GET['edd'])) {
                    echo "......................" . $item->getName() . "......................";
                }
                $_product = Mage::getModel('catalog/product')->load($item->getProductId());
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                $type = ((int) $inventory->getQty() <= 0 ) ? "back" : null;
                $leadtime[] = $this->getLeadtime($_product, $storeId, $type);
            }

            usort($leadtime, array("Wyomind_Estimateddeliverydate_Helper_Data", "cmp"));
            $leadtime = array_pop($leadtime);

            $date['from'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * ($leadtime['from']));
            $date['from'] = $this->dateTranslate($date['from']);
            $date['to'] = Mage::getSingleton('core/date')->date($dateFormat, Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * ($leadtime['to']));
            $date['to'] = $this->dateTranslate($date['to']);
            $week['from'] = floor($leadtime['from'] / 7);
            $week['to'] = ceil($leadtime['to'] / 7);

            $msg = str_replace(array("{{leadtime_from}}", "{{leadtime_to}}", "{{date_from}}", "{{date_to}}", "{{week_from}}", "{{week_to}}"), array($leadtime['from'], $leadtime['to'], $date['from'], $date['to'], $week['from'], $week['to']), $message);

            return json_encode(array('time' => (Mage::getSingleton('core/date')->gmtTimestamp() + 86400 * $leadtime['from']), 'msg' => $msg));
        }
    }

}
