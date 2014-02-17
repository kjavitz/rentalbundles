<?php
class ITwebexperts_Rentalbundles_Helper_Price extends ITwebexperts_Payperrentals_Helper_Price
{
    public static function calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup)
    {
        $a = 5;
        return parent::calculatePrice($product, $startingDate, $endingDate, $qty, $customerGroup);
    }
}