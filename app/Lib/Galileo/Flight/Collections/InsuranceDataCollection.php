<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Galileo\Flight\Collections;


use Illuminate\Support\Collection;

class InsuranceDataCollection extends Collection
{

    protected $items =
        [
            [
                'idnetifier' => '',
                'pnr_id' => '',
                'name' => '',
                'beneficiary' => '',
                'no_ppl' => '0',

                'address' => '',
                'emergency' => '',

                'depdate' => '',
                'arrdate' => '',

                'trip' => '',
                'tripvalue' => '',

                'geozone' => '',
                'tocode' => '',

                'insurance_provider_code' => '',
                'insurance_provider_name' => '',

                'insurance_product_code' => '',
                'insurance_product_name' => '',


                'product_details' => '',
                'extension' => '',

                'subscription_date' => '',
                'subscribtion_time' => '',

                'deposit_date' => '',
                'departure_time' => '',

                'reduction_code' => '',
                'substitute' => '',

                'babysit' => '',
                'siid' => '',
                'policy_number' => '',
                'appraisal_number' => '',
                'net_premium_currency' => '',
                'net_premium_amount' => '',
                'commission_percentage' => '',
                'commission_amount' => '',
                'tax_codes' => '',
                'tax_amounts' => '',
                'total_currency' => '',
                'total_amount' => '',
                'participants_id'=>'',

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
            if(!isset($this->items[0]))
            {
                throw new \Exception('must be checked '.json_encode($this->items));
                return false;
            }
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
                $array[$k] = '';
            }
        }
        return $array;
    }


    private function _offsetSet($key, $value, $index)
    {
        $this->items[$index][$key] = $value;
    }

    private function _offsetGet($key,$index)
    {
        return $this->items[$index][$key];
    }

    public function removeEmptyValue()
    {
        foreach ($this->items as &$single)
        {
            foreach ($single as $k=>&$item)
            {
                if(empty($item)){
                    unset($single[$k]);
                }
            }
        }
    }

    public function getByIndex($key, $index)
    {
        if ($this->_offsetExists($key, $index)) {
            return $this->_offsetGet($key, $index);
        }
        throw new \Exception('Key {' . $key . '} does not exist in the ' . get_class($this) . ' ' . __FILE__);
    }


    public function remove($index)
    {
        unset($this->items[$index]);
    }

    public function copyFromTo($fromIndex,$toIndex)
    {
        $this->items[$toIndex] = $this->items[$fromIndex];
    }
}