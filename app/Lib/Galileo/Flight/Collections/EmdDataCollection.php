<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Galileo\Flight\Collections;



use Illuminate\Support\Collection;

class EmdDataCollection extends Collection
{

    protected $items =
        [
            [
                'tsm_identifier' => '',
                'pnr_id' => '',
                'ticketdata_id' => '',
                'airline_code' => '',
                'airline_number' => '',
                'airline_name' => '',
                'creation_date' => '',
                'marketing_airline_code' => '',
                'operating_airline_code' => '',
                'carrier_fee_owner' => '',
                'origin_city' => '',
                'destination_city' => '',
                'to_carrier' => '',
                'at_location' => '',
                'emd_type' => '',
                'reason_issuance_code' => '',
                'reason_issuance_code_desc' => '',
                'reason_issuance_sub_code' => '',
                'reason_issuance_sub_code_desc' => '',
                'remarks' => '',
                'service_remarks' => '',
                'not_valid_before_date' => '',
                'not_valid_after_date' => '',
                'coupon_value' => '',
                'issue_identifier' => '',
                'fare_currency' => '',
                'fare_amount' => '',
                'inclusive_tax_included' => '',
                'equiv_currency' => '',
                'equiv_amount' => '',
                'refund_currency' => '',
                'refund_amount' => '',
                'total_currency' => '',
                'total_amount' => '',
            ]
        ];

    public function __construct()
    {
        parent::__construct($this->items);
    }


    public function putByIndex($key, $value, $index)
    {
        if ($this->_offsetExists($key, $index)) {
            $this->_offsetSet($key, $value, $index);
            return $this;
        }
        throw new \Exception('Key {' . $key . '} does not exist in the ' . get_class($this) . ' ' . __FILE__);
    }

    private function _offsetExists($key, $index)
    {
        if (!isset($this->items[$index])) {
            $this->items[$index] = $this->_free($this->items[0]);
        }
        return array_key_exists($key, $this->items[$index]);
    }

    private function _free($array)
    {
        if(is_array($array))
        {
            foreach ($array as $k=>$single)
            {
                $array[$k] = null;
            }
        }
        return $array;
    }

    private function _offsetSet($key, $value, $index)
    {
        $this->items[$index][$key] = $value;
    }

    public function updateValue($key,$value)
    {
        foreach ($this->items as &$singleItem)
        {
            if(array_key_exists($key,$singleItem)){
                $singleItem[$key] = $value;
            }
        }
    }
}

