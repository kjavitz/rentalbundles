<?php
class ITwebexperts_Rentalbundles_Model_System_Config_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const TYPE_COUNTRY = 1;
    const TYPE_SIM = 2;

    /**
     * Returns options array.
     *
     * @return array
     */
    static public function getOptionArray(){
        return array(
            self::TYPE_COUNTRY    => Mage::helper('rentalbundles')->__('Country'),
            self::TYPE_SIM   => Mage::helper('rentalbundles')->__('SIM')
        );
    }

    /**
     * Returns all options
     *
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }

    /**
     * Returns option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('rentalbundles')->__('-- Not Set --')
        );

        $options[] = array(
            'value' => self::TYPE_COUNTRY,
            'label' => Mage::helper('rentalbundles')->__('Country')
        );

        $options[] = array(
            'value' => self::TYPE_SIM,
            'label' => Mage::helper('rentalbundles')->__('SIM')
        );

        return $options;
    }
}