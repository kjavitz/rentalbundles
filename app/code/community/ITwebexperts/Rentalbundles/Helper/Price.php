<?php

class ITwebexperts_Rentalbundles_Helper_Price extends ITwebexperts_Payperrentals_Helper_Price
{
    public function _calculatePrice(Mage_Catalog_Model_Product $product, $startingDate, $endingDate, $qty, $customerGroup, $useCurrency = false)
    {
        if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $product->getRentalbundlesType()) {
            $model = Mage::getModel('rentalbundles/product_type_reservation');
            $requestParams = Mage::app()->getRequest()->getParams();
            if (!Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId())) {
                $filteredDates = array();
                if (array_key_exists('start_date', $requestParams)) $filteredDates['start_date'] = $requestParams['start_date'];
                if (array_key_exists('end_date', $requestParams)) $filteredDates['end_date'] = $requestParams['end_date'];
                if (!count($filteredDates)) return parent::_calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup, $useCurrency);
                $filteredDates = ITwebexperts_Payperrentals_Helper_Data::filterDates($filteredDates, true);
                $requestParams['start_date'] = $filteredDates['start_date'];
                $requestParams['end_date'] = $filteredDates['end_date'];
            }

            $buyRequest = new Varien_Object($requestParams);
            // Calculating start/end date for a country
            $buyRequest = $model->processCountry($buyRequest, $product);


            if ($buyRequest instanceof Varien_Object) {
                $startingDate = $buyRequest->getData(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::START_DATE_OPTION);
                $endingDate = $buyRequest->getData(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::END_DATE_OPTION);
            } else {
                $item = $this->_getItem($qty, $product);
                if ($item) {
                    $startingDate = $item->getStartTurnoverBefore();
                    $endingDate = $item->getEndTurnoverAfter();
                }
            }
        }

        return parent::_calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup, $useCurrency);
    }

    /**
     * @param $option
     * @param $product
     * @return Mage_Sales_Model_Quote_Item|null
     */
    protected function _getItem($option, $product)
    {
        if (!($option instanceof Mage_Sales_Model_Quote_Item_Option)) {
            return null;
        }

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            return null;
        }

        $item = $option->getItem();
        if (!($item instanceof Mage_Sales_Model_Quote_Item)) {
            return null;
        }

        $children = $item->getChildren();
        if (!is_array($children) || !count($children)) {
            return null;
        }

        $currentItem = null;
        foreach ($children as $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            if ($item->getProductId() == $product->getId()) {
                return $item;
            }
        }

        return null;
    }
}