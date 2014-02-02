<?php
class ITwebexperts_Rentalbundles_Model_Observer
{
    /**
     * Handles reservation of bundle product.
     * Adds sims to the product buy request.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onProductCardAddAction(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();

        $bundle = $this->getHelper()->initBundle($request->getParam('product'));
        if (!$bundle) {
            return;
        }

        $countryOption = null;
        $options = $this->getHelper()->getOptionsCollection($bundle);

        foreach ($options as $option) {
            if ($countryOption) {
                break;
            }

            foreach ($option->getSelections() as $selection) {
                $selection = Mage::getModel('catalog/product')->load($selection->getId());
                if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $selection->getRentalbundlesType()) {
                    $countryOption = $option;
                    break;
                }
            }
        }

        if (!$countryOption) {
            return;
        }

        $bundleOptions = $request->getParam(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::BUNDLE_OPTIONS_FIELD);
        if (empty($bundleOptions) || !is_array($bundleOptions)) {
            return;
        }

        if (!isset($bundleOptions[$countryOption->getId()])
            || !is_array($bundleOptions[$countryOption->getId()]) || !count($bundleOptions[$countryOption->getId()])
        ) {
            return;
        }

        $selectedCountries = array_filter($bundleOptions[$countryOption->getId()]);
        if (empty($selectedCountries)) {
            return;
        }

        $SIMs = array();
        $finalSims = array();

        foreach ($countryOption->getSelections() as $country) {
            if (!in_array($country->getSelectionId(), $selectedCountries)) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($country->getId());
            $sims = $product->getRentalbundlesCountrySims();

            $sims = explode(',', $sims);
            if (!empty($sims)) {
                $SIMs[$country->getId()] = array();
                foreach ($sims as $simId) {
                    $sim = Mage::getModel('catalog/product')->load($simId);
                    if (!$sim->getId()) {
                        continue;
                    }

                    $SIMs[$country->getId()][$sim->getId()] = (int)$sim->getRentalbundlesPriority();
                }

                if ($SIMs[$country->getId()]) {
                    // Sorting SIMs by priority
                    arsort($SIMs[$country->getId()]);
                    $finalSims[] = key($SIMs[$country->getId()]);
                }
            }
        }

        $finalSims = array_unique($finalSims);

        if (empty($finalSims)) {
            return;
        }

        $simOption = null;
        $chosenSims = array();
        foreach ($options as $option) {
            if ($simOption) {
                break;
            }

            foreach ($option->getSelections() as $selection) {
                $selection = Mage::getModel('catalog/product')->load($selection->getId());
                if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM == $selection->getRentalbundlesType()) {
                    $simOption = $option;
                    break;
                }
            }
        }

        if (!$simOption) {
            return;
        }

        foreach ($finalSims as $sim) {
            foreach ($simOption->getSelections() as $simSelection) {
                if ($sim == $simSelection->getId()) {
                    $chosenSims[] = $simSelection->getSelectionId();
                }
            }
        }

        $chosenSims = array_unique($chosenSims);

        if (empty($chosenSims)) {
            return;
        }

        unset($bundleOptions[$simOption->getId()]);
        $bundleOptions[$simOption->getId()] = $chosenSims;
        $request->setParam(ITwebexperts_Rentalbundles_Model_Product_Type_Reservation::BUNDLE_OPTIONS_FIELD, $bundleOptions);

    }

    /**
     * Returns module's default helper.
     *
     * @return ITwebexperts_Rentalbundles_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('rentalbundles');
    }
}