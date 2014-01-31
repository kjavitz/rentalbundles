<?php
$installer = $this;
$installer->startSetup();

$groupLabel = 'Rental Bundles';
$type = 'rentalbundles_type';
$simCountries = 'rentalbundles_country_sims';
$priority = 'rentalbundles_priority';

Mage::getResourceModel('catalog/setup', 'catalog_setup')->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $type,
    array(
        'group' => $groupLabel,
        'label' => 'Type',
        'type' => 'int',
        'input' => 'select',
        'source' => 'rentalbundles/system_config_source_type',
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
    Mage_Catalog_Model_Product::ENTITY, $simCountries,
    array(
        'backend' => 'eav/entity_attribute_backend_array',
        'source' => 'rentalbundles/system_config_source_sims',
        'group' => $groupLabel,
        'label' => 'Assign to SIMs',
        'input' => 'multiselect',
        'type' => 'varchar',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => 0,
        'apply_to' => 'reservation',
        'visible_on_front' => false,
        'position' => 20,
    ));

Mage::getResourceModel('catalog/setup', 'catalog_setup')->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, $priority,
    array(
        'backend' => 'eav/entity_attribute_backend_array',
        'source' => 'rentalbundles/system_config_source_countries',
        'group' => $groupLabel,
        'label' => 'SIM Priority',
        'input' => 'text',
        'type' => 'int',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => 0,
        'apply_to' => 'reservation',
        'visible_on_front' => false,
        'position' => 20,
    ));

$entityTypeId = $installer->getEntityTypeId('catalog_product');
$typeAttributeId = $installer->getAttributeId('catalog_product', $type);
$simCountriesAttributeId = $installer->getAttributeId('catalog_product', $simCountries);
$priorityAttributeId = $installer->getAttributeId('catalog_product', $priority);

$attributeSets = $installer->_conn->fetchAll('select * from ' . $this->getTable('eav/attribute_set') . ' where entity_type_id=?', $entityTypeId);

foreach ($attributeSets as $attributeSet) {
    $setId = $attributeSet['attribute_set_id'];
    $installer->addAttributeGroup($entityTypeId, $setId, $groupLabel);
    $groupId = $installer->getAttributeGroupId($entityTypeId, $setId, $groupLabel);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $typeAttributeId);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $simCountriesAttributeId);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $priorityAttributeId);
}

$installer->endSetup();