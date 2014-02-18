<?php

class ITwebexperts_Rentalbundles_Model_Observer extends ITwebexperts_Payperrentals_Model_Observer
{
    const DATETIME_FORMAT = 'm/d/Y';

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

        $countryOption = $this->getHelper()->getOptionBySelectionType($bundle, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY);

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
        $simOption = $this->getHelper()->getOptionBySelectionType($bundle, ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM);

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

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onBundleOptionPriceCalculation(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $selection = $observer->getEvent()->getSelection();
        if (!$item || !$selection) {
            return;
        }

        $product = Mage::getModel('catalog/product')->load($selection->getProductId());
        if ($product->getId() && (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY != $product->getRentalbundlesType())) {
            return;
        }

        $data = $observer->getEvent()->getData('data');
        if (!$item || !$selection || !$data) {
            return;
        }

        if (!($item instanceof Mage_Sales_Model_Quote_Item)) {
            return;
        }

        $children = $item->getChildren();
        if (!is_array($children) || !count($children)) {
            return;
        }

        $currentItem = null;
        foreach ($children as $item) {
            if ($item->getProductId() == $product->getId()) {
                $currentItem = $item;
                break;
            }
        }

        if (!$currentItem) {
            return;
        }

        if (!$currentItem->getStartTurnoverBefore() || !$currentItem->getEndTurnoverAfter()) {
            return;
        }

        $data
            ->setStartDate($currentItem->getStartTurnoverBefore())
            ->setEndDate($currentItem->getEndTurnoverAfter());
    }

    /**
     * Prevents modification of $buyRequest for country products.
     * Because we use modified $buyRequest for such products.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function prepareBuyRequestCartAdvanced(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product && (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY == $product->getRentalbundlesType())) {
            return;
        }

        return parent::prepareBuyRequestCartAdvanced($observer);
    }

    /**
     * Custom renderer for countries in checkout cart.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCartRender(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getData();
        $item = $observer->getEvent()->getItem();
        $result = $observer->getEvent()->getResult();
        if (!($item instanceof Mage_Sales_Model_Quote_Item)) {
            return;
        }

        $children = $item->getChildren();
        if (!is_array($children) || !count($children)) {
            return;
        }

        $options = $result->getResult();

        foreach ($children as $item) {
            $product = $item->getProduct();
            if (ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_COUNTRY != $product->getRentalbundlesType()) {
                continue;
            }

            $startDate = date(self::DATETIME_FORMAT, strtotime($item->getStartTurnoverBefore()));
            $endDate = date(self::DATETIME_FORMAT, strtotime($item->getEndTurnoverAfter()));

            $options[] = array(
                'label' => $item->getName(),
                'value' => $this->getHelper()->__('Start Date: ') . $startDate . '<br />' . $this->getHelper()->__('End Date: ') . $endDate,
                'type' => 'reservation',
            );
        }

        $result->setResult($options);
    }
}