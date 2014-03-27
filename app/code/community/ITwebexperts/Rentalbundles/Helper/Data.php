<?php

class ITwebexperts_Rentalbundles_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_optionsSelectionCache = array();

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
     * @return Mage_Catalog_Model_Product|null
     */
    public function initBundle($productId)
    {
        if (!$productId) {
            return null;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE != $product->getTypeId()) {
            return null;
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
            return null;
        }

        if (!array_key_exists($product->getId() . ':' . $type, $this->_optionsSelectionCache)) {
            $options = $this->getOptionsCollection($product);
            if (!$options) {
                $this->_optionsSelectionCache[$product->getId() . ':' . $type] = null;
                return $this->_optionsSelectionCache[$product->getId() . ':' . $type];
            }

            foreach ($options as $option) {
                foreach ($option->getSelections() as $selection) {
                    $selection = Mage::getModel('catalog/product')->load($selection->getId());
                    if ($type == $selection->getRentalbundlesType()) {
                        $this->_optionsSelectionCache[$product->getId() . ':' . $type] = $option;
                        return $this->_optionsSelectionCache[$product->getId() . ':' . $type];
                    }
                }
            }
        }

        return $this->_optionsSelectionCache[$product->getId() . ':' . $type];
    }
}