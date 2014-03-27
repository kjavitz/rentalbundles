<?php

class ITwebexperts_Rentalbundles_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi extends ITwebexperts_Payperrentals_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi
{
    /**
     * This method hides SIMs bundle options
     * on frontend (product view page).
     *
     * @return string
     */
    protected function _toHtml()
    {
        $currentOption = $this->getOption();
        if (!$currentOption->getCustomerVisibility()) {
            return '';
        }
        return parent::_toHtml();
    }


    /**
     * Returns default module helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    public function _getModuleHelper()
    {
        return $this->helper('rentalbundles');
    }
}