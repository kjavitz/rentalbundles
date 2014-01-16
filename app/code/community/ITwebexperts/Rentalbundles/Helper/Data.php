<?php
class ITwebexperts_Rentalbundles_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Checks if given product is kit.
     *
     * @param Varien_Object $product
     * @return bool
     */
    public function isKitProduct(Varien_Object $product)
    {
        return (false !== stripos('mobile-kit', $product->getSku()));
    }
}