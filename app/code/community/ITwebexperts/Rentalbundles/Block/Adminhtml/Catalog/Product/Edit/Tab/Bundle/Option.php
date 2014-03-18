<?php

class ITwebexperts_Rentalbundles_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option
{
    /**
     * Bundle option renderer class constructor
     *
     * Sets block template and necessary data
     */
    public function __construct()
    {
        $this->setTemplate('rentalbundles/bundle/product/edit/bundle/option.phtml');
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * Get Customer visibility options
     */
    public function getCustomerReviewVisibilityHtml()
    {
        $_select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId() . '_{{index}}_customer_visibility',
                'class' => 'select'
            ))
            ->setName($this->getFieldName() . '[{{index}}][customer_visibility]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $_select->getHtml();
    }
}