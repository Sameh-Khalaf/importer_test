<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Sabre\Flight\Collections;


use Illuminate\Support\Collection;

class PriceCollection extends Collection
{

    protected $items =
        [
            'tax_codes'=>[],
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


        ]
    ];

    public function __construct()
    {
        parent::__construct($this->items);
    }

    public function getByIndex($key,$index){
        if($this->_offsetExists($key,$index)){
            return $this->_offsetGet($key,$index);
        }
        throw new \Exception('Key {' . $key . '} does not exist in the ' . get_class($this) . ' ' . __FILE__);
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
}