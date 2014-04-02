<?php

class ITwebexperts_Rentalbundles_Block_Adminhtml_Bundle_Catalog_Product_Composite_Fieldset_Options_Type_Multi extends ITwebexperts_Payperrentals_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('bundle/product/composite/fieldset/options/type/multi.phtml');
    }

    /**
     * Sets custom template for countries.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = parent::getProduct();
        $currentOption = $this->getOption();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $option = $this->_getModuleHelper()->getOptionBySelectionType($product, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM);
            if ($option && $currentOption && $currentOption->getId() == $option->getId()) {
                $this->setTemplate('rentalbundles/bundle/product/composite/fieldset/options/type/multi.phtml');
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