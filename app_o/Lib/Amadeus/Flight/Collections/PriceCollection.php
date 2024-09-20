<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Amadeus\Flight\Collections;


use Illuminate\Support\Collection;

class PriceCollection extends Collection
{

    protected $items =
        [
            'fare_amount' => '',
            'fare_currency' => '',
            'tax_amount' => '',
            'tax_currency' => '',
            'equiv_amount' => '',
            'equiv_currency' => '',
            'total_amount' => '',
            'total_currency' => '',
            'rate' => '',
            'emd_flag'=>false,
            'remaining_amount'=>null,
            'own_cc'=>false,
        ];

    public function __construct()
    {
        parent::__construct($this->items);
    }
}