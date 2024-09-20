<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Sabre\Flight\Collections;


use Illuminate\Support\Collection;

class RefundCollection extends Collection
{

    protected $items =
        [
            'pnr_id' => '',
            'ticket_number'=>'',
            'domestic_flag' => '',
            'currency' => '',
            'fare_paid' => '0.0',
            'fare_used' => '0.0',
            'fare_refund' => '0.0',
            'net_refund' => '0.0',
            'cancel_fee' => '0.0',
            'cancel_fee_commission' => '0.0',
            'misc_fee'=>'0.0',
            'tax_code'=>'',
            'tax_refund'=>'0.0',
            'refund_total'=>'0.0',
            'refund_date'=>'',
            'dep_date_first_seg'=>'',
            'source'=>'',
            'fop'=>'',
            'orig_pnr'=>''
        ];

    public function __construct()
    {
        parent::__construct($this->items);
    }
}