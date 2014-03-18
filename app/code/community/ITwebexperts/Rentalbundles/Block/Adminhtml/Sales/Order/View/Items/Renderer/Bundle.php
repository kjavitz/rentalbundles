<?php

class ITwebexperts_Rentalbundles_Block_Adminhtml_Sales_Order_View_Items_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Adminhtml_Sales_Order_View_Items_Renderer_Bundle
{
    /**
     * This method adds information about arrival dates for countries
     * to sales order view page in admin.
     *
     * @param $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = parent::getValueHtml($item);

        // If product isn't a country we
        // don't want to display
        // start/end dates for it.
        if (!$this->_checkValidProduct($item)) {
            return $result;
        }

        $option = $item->getProductOptions();
        $startDate = $endDate = null;

        $ppHelper = Mage::helper('payperrentals');
        if (isset($option['info_buyRequest']['start_date'])) {
            $startDate = ((array_key_exists('country_dates_normalized', $option['info_buyRequest']) && $option['info_buyRequest']['country_dates_normalized']) || preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/', $option['info_buyRequest']['start_date'])) ? $option['info_buyRequest']['start_date'] : false;
            if (!$startDate) {
                $startDateTempAr = $ppHelper->filterDates(array($option['info_buyRequest']['start_date']), true);
                $startDate = $startDateTempAr[0];
            }
        }

        if (isset($option['info_buyRequest']['end_date'])) {
            $endDate = ((array_key_exists('country_dates_normalized', $option['info_buyRequest']) && $option['info_buyRequest']['country_dates_normalized']) || preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/', $option['info_buyRequest']['end_date'])) ? $option['info_buyRequest']['end_date'] : false;
            if (!$endDate) {
                $endDateTempAr = $ppHelper->filterDates(array($option['info_buyRequest']['end_date']), true);
                $endDate = $endDateTempAr[0];
            }
        }

        if ($startDate) {
            $result .= '<br />' . $this->__('Start Date: ') . date(ITwebexperts_Rentalbundles_Model_Observer::DATETIME_FORMAT, strtotime($startDate));
        }

        if ($endDate) {
            $result .= '<br />' . $this->__('End Date: ') . date(ITwebexperts_Rentalbundles_Model_Observer::DATETIME_FORMAT, strtotime($endDate));
        }
        return $result;
    }

    /**
     * @param Varien_Object $item
     * @return bool
     */
    protected function _checkValidProduct(Varien_Object $item)
    {
        $productId = $item->getProductId();
        if (!$productId) {
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            return;
        }

        return ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $product->getRentalbundlesType();
    }
}