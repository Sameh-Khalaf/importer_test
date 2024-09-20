<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:58 PM
 */

namespace App\Lib\Sabre\Flight\Collections;



use Illuminate\Support\Collection;

class SegmentsCollection extends Collection
{

    protected $items =
        [
            [
                'pnr_id' => '',
                'dep_city' => '',
                'dep_city_name' => '',
                'dep_date' => '',
                'dep_time' => '',
                'arr_city' => '',
                'arr_city_name' => '',
                'arr_date' => '',
                'arr_time' => '',
                'fare' => '',
                'total_tax' => '',
                'flight_no' => '',
                'class_of_service' => '',
                'class_of_booking' => '',
                'status' => '',
                'segtype' => '',
                'ticketed' => '',
                'filekey' => '',
                'carrier' => '',
                'tour_operator' => '',
                'fare_currency' => '',
                'tax_currency' => '',
                'stop_over' => '',
                'payment' => 'A',
                'equipment'=>null
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


    public function getByIndex($key, $index)
    {
        if ($this->_offsetExists($key, $index)) {
            return $this->_offsetGet($key, $index);
        }
        return false;
//        throw new \Exception('Key {' . $key . '} does not exist in the ' . get_class($this) . ' ' . __FILE__);
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

