<?php
class ITwebexperts_Rentalbundles_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Checkbox extends ITwebexperts_Payperrentals_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Checkbox
{

    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        if (!$this->_getModuleHelper()->isKitProduct($this->getProduct())) {
            return parent::_construct();
        }

        $this->setTemplate('rentalbundles/catalog/product/view/type/bundle/option/checkbox.phtml');
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