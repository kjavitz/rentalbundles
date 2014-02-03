<?php
class ITwebexperts_Rentalbundles_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PATH_MOBILE_KIT_SKU = 'rentalbundles/general/mobile_kit_sku';

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

    /**
     * Initialises the bundle product.
     *
     * @param mixed $productId
     * @return Mage_Core_Model_Product|null
     */
    public function initBundle($productId)
    {
        if (!$productId) {
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE != $product->getTypeId()) {
            return;
        }

        return $product;
    }

    /**
     * Returns bundle option by rental bundle type.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $type
     * @return Mage_Bundle_Model_Option|null
     */
    public function getOptionBySelectionType(Mage_Catalog_Model_Product $product, $type)
    {
        if (!$type) {
            return;
        }

        $options = $this->getOptionsCollection($product);
        if (!$options) {
            return;
        }

        foreach ($options as $option) {
            foreach ($option->getSelections() as $selection) {
                $selection = Mage::getModel('catalog/product')->load($selection->getId());
                if ($type == $selection->getRentalbundlesType()) {
                    return $option;
                }
            }
        }
    }
}