<?php

class ITwebexperts_Rentalbundles_Block_Checkout_Cart_Item_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Checkout_Cart_Item_Renderer_Bundle
{
    /**
     * Returns module's helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    public function getModuleHelper()
    {
        return Mage::helper('rentalbundles');
    }

    /**
     * Returns title for SIM card bundle option.
     *
     * @return string|null
     */
    protected function _getSimCardsTitle()
    {
        $product = $this->getProduct();
        if (!$product) {
            return;
        }

        $option = $this->getModuleHelper()->getOptionBySelectionType($product, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM);
        if (!$option) {
            return;
        }

        return $option->getDefaultTitle();
    }
}