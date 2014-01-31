<?php
class ITwebexperts_Rentalbundles_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PATH_MOBILE_KIT_SKU = 'rentalbundles/general/mobile_kit_sku';

    /**
     * Checks if given product is kit.
     *
     * @param Varien_Object $product
     * @return bool
     */
    public function isKitProduct(Varien_Object $product)
    {
        return (false !== stripos($this->getMobileKitSku(), $product->getSku()))
            && (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $product->getTypeId());
    }

    /**
     * Returns SKU of a mobile kit product
     *
     * @return mixed
     */
    public function getMobileKitSku()
    {
        return Mage::getStoreConfig(self::PATH_MOBILE_KIT_SKU);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    public function getOptionsCollection(Mage_Catalog_Model_Product $product)
    {
        $options = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        $selections = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );

        $options->appendSelections($selections);
        return $options;
    }
}