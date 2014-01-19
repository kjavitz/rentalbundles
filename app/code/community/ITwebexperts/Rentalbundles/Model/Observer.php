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
        $request = Mage::app()->getRequest();
        $params = $request->getParams();
        if (isset($params['start_date']) && is_array($params) && isset($params['start_date'][0])) {
            // hardcoded for now. Will add better processing later
            $request
                ->setParam('start_date', '01/06/2014')
                ->setParam('end_date', '01/07/2014');
        }
    }
}