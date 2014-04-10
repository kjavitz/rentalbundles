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
    public function preDispatchPriceAndAddActions(Varien_Event_Observer $observer)
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

        /** @var $countryOption Mage_Bundle_Model_Option */
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
                    $finalSims = array_merge($finalSims, array_keys($SIMs[$country->getId()]));
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


        /**
         * Filter dates
         * */
        $_useNonsequential = Mage::helper('payperrentals/config')->isNonSequentialSelect(Mage::app()->getStore()->getId());
        if (!$request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION)) {
            $request->setParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION, $request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION));
        }
        if (!$_useNonsequential) {
            if (!$request->getParam('is_filtered') && !ITwebexperts_Payperrentals_Helper_Date::isFilteredDate($request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION))) {
                $params = array('start_date' => $request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION), 'end_date' => $request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION));
                $params = ITwebexperts_Payperrentals_Helper_Data::filterDates($params, true);
                $startingDateFiltered = $params['start_date'];
                $endingDateFiltered = $params['end_date'];
                $request->setParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION, $startingDateFiltered);
                $request->setParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION, $endingDateFiltered);
                $request->setParam('is_filtered', true);
            }
        }
        if ($request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION) == $request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION)) {
            if (date('H:i:s', strtotime($request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION))) == '00:00:00') {
                $request->setParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION, date('Y-m-d', strtotime($request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION))) . ' 23:59:59');
            }
        }
        /**
         * Change end date for correct price calculation
         */
        if (!Mage::helper('payperrentals')->useTimes($bundle->getId()) && $request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION)) {
            $request->setParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION, date('Y-m-d', strtotime($request->getParam(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION))) . ' 23:59:59');
        }
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

    /**
     * Change admin edit bundle option block
     *
     * @param Varien_Event_Observer $_observer
     */
    public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $_observer)
    {
        $_block = $_observer->getEvent()->getBlock();
        if ($_block->getType() == 'bundle/adminhtml_catalog_product_edit_tab_bundle') {
            /** @var $_block Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle */
            $_block->unsetChild('options_box');
            $_block->setChild('options_box',
                $_block->getLayout()->createBlock('rentalbundles/adminhtml_catalog_product_edit_tab_bundle_option',
                    'adminhtml.catalog.product.edit.tab.bundle.option')
            );
        }
    }
}