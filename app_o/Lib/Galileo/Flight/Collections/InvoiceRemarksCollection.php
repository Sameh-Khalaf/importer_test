<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:59 PM
 */

namespace App\Lib\Galileo\Flight\Collections;



use Illuminate\Support\Collection;

class InvoiceRemarksCollection extends collection
{
    protected $items =
        [
            [
                'pnr_id'=>'',
                'currency'=>'',
                'amount' => '',
                'remark' => '',
                'remark_type' => ''
            ]
        ];

    public function __construct($items = null)
    {
        if(null == $items) {
            parent::__construct($this->items);
        }else{
            parent::__construct($items);
        }
    }


    public function putByIndex($key, $value, $index)
    {
        if ($this->_offsetExists($key,$index)) {
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
                $array[$k] = '';
            }
        }
        return $array;
    }

    private function _offsetSet($key, $value, $index)
    {
        $this->items[$index][$key] = $value;
    }

    public function offsetGetByIndex($key,$index)
    {
        return $this->items[$index][$key];
    }
}