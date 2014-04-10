<?php
require_once(Mage::getModuleDir('controllers', 'ITwebexperts_Payperrentals') . DS . 'AjaxController.php');

class ITwebexperts_Rentalbundles_AjaxController extends ITwebexperts_Payperrentals_AjaxController
{
    /**
     * todo needs refactor
     * Rewrite for action, because we need filter dates lie 40-43
     */
    public function getPriceAction()
    {
        if (!$this->getRequest()->getParam('product_id')) {
            return;
        }
        $Product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
        /*Allow rent configurable with associated simply products*/
        $normalPrice = '';
        $needsConfigure = false;
        $isConfigurable = false;
        if ($Product->isConfigurable()) {
            $isConfigurable = true;
            $_childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($this->getRequest()->getParam('super_attribute'), $Product);
            if (is_object($_childProduct) && $_childProduct->getTypeId() != 'simple') {
                $Product = $_childProduct;
            } else {
                $needsConfigure = true;
            }
            $normalPrice = ITwebexperts_Payperrentals_Helper_Price::getPriceListHtml($Product);
        }
        if (is_object($Product) && $this->getRequest()->getParam('start_date')) {
            $qty = urldecode($this->getRequest()->getParam('qty'));
            $customerGroup = ITwebexperts_Payperrentals_Helper_Data::getCustomerGroup();

            $params = $this->getRequest()->getParams();
            if (!Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId())) {
                //$params = $this->_filterDates($params, array('start_date', 'end_date'));
                $paramsAll = $this->getRequest()->getParams();
                $newParams['start_date'] = $paramsAll['start_date'];
                $newParams['end_date'] = $paramsAll['end_date'];
                if (!$this->getRequest()->getParam('is_filtered')) {
                    $newParams = ITwebexperts_Payperrentals_Helper_Data::filterDates($newParams, true);
                }
                $startingDate = $newParams['start_date'];
                $endingDate = $newParams['end_date'];
            } else {
                $startingDate = $params['start_date'];
                $endingDate = $params['end_date'];
            }
            $selDays = false;
            $availDate = $startingDate;
            if ($this->getRequest()->getParam('selDays')) {
                $selDays = (int)$this->getRequest()->getParam('selDays') + 1;
                $availDate = false;
            }
            $onclick = '';
            if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_GROUPED) {
                if (is_object($Product) && urldecode($this->getRequest()->getParam('read_start_date')) != '' && (urldecode($this->getRequest()->getParam('read_end_date')) || Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId()))) {
                    $associatedProducts = $Product->getTypeInstance(true)
                        ->getAssociatedProducts($Product);
                    //$priceVal = 0;
                    foreach ($associatedProducts as $Product) {
                        //Zend_Debug::dump($selection->getData());
                        if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE) {

                            $Product = Mage::getModel('catalog/product')->load($Product->getId());
                            $_productAssoc = $Product;
                            $priceAmount = ITwebexperts_Payperrentals_Helper_Price::calculatePrice($Product, $startingDate, $endingDate, $qty, $customerGroup, true);
                            //if($priceAmount == -1){

                            //}
                            $availDate = false;
                            $_maxQty = ITwebexperts_Payperrentals_Helper_Data::getQuantity($Product);
                            if ($_maxQty >= $qty) {
                                if ($selDays !== false) {
                                    $isAvailable = 0;
                                    $endingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($startingDate)));
                                    while (true) {
                                        $isAvailableArr = ITwebexperts_Payperrentals_Helper_Data::isAvailableWithQty($Product->getId(), $qty, $startingDate, $endingDate);
                                        $isAvailable = $isAvailableArr['avail'];

                                        if ($isAvailable >= 1) break;
                                        $startingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($startingDate)));
                                        $endingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($endingDate)));
                                    }
                                    if ($isAvailable >= 1) {
                                        $availDate = $startingDate;
                                    }

                                }
                            }
                        }
                    }
                    $onclick = "setLocation('" . Mage::helper('checkout/cart')->getAddUrl($_productAssoc, array('_query' => array('options' => array('start_date' => $startingDate, 'end_date' => $endingDate, 'qty' => $qty, 'is_filtered' => true), 'start_date' => $startingDate, 'end_date' => $endingDate, 'qty' => $qty, 'is_filtered' => true))) . "');";

                } else {
                    $priceAmount = -1;
                }
            } elseif ($Product->getTypeId() != ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE || $Product->getBundlePricingtype() == ITwebexperts_Payperrentals_Model_Product_Bundlepricingtype::PRICING_BUNDLE_FORALL) {
                if (is_object($Product) && urldecode($this->getRequest()->getParam('read_start_date')) != '' && (urldecode($this->getRequest()->getParam('read_end_date')) || Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId()))) {
                    $Product = Mage::getModel('catalog/product')->load($Product->getId());
                    $priceAmount = ITwebexperts_Payperrentals_Helper_Price::calculatePrice($Product, $startingDate, $endingDate, $qty, $customerGroup, true);


                    $availDate = false;
                    $_maxQty = ITwebexperts_Payperrentals_Helper_Data::getQuantity($Product);
                    if ($_maxQty >= $qty) {
                        if ($selDays !== false) {
                            $isAvailable = 0;
                            $endingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($startingDate)));
                            while (true) {
                                $isAvailableArr = ITwebexperts_Payperrentals_Helper_Data::isAvailableWithQty($Product->getId(), $qty, $startingDate, $endingDate);
                                $isAvailable = $isAvailableArr['avail'];
                                if ($isAvailable >= 1) break;
                                $startingDate = date('Y-m-d', strtotime('+1 days', strtotime($startingDate)));
                                $endingDate = date('Y-m-d', strtotime('+1 days', strtotime($endingDate)));
                            }
                            if ($isAvailable >= 1) {
                                $availDate = $startingDate;
                            }
                        }
                    }
                } else {
                    $priceAmount = -1;
                }
            } elseif ($this->getRequest()->getParam('bundle_option')) {
                if (urldecode($this->getRequest()->getParam('read_start_date')) != '' && (urldecode($this->getRequest()->getParam('read_end_date')) || Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId()))) {
                    $selectionIds = $this->getRequest()->getParam('bundle_option');
                    $selectedQtys1 = $this->getRequest()->getParam('bundle_option_qty1');
                    $selectedQtys2 = $this->getRequest()->getParam('bundle_option_qty');
                    if ($selectedQtys1)
                        foreach ($selectedQtys1 as $i1 => $j1) {
                            if (is_array($j1)) {
                                foreach ($j1 as $k1 => $p1) {
                                    $selectedQtys[$i1][$k1] = $p1;
                                }
                            } else {
                                $selectedQtys[$i1] = /*$qty **/
                                    $j1;
                            }
                        }
                    if ($selectedQtys2)
                        foreach ($selectedQtys2 as $i1 => $j1) {
                            if (is_array($j1)) {
                                foreach ($j1 as $k1 => $p1) {
                                    $selectedQtys[$i1][$k1] = $p1;
                                }
                            } else {
                                $selectedQtys[$i1] = /*$qty **/
                                    $j1;
                            }
                        }

                    $selections = $Product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $Product);
                    $priceVal = 0;
                    $availDate = false;
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
                        if ($qty == 0) {
                            $qty = 1;
                        }
                        if ($Product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE) {
                            $priceAmount = $qty * ITwebexperts_Payperrentals_Helper_Price::calculatePrice($Product, $startingDate, $endingDate, $qty, $customerGroup, true);
                            //echo $qty.'-'.$priceAmount;
                            if ($priceAmount == -1) {
                                $priceVal = -1;
                                break;
                            }

                            $availDateMax = false;
                            $_maxQty = ITwebexperts_Payperrentals_Helper_Data::getQuantity($Product);
                            if ($_maxQty >= $qty) {
                                if ($selDays !== false) {
                                    $isAvailable = 0;
                                    $endingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($startingDate)));
                                    while (true) {
                                        $isAvailableArr = ITwebexperts_Payperrentals_Helper_Data::isAvailableWithQty($Product->getId(), $qty, $startingDate, $endingDate);
                                        $isAvailable = $isAvailableArr['avail'];

                                        if ($isAvailable >= 1) break;
                                        $startingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($startingDate)));
                                        $endingDate = date('Y-m-d', strtotime('+' . $selDays . ' days', strtotime($endingDate)));
                                    }
                                    if ($isAvailable >= 1) {
                                        $availDateMax = $startingDate;
                                    }
                                }
                            }
                            if ($availDate === false || ($availDateMax !== false && strtotime($availDate) > strtotime($availDateMax))) {
                                $availDate = $availDateMax;
                            }

                            $priceVal = $priceVal + /*$qty **/
                                $priceAmount;
                        }
                    }
                    $priceAmount = $priceVal;
                } else {
                    $priceAmount = -1;
                }

            }

            if (ITwebexperts_Payperrentals_Helper_Data::useCalendarForFixedSelection()) {
                $startingDateNow = $startingDate;
            } else {
                $startingDateNow = date('Y-m-d');
            }
            $nextDay = date('Y-m-d', strtotime($startingDateNow));
            if (ITwebexperts_Payperrentals_Helper_Data::isNextHourSelection() && !ITwebexperts_Payperrentals_Helper_Data::useCalendarForFixedSelection()) {
                $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($startingDateNow)));
            }
            if (ITwebexperts_Payperrentals_Helper_Data::useListButtons()) {
                $paramsAll = $this->getRequest()->getPost();
                $newParams['start_date'] = $paramsAll['start_date'];
                $newParams['end_date'] = $paramsAll['end_date'];
                $newParams = ITwebexperts_Payperrentals_Helper_Data::filterDates($newParams, true);
                $startingDateFiltered = $newParams['start_date'];
                $endingDateFiltered = $newParams['end_date'];
                Mage::getSingleton('core/session')->setData('startDateInitial', $startingDateFiltered);
                Mage::getSingleton('core/session')->setData('endDateInitial', $endingDateFiltered);
            }
            $price = array(
                'amount' => ((intval($this->getRequest()->getParam('qty'))) * (isset($priceAmount) ? $priceAmount : -1)),
                'onclick' => $onclick,
                'needsConfigure' => $needsConfigure,
                'isConfigurable' => $isConfigurable,
                'normalPrice' => $normalPrice,
                'availdate' => ($availDate != false) ? date('Y-m-d', strtotime($availDate)) : '',
                'btnList' => (ITwebexperts_Payperrentals_Helper_Data::useListButtons() ? ITwebexperts_Payperrentals_Helper_Price::getPriceListHtml(Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id')), -1, false, true) : ''),
                'isavail' => ((date('Y-m-d', strtotime($availDate)) != $nextDay && $selDays !== false) ? false : true),
                'formatAmount' => isset($priceAmount) ? Mage::helper('core')->currency((intval($this->getRequest()->getParam('qty'))) * $priceAmount) : -1
            );
        } else {
            $price = array(
                'amount' => -1,
                'onclick' => '',
                'needsConfigure' => $needsConfigure,
                'normalPrice' => $normalPrice,
                'isConfigurable' => $isConfigurable,
                'availdate' => '',
                'availdatetime' => '',
                'isavail' => false,
                'formatAmount' => -1
            );
        }
        $this->getResponse()->setBody(Zend_Json::encode($price));
    }
}