<?php

class ITwebexperts_Rentalbundles_Helper_Price extends ITwebexperts_Payperrentals_Helper_Price
{
    public function _calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup)
    {
        if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $product->getRentalbundlesType()) {

            $model = Mage::getModel('rentalbundles/product_type_reservation');
            $buyRequest = new Varien_Object(Mage::app()->getRequest()->getParams());
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

        return parent::_calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup);
    }

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
            if ($item->getProductId() == $product->getId()) {
                return $item;
            }
        }

        return null;
    }
}