<?php

class ITwebexperts_Rentalbundles_Block_Checkout_Cart_Item_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Checkout_Cart_Item_Renderer_Bundle
{
    /**
     * Removes SIM cards from bundle options list
     * Allows to hide SIM cards on checkout cart page.
     *
     * @return array
     */
    public function getOptionList()
    {
        $options = parent::getOptionList();
        $simCardsTitle = $this->_getSimCardsTitle();
        if ($simCardsTitle && is_array($options) && count($options)) {
            for ($i = 0; $i < count($options); $i++) {
                if (isset($options[$i]['label']) && (false !== stripos($options[$i]['label'], $simCardsTitle))) {
                    // We don't want to display SIM cards
                    // on FE so we should remove them
                    // from array
                    unset($options[$i]);
                    break;
                }
            }
        }

        return $options;
    }

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