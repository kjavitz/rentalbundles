<?php
class ITwebexperts_Rentalbundles_Model_System_Config_Source_Countries
{
    public function toOptionArray()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToFilter('rentalbundles_is_country', array('gt' => 0))
            ->addAttributeToSelect('name') //joining the name attribute
            ->setOrder('name', Mage_Eav_Model_Entity_Collection_Abstract::SORT_ORDER_ASC);

        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('rentalbundles')->__('-- Please Select --')
        );

        foreach ($collection as $country) {
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
            );
        }
        return $options;
    }
}