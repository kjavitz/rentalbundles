<?php

class ITwebexperts_Rentalbundles_Block_Adminhtml_Bundle_Catalog_Product_Composite_Fieldset_Options_Type_Checkbox extends ITwebexperts_Payperrentals_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Checkbox
{
    private $_simPrepareSelections = array();
    private $_simJsonConfig = array();

    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('bundle/product/composite/fieldset/options/type/checkbox.phtml');
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
            $option = $this->_getModuleHelper()->getOptionBySelectionType($product, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY);
            if ($option && $currentOption && $currentOption->getId() == $option->getId()) {
                $this->setTemplate('rentalbundles/bundle/product/composite/fieldset/options/type/checkbox.phtml');
            }
        }

        return parent::_toHtml();
    }

    /**
     * Returns default module helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    protected function _getModuleHelper()
    {
        return $this->helper('rentalbundles');
    }

    /**
     * @param $selections
     * @return string
     */
    public function getSimJsonConfig($selections, $bundleId)
    {
        if (!array_key_exists($bundleId, $this->_simJsonConfig)) {
            $bundle = $this->_getModuleHelper()->initBundle($bundleId);
            $simOption = $this->_getModuleHelper()->getOptionBySelectionType($bundle, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM);
            $simSelectionIds = $this->_prepareOption($simOption);

            $SIMs = array();
            $finalSims = array();

            foreach ($selections as $country) {
                $product = Mage::getModel('catalog/product')->load($country->getId());
                $sims = $product->getRentalbundlesCountrySims();

                $sims = explode(',', $sims);
                if (!empty($sims)) {
                    $SIMs[$country->getSelectionId()] = array();
                    foreach ($sims as $simId) {
                        $sim = Mage::getModel('catalog/product')->load($simId);
                        if (!$sim->getId() || array_search($simId, $simSelectionIds) === false) {
                            continue;
                        }
                        $simSelectionIdAr = array_keys($simSelectionIds, $simId);
                        $SIMs[$country->getSelectionId()][array_shift($simSelectionIdAr)] = (int)$sim->getRentalbundlesPriority();
                    }

                    if ($SIMs[$country->getSelectionId()]) {
                        // Sorting SIMs by priority
                        arsort($SIMs[$country->getSelectionId()]);
                        $finalSims[$country->getSelectionId()] = array_keys($SIMs[$country->getSelectionId()]);
                    }
                }
            }
            $this->_simJsonConfig[$bundleId] = Mage::helper('core')->jsonEncode($finalSims);
        }

        return $this->_simJsonConfig[$bundleId];
    }

    /**
     * Prepare sim option as product id - selection id array
     * @param $simOption
     * @return array
     */
    protected function _prepareOption($simOption)
    {
        if (!array_key_exists($simOption->getId(), $this->_simPrepareSelections)) {
            $prepareSelections = array();
            if (!$simOption->getSelections()) {
                $this->_simPrepareSelections[$simOption->getId()] = $prepareSelections;
                return $this->_simPrepareSelections[$simOption->getId()];
            }
            foreach ($simOption->getSelections() as $selection) {
                $prepareSelections[$selection->getSelectionId()] = $selection->getProductId();
            }
            $this->_simPrepareSelections[$simOption->getId()] = $prepareSelections;
        }

        return $this->_simPrepareSelections[$simOption->getId()];
    }
}