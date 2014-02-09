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
        $product = parent::getProduct();
        $currentOption = $this->getOption();

        if ($product instanceof Mage_Catalog_Model_Product) {
            $option = $this->_getModuleHelper()->getOptionBySelectionType($product, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM);
            if ($option && $currentOption && ($currentOption->getId() == $option->getId())) {
                return '';
            }
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