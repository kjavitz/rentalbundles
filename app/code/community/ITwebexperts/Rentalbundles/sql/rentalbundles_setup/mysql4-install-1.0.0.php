<?php
$installer = $this;
$installer->startSetup();

$groupLabel = 'Rental Bundles';
$isCountry = 'rentalbundles_is_country';
$isSIM = 'rentalbundles_is_sim';

Mage::getResourceModel('catalog/setup', 'catalog_setup')->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $isCountry,
    array(
        'group' => $groupLabel,
        'label' => 'Consider as Country',
        'type' => 'int',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => 0,
        'apply_to' => 'reservation',
        'visible_on_front' => false,
        'position' => 10,
    )
);

Mage::getResourceModel('catalog/setup', 'catalog_setup')->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $isSIM,
    array(
        'group' => $groupLabel,
        'label' => 'Consider as SIM',
        'type' => 'int',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => 0,
        'apply_to' => 'reservation',
        'visible_on_front' => false,
        'position' => 20,
    )
);

$entityTypeId = $installer->getEntityTypeId('catalog_product');
$countryAttributeId  = $installer->getAttributeId('catalog_product', $isCountry);
$simAttributeId = $installer->getAttributeId('catalog_product', $isSIM);


$attributeSets = $installer->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);

foreach ($attributeSets as $attributeSet) {
    $setId = $attributeSet['attribute_set_id'];
    $installer->addAttributeGroup($entityTypeId, $setId, $groupLabel);
    $groupId = $installer->getAttributeGroupId($entityTypeId, $setId, $groupLabel);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $countryAttributeId);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $simAttributeId);
}

$installer->endSetup();