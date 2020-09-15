<?php

class Billing
{
    private $address;
    private $address_type;
    private $country;

    /**
     * Billing constructor.
     * @param $address
     * @param $address_type
     * @param $country
     */
    public function __construct($address, $address_type, $country)
    {
        $this->address = $address;
        $this->address_type = $address_type;
        $this->country = $country;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getAddressType()
    {
        return $this->address_type;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public static function  createFromPrestaBillingData(\Address $psBillingAddress, $country)
    {
        return new static(
            implode("\n", array_filter(array(
                $psBillingAddress->company,
                $psBillingAddress->address1,
                $psBillingAddress->address2,
                $psBillingAddress->postcode . " " . $psBillingAddress->city,
            ))),
            'billing',
           $country->iso_code
        );
    }
}