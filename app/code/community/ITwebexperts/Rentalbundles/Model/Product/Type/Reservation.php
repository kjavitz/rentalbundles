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

        /**
         * Change end date for correct price calculation
         */
        /*if (!Mage::helper('payperrentals')->useTimes($product->getId()) && $buyRequest->getEndDate()) {
            $buyRequest->setEndDate(date('Y-m-d', strtotime($buyRequest->getEndDate())) . ' 23:59:59');
        }*/

        // We want to modify booking dates for countries
        // according to start/end dates for each country on FE
        // So we need to pass modified $buyRequest to the parent method in such case.
        // This method only modifies $buyRequest for countries products.
        $newBuyRequest = $this->processCountry($buyRequest, $product);
        if ($newBuyRequest) {
            $product->addCustomOption('info_buyRequest', serialize($newBuyRequest->getData()));
        } else {
            $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));
        }
        return parent::prepareForCartAdvanced($newBuyRequest ? $newBuyRequest : $buyRequest, $product, $processMode);
    }

    /**
     * Processes country reservation product.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Object|null
     */
    public function processCountry(Varien_Object $buyRequest, Mage_Catalog_Model_Product $product)
    {
        if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY != $product->getRentalbundlesType()) {
            return null;
        }

        $countryStartDates = $buyRequest->getData(self::COUNTRY_START_DATE);
        if (empty($countryStartDates) || !is_array($countryStartDates)) {
            return null;
        }
        $ppHelper = Mage::helper('payperrentals');
        foreach ($countryStartDates as $key => $startDate) {
            if (empty($startDate)) continue;
            $normalizedDate = $ppHelper->filterDatesOnly(array($startDate), true);
            $countryStartDates[$key] = $normalizedDate[0];
        }
        $buyRequest->setData('country_dates_normalized', true);

        $productId = ($buyRequest->getProduct()) ? $buyRequest->getProduct() : $buyRequest->getConfigurate();
        $bundle = $this->_getModuleHelper()->initBundle($productId);
        if (!$bundle) {
            return null;
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
            return null;
        }

        $bundleOptions = $buyRequest->getData(self::BUNDLE_OPTIONS_FIELD);
        if (empty($bundleOptions) || !is_array($bundleOptions)) {
            return null;
        }

        if (!isset($bundleOptions[$countryOption->getId()]) || !is_array($bundleOptions[$countryOption->getId()])
            || !count($bundleOptions[$countryOption->getId()])
        ) {
            return null;
        }

        $key = array_search($productSelection->getSelectionId(), $bundleOptions[$countryOption->getId()]);
        if (false === $key) {
            return null;
        }

        if (!isset($countryStartDates[$key])) {
            return null;
        }

        $startDate = $countryStartDates[$key];
        $endDate = $buyRequest->getData('end_date');
        if (isset($countryStartDates[$key + 1]) && $countryStartDates[$key + 1]) {
            $endDate = $countryStartDates[$key + 1];
        }

        if (!$startDate || !$endDate) {
            return null;
        }

        // I don't want to modify $buyRequest
        // because it may be used somewhere else
        // It's better to clone it
        // and modify the cloned object.
        $newBuyRequest = clone $buyRequest;
        $newBuyRequest
            ->setData(self::START_DATE_OPTION, $startDate)
            ->setData('read_start_date', $startDate)
            ->setData(self::END_DATE_OPTION, $endDate)
            ->setData('read_end_date', $endDate);

        return $newBuyRequest;
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