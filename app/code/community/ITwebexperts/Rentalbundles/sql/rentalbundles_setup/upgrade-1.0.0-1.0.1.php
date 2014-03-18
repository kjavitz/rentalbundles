<?php
$_installer = new Mage_Core_Model_Resource_Setup('core_setup');
$_installer->startSetup();

$_installer->getConnection()
    ->addColumn($_installer->getTable('bundle/option'), 'customer_visibility', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default' => '1',
        'comment' => 'Customer Option Visibility'
    ));

$_installer->endSetup();