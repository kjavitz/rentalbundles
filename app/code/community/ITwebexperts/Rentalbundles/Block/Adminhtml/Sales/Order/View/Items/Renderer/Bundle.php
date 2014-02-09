<?php

class ITwebexperts_Rentalbundles_Block_Adminhtml_Sales_Order_View_Items_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Adminhtml_Sales_Order_View_Items_Renderer_Bundle
{
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

        if (isset($option['info_buyRequest']['start_date'])) {
            $startDate = $option['info_buyRequest']['start_date'];
        }

        if (isset($option['info_buyRequest']['end_date'])) {
            $endDate = $option['info_buyRequest']['end_date'];
        }

        if ($endDate) {
            $result .= '<br />' . $this->__('Start Date: ') . $startDate;
        }

        if ($endDate) {
            $result .= '<br />' . $this->__('End Date: ') . $endDate;
        }
        return $result;
    }

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