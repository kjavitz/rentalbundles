<?php

require_once(Mage::getBaseDir('code') . DS . 'community' . DS . 'ITwebexperts' . DS . 'Payperrentals' . DS . 'controllers' . DS . 'Adminhtml' . DS . 'AjaxController.php');

/**
 * Class ITwebexperts_Rentalbundles_Adminhtml_AjaxController
 */
class ITwebexperts_Rentalbundles_Adminhtml_AjaxController extends ITwebexperts_Payperrentals_Adminhtml_AjaxController
{
    /**
     *
     */
    public function getPriceAction()
    {
        if (!$this->getRequest()->getParam('product_id')) {
            return;
        }
        $Product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
        $needsConfigure = false;
        $priceAmount = -1;
        $stockAvail = '';
        $stockRest = '';
        //if(is_object($Product)){

        if ($Product->isConfigurable()) {
            $Product = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($this->getRequest()->getParam('super_attribute'), $Product);
            $needsConfigure = true;
        }
        if (!is_object($Product)) {
            $price = array(
                'amount' => -1,
                'stockAvail' => 0,
                'stockRest' => 0,
                'needConfigure' => $needsConfigure,
                'item' => $this->getRequest()->getParam('product_id'),
                'formatAmount' => Mage::helper('core')->currency(-1)
            );

            $this->getResponse()->setBody(Zend_Json::encode($price));
            return;
        }
        if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE) {
            $needsConfigure = true;
        }
        if ($Product->getOptions()) {
            $needsConfigure = true;
        }

        $qty = urldecode($this->getRequest()->getParam('qty'));
        if (!$qty) {
            $qty = 1;
        }
        $qty1 = $qty;
        $customerGroup = ITwebexperts_Payperrentals_Helper_Data::getCustomerGroup();

        $params = $this->getRequest()->getParams();
        if (isset($params['configurate-product-id']) && !isset($params['configurate'])) {
            $params['configurate'] = $params['configurate-product-id'];
        }
        if (urldecode($this->getRequest()->getParam('isGrid'))) {
            /** @var $Product Mage_Catalog_Model_Product */
            $_dateRange = ITwebexperts_Payperrentals_Helper_Data::getFirstAvailableDateRange($Product);
            $startingDate = $_dateRange['start_date'];
            $endingDate = $_dateRange['end_date'];
        } else {
            if (!Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId())) {
                $newParams['start_date'] = $params['start_date'];
                $newParams['end_date'] = $params['end_date'];
                $newParams = ITwebexperts_Payperrentals_Helper_Data::filterDates($newParams, true);
                $startingDate = $params['start_date'] = $params['read_start_date'] = $newParams['start_date'];
                $endingDate = $params['end_date'] = $params['read_end_date'] = $newParams['end_date'];
            } else {
                $startingDate = $params['start_date'];
                $endingDate = $params['end_date'];
            }
        }

        if ($Product->getTypeId() != ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE || $Product->getBundlePricingtype() == ITwebexperts_Payperrentals_Model_Product_Bundlepricingtype::PRICING_BUNDLE_FORALL) {
            if (is_object($Product)) {
                $Product = Mage::getModel('catalog/product')->load($Product->getId());
                $priceAmount = ITwebexperts_Payperrentals_Helper_Price::calculatePrice($Product, $startingDate, $endingDate, $qty, $customerGroup);
            } else {
                $priceAmount = -1;
            }
        } elseif ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE) {
            if ($this->getRequest()->getParam('bundle_option') && !urldecode($this->getRequest()->getParam('isGrid'))) {
                $selectionIds = $this->getRequest()->getParam('bundle_option');
                $selectedQtys = $this->getRequest()->getParam('bundle_option_qty');
                foreach ($selectedQtys as $i1 => $j1) {
                    if (is_array($j1)) {
                        foreach ($j1 as $k1 => $p1) {
                            $selectedQtys[$i1][$k1] = $qty * ($p1 == 0 ? 1 : $p1);
                        }
                    } else {
                        $selectedQtys[$i1] = $qty * ($j1 == 0 ? 1 : $j1);
                    }
                }
            } else {
                $_selectionCollection = $Product->getTypeInstance(true)->getSelectionsCollection($Product->getTypeInstance(true)->getOptionsIds($Product), $Product);
                $selectionIds = array();
                foreach ($_selectionCollection as $_option) {
                    if (array_search($_option->getProductId(), $selectionIds) === false) {
                        $selectionIds[] = $_option->getProductId();
                    }
                }
            }
            $selections = $Product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $Product);
            $priceVal = 0;
            $qty1 = $qty;
            foreach ($selections->getItems() as $selection) {
                $Product = Mage::getModel('catalog/product')->load($selection->getProductId());
                if (isset($selectedQtys[$selection->getOptionId()][$selection->getSelectionId()])) {
                    $qty = $selectedQtys[$selection->getOptionId()][$selection->getSelectionId()];
                } elseif (isset($selectedQtys[$selection->getOptionId()])) {
                    $qty = $selectedQtys[$selection->getOptionId()];
                } else {
                    $qty = $qty1;
                }

                if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE) {
                    $paramsAfterPrepare = $this->_prepareParamDates($params, $Product);
                    $priceAmount = ITwebexperts_Payperrentals_Helper_Price::calculatePrice($Product, $paramsAfterPrepare['start_date'], $paramsAfterPrepare['end_date'], $qty, $customerGroup);
                    if ($priceAmount == -1) {
                        $priceVal = -1;
                        break;
                    }
                    $priceVal = $priceVal + $qty * $priceAmount;
                } else {
                    $priceVal = $priceVal + $qty * $Product->getPrice();
                }
            }
            $priceAmount = $priceVal;

        }
        if ($priceAmount != -1) {
            if ($Product->getHasmultiply() == ITwebexperts_Payperrentals_Model_Product_Hasmultiply::STATUS_ENABLED && !is_null($qty)) {
                $priceAmount += ITwebexperts_Payperrentals_Helper_Data::getOptionsPrice($Product, $priceAmount) * $qty;
            } else {
                $priceAmount += ITwebexperts_Payperrentals_Helper_Data::getOptionsPrice($Product, $priceAmount);
            }
        } else {
            $needsConfigure = true;
        }

        if ($priceAmount != -1 && $this->getRequest()->getParam('saveDates')) {
            if (Mage::getStoreConfig(ITwebexperts_Payperrentals_Helper_Data::XML_PATH_USE_GLOBAL_DAYS) == 1) {
                $_useNonsequential = Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId());
                if (!$_useNonsequential) {
                    Mage::getSingleton('core/session')->setData('startDateInitial', $startingDate);
                    Mage::getSingleton('core/session')->setData('endDateInitial', $endingDate);
                } else {
                    Mage::getSingleton('core/session')->setData('startDateInitial', $startingDate);
                }
            }
        }

        $stockArr = array();
        $Product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));

        if ($Product->getTypeId() != ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE) {
            if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE || ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_CONFIGURABLE && $Product->getIsReservation() != ITwebexperts_Payperrentals_Model_Product_Isreservation::STATUS_DISABLED)) {
                $stockArr[$Product->getId()] = ITwebexperts_Payperrentals_Helper_Data::getStock($Product->getId(), $startingDate, $endingDate, $qty);
            } else {
                $_product1 = Mage::getModel('catalog/product')->load($Product->getId());
                $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product1)->getQty();
                $stockArr[$Product->getId()]['avail'] = $qtyStock;
                $stockArr[$Product->getId()]['remaining'] = $stockArr[$Product->getId()]['avail'] - $qty;
            }


        } elseif ($this->getRequest()->getParam('bundle_option')) {
            $selectionIds = $this->getRequest()->getParam('bundle_option');
            $selectedQtys = $this->getRequest()->getParam('bundle_option_qty');
            foreach ($selectedQtys as $i1 => $j1) {
                if (is_array($j1)) {
                    foreach ($j1 as $k1 => $p1) {
                        $selectedQtys[$i1][$k1] = $qty * ($p1 == 0 ? 1 : $p1);
                    }
                } else {
                    $selectedQtys[$i1] = $qty * ($j1 == 0 ? 1 : $j1);
                }
            }
            $selections = $Product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $Product);

            $qty1 = $qty;
            foreach ($selections->getItems() as $selection) {
                $Product = Mage::getModel('catalog/product')->load($selection->getProductId());
                /*if(isset($selectedQtys[$selection->getOptionId()])){
					$qty = $selectedQtys[$selection->getOptionId()];
				}else{
					$qty = $qty1;
				}*/

                if (isset($selectedQtys[$selection->getOptionId()][$selection->getSelectionId()])) {
                    $qty = $selectedQtys[$selection->getOptionId()][$selection->getSelectionId()];
                } elseif (isset($selectedQtys[$selection->getOptionId()])) {
                    $qty = $selectedQtys[$selection->getOptionId()];
                } else {
                    $qty = $qty1;
                }


                if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE) {
                    if (!isset($stockArr[$selection->getProductId()])) {
                        $stockArr[$selection->getProductId()] = ITwebexperts_Payperrentals_Helper_Data::getStock($Product->getId(), $startingDate, $endingDate, $qty);
                        //$stockArr[$selection->getProductId()]['remaining'] = $stockArr[$selection->getProductId()]['remaining'] - ($qty-1);
                    } else {
                        $stockArr[$selection->getProductId()]['remaining'] = $stockArr[$selection->getProductId()]['remaining'] - $qty;
                    }

                } else {
                    if (!isset($stockArr[$selection->getProductId()])) {
                        $_product1 = Mage::getModel('catalog/product')->load($selection->getProductId());
                        $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product1)->getQty();
                        $stockArr[$selection->getProductId()]['avail'] = $qtyStock;
                        $stockArr[$selection->getProductId()]['remaining'] = $stockArr[$selection->getProductId()]['avail'] - $qty;
                    } else {
                        $stockArr[$selection->getProductId()]['remaining'] = $stockArr[$selection->getProductId()]['remaining'] - $qty;
                    }
                }
            }
        }

        $maxQty = 100000;
        $stockRest = 100000;
        $stockAvailText = '';
        $stockRestText = '';
        foreach ($stockArr as $id => $avArr) {

            if ($stockAvail > $avArr['avail']) {
                //$maxQty = $avArr['avail'];
                $stockAvail = $avArr['avail'];
            }
            if ($stockRest > $avArr['remaining']) {
                $stockRest = $avArr['remaining'];
                $pid = $id;
            }
            $curProd = Mage::getModel('catalog/product')->load($id);
            $stockAvailText .= 'Stock available for product ' . $curProd->getName() . ': ' . $avArr['avail'] . '<br/>';
            $stockRestText .= 'Stock remaining for product ' . $curProd->getName() . ': ' . $avArr['remaining'] . '<br/>';
        }
        if (isset($pid)) {
            $maxQty = intval($stockArr[$pid]['avail'] / intval(($stockArr[$pid]['avail'] - $stockRest) / $qty1));
        }

        $price = array(
            'amount' => $priceAmount,
            'stockAvail' => $maxQty,
            'stockRest' => ($maxQty - $qty1),
            'needConfigure' => $needsConfigure,
            'item' => $this->getRequest()->getParam('product_id'),
            'formatAmount' => Mage::helper('core')->currency($priceAmount)
        );

        $this->getResponse()->setBody(Zend_Json::encode($price));
    }

    private function _prepareParamDates($params, $product)
    {
        if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $product->getRentalbundlesType()) {
            $model = Mage::getModel('rentalbundles/product_type_reservation');
            $buyRequest = new Varien_Object($params);
            // Calculating start/end date for a country
            $buyRequest = $model->processCountry($buyRequest, $product);

            if ($buyRequest instanceof Varien_Object) {
                $params['start_date'] = $params['read_start_date'] = $buyRequest->getData(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::START_DATE_OPTION);
                $params['end_date'] = $params['read_end_date'] = $buyRequest->getData(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::END_DATE_OPTION);
            }
        }
        return $params;
    }
}