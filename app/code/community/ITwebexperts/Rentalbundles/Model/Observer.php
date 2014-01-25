<?php
class ITwebexperts_Rentalbundles_Model_Observer
{
    /**
     * Handles reservation of bundle product.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onProductCardAddAction(Varien_Event_Observer $observer)
    {
        return;
        $request = Mage::app()->getRequest();
        $params = $request->getParams();
        if (isset($params['start_date']) && is_array($params['start_date'])) {
            foreach ($params['start_date'] as $product => $date) {
                $this->getHelper()->getSession()->addStartDate($product, $date);
            }
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
}