<?php

class ITwebexperts_Rentalbundles_Helper_Bundle_Catalog_Product_Configuration extends ITwebexperts_Payperrentals_Helper_Bundle_Catalog_Product_Configuration
{
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $_item
     * @return array
     */
    public function getBundleOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $_item)
    {
        $options = array();
        $product = $_item->getProduct();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $_item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : array();
        if ($bundleOptionsIds) {
            /**
             * @var Mage_Bundle_Model_Mysql4_Option_Collection
             */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $_item->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $product
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if (!$bundleOption->getCustomerVisibility()) continue;
                if ($bundleOption->getSelections()) {
                    $option = array(
                        'label' => $bundleOption->getTitle(),
                        'value' => array()
                    );

                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                        if ($qty) {
                            $val = $qty . ' x ' . $this->escapeHtml($bundleSelection->getName())
                                . ' ';

                            if ($bundleSelection->getTypeId() == 'reservation') {
                                if ($product->getBundlePricingtype() == ITwebexperts_Payperrentals_Model_Product_Bundlepricingtype::PRICING_BUNDLE_PERPRODUCT) {
                                    $customerGroup = ITwebexperts_Payperrentals_Helper_Data::getCustomerGroup();
                                    if (!is_object($product->getCustomOption(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION))) {
                                        $source = unserialize($product->getCustomOption('info_buyRequest')->getValue());
                                        if (isset($source[ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION])) {
                                            $start_date = $source[ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION];
                                            $end_date = $source[ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION];
                                        }
                                    } else {
                                        $start_date = $product->getCustomOption(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION)->getValue();
                                        $end_date = $product->getCustomOption(ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION)->getValue();
                                    }

                                    $data = new Varien_Object(array(
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                    ));

                                    Mage::dispatchEvent('payperrentals_bundle_option_calculate_price_before', array(
                                        'data' => $data,
                                        'item' => $_item,
                                        'selection' => $bundleSelection,
                                    ));

                                    extract($data->getData(), EXTR_OVERWRITE);

                                    $priceAmount = ITwebexperts_Payperrentals_Helper_Price::calculatePrice($bundleSelection, $start_date, $end_date, $qty, $customerGroup);
                                    $val .= Mage::helper('core')->currency($priceAmount);
                                } else {

                                }
                            } else {
                                $val .= Mage::helper('core')->currency($this->getSelectionFinalPrice($_item, $bundleSelection));
                            }

                            $option['value'][] = $val;
                        }
                    }

                    if ($option['value']) {
                        $options[] = $option;
                    }
                }
            }
        }

        return $options;
    }
}