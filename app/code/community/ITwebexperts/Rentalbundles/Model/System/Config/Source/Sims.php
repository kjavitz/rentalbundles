<?php

class ITwebexperts_Rentalbundles_Model_System_Config_Source_Sims extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected static $_collection = null;

    /**
     * Returns product collection.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    protected static function _getCollection()
    {
        if (is_null(self::$_collection)) {
            self::$_collection = Mage::getModel('catalog/product')->getCollection();
            self::$_collection
                ->addAttributeToFilter('rentalbundles_type', array('eq' => ITwebexperts_Rentalbundles_Model_System_Config_Source_Type::TYPE_SIM))
                ->addAttributeToSelect('name') //joining the name attribute
                ->setOrder('name', Mage_Eav_Model_Entity_Collection_Abstract::SORT_ORDER_ASC);
        }

        return self::$_collection;
    }

    /**
     * Returns options array.
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $array = array();

        foreach (self::_getCollection() as $country) {
            $array[$country->getId()] = $country->getName();
        }

        return $array;
    }

    /**
     * Returns all options
     *
     * @return array
     */
    static public function getAllOption()
    {
        return self::getOptionArray();
    }

    /**
     * Returns option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array();
        foreach (self::_getCollection() as $country) {
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
            );
        }

        return $options;
    }
}

