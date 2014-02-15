<?php

class ITwebexperts_Rentalbundles_Block_Catalog_Product_View_Rental extends ITwebexperts_Payperrentals_Block_Catalog_Product_View_Rental
{
    protected function _toHtml()
    {
        $product = $this->getProduct();
        $html = parent::_toHtml();

        if ($this->_canReplaceInputs($product)) {
            $html = str_replace($this->_getPayperrentalsHelper()->__('Start Date'), $this->_getHelper()->__('Requested Delivery Date'), $html);
            $html = str_replace($this->_getPayperrentalsHelper()->__('End Date'), $this->_getHelper()->__('Trip Return Date'), $html);
            $html = str_replace('name="read_start_date"', 'name="read_start_date" style="width:80px;"', $html);
            $html = str_replace('name="read_end_date"', 'name="read_end_date" style="width:80px;"', $html);
        }
        return $html;
    }

    /**
     * Returns module's helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('rentalbundles');
    }

    /**
     * Returns module's helper.
     *
     * @return ITwebexperts_Payperrentals_Helper_Data
     */
    protected function _getPayperrentalsHelper()
    {
        return Mage::helper('payperrentals');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _canReplaceInputs(Mage_Catalog_Model_Product $product)
    {
        return (bool)$this->_getHelper()->getOptionBySelectionType($product, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY);
    }
}