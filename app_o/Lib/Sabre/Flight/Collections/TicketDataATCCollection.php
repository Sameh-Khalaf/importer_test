<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Sabre\Flight\Collections;


use Illuminate\Support\Collection;

class TicketDataATCCollection extends Collection
{

    protected $items =
        [
            'pnr_id' => '',
            'ticketdata_id'=>'',
            'old_base_fare_currency' => '',
            'old_base_fare' => '0.0',

            'new_base_fare_currency'=>'',
            'new_base_fare' => '0.0',

            'base_fare_balance_currency'=>'',
            'base_fare_balance' =>'0.0',

            'old_tax_currency'=>'',
            'old_tax'=>'0.0',

            'new_tax_currency'=>'',
            'new_tax'=>'0.0',

            'tax_balance_currency'=>'',
            'tax_balance'=>'0.0',

            'ticket_difference_currency'=>'',
            'ticket_difference'=>'0.0',


            'tst_collection_currency'=>'',
            'tst_collection'=>'0.0',

            'penalty_currency'=>'',
            'penalty'=>'0.0',

            'total_additional_collection_currency'=>'',
            'total_additional_collection'=>'0.0',

            'residual_value_currency'=>'',
            'residual_value'=>'0.0',

            'grand_total_currency'=>'',
            'grand_total'=>'0.0',

        ];

    public function __construct()
    {
        parent::__construct($this->items);
    }
}