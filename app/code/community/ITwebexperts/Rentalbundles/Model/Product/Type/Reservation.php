<?php
class ITwebexperts_Rentalbundles_Model_Product_Type_Reservation extends ITwebexperts_Payperrentals_Model_Product_Type_Reservation
{
    const COUNTRY_START_DATE = 'country_start_date';
    const COUNTRY_END_DATE = 'country_end_date';

    const BUNDLE_OPTIONS_FIELD = 'bundle_option';


    /**
     * This method adds custom options to sim card products.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param null|string $processMode
     * @return array|string
     */
    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product = null, $processMode = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }
        $this->_processCountry($buyRequest, $product);

        return parent::prepareForCartAdvanced($buyRequest, $product, $processMode);
    }

    /**
     * Processes country reservation product.
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _processCountry(Varien_Object $buyRequest, Mage_Catalog_Model_Product $product)
    {
        if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY != $product->getRentalbundlesType()) {
            return;
        }

        $countryStartDates = $buyRequest->getData(self::COUNTRY_START_DATE);
        if (empty($countryStartDates) || !is_array($countryStartDates)) {
            return;
        }

        $bundle = $this->_initBundle($buyRequest->getProduct());
        if (!$bundle) {
            return;
        }

        $countryOption = $productSelection = null;
        $options = $this->_getModuleHelper()->getOptionsCollection($bundle);

        foreach ($options as $option) {
            if ($countryOption) {
                break;
            }

            foreach ($option->getSelections() as $selection) {
                if ($selection->getId() == $product->getId()) {
                    $countryOption = $option;
                    $productSelection = $selection;
                    break;
                }
            }
        }

        if (!$countryOption) {
            return;
        }

        $bundleOptions = $buyRequest->getData(self::BUNDLE_OPTIONS_FIELD);
        if (empty($bundleOptions) || !is_array($bundleOptions)) {
            return;
        }

        if (!isset($bundleOptions[$countryOption->getId()]) || !is_array($bundleOptions[$countryOption->getId()])
            || !count($bundleOptions[$countryOption->getId()])
        ) {
            return;
        }

        $key = array_search($productSelection->getSelectionId(), $bundleOptions[$countryOption->getId()]);
        if (false === $key) {
            return;
        }

        if (!isset($countryStartDates[$key])) {
            return;
        }

        $startDate = $countryStartDates[$key];
        $endDate = $buyRequest->getData('end_date');
        if (isset($countryStartDates[$key + 1]) && $countryStartDates[$key + 1]) {
            $endDate = $countryStartDates[$key + 1];
        }

        if ($startDate && $endDate) {
            $product
                ->addCustomOption(self::COUNTRY_START_DATE, $startDate, $product)
                ->addCustomOption(self::COUNTRY_END_DATE, $endDate, $product);
        }
    }

    /**
     * Initialises the bundle product.
     *
     * @param mixed $productId
     * @return Mage_Core_Model_Product|null
     */
    protected function _initBundle($productId)
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
     * Returns default module helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    public function _getModuleHelper()
    {
        return Mage::helper('rentalbundles');
    }
}