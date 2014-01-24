<?php
$installer = $this;
$installer->startSetup();

$groupLabel = 'Rental Bundles';

Mage::getResourceModel('catalog/setup', 'catalog_setup')->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, 'rentalbundles_is_country',
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
        'position' => 1,
    )
);

$entityTypeId = $installer->getEntityTypeId('catalog_product');
$attributeId  = $installer->getAttributeId('catalog_product', 'rentalbundles_is_country');


$attributeSets = $installer->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);

foreach ($attributeSets as $attributeSet) {
    $setId = $attributeSet['attribute_set_id'];
    $installer->addAttributeGroup($entityTypeId, $setId, $groupLabel);
    $groupId = $installer->getAttributeGroupId($entityTypeId, $setId, $groupLabel);
    $installer->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId);
}

$installer->endSetup();