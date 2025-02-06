<?php

namespace App\Objects;

class Seller
{

    public $registration_name;
    public $city;
    public $country;
    public $tax_number;
    public $postal_number;


    public function __construct(

        string $registration_name,
        string $tax_number = null,
        string $city = null,
        string $country = 'SA',
        string $postal_number = null,
    ) {

        $this->registration_name        = $registration_name;
        $this->tax_number               = $tax_number;
        $this->city                     = $city;
        $this->country                  = $country;
        $this->postal_number            = $postal_number;
    }
}
