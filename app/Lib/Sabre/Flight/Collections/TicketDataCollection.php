<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:57 PM
 */

namespace App\Lib\Sabre\Flight\Collections;



use Illuminate\Support\Collection;

class TicketDataCollection extends Collection
{

    protected $items =
        [
            [
                'participants_index'=>'',
                'emd_identifier'=>'',
                'pnr_id'=>'',
                'number' => '',
                'date' => '',
                'fop' => '',
                'isdomestic' => 'false',
                'iatanr' => '',
//                'endorsement' => '',
                'name' => '',
                'orig_pnr' => '',
                'farebase' => '',
                'fare_amount' => '0.00',
                'fare_currency' => '',
                'tax_amount' => '0.00',
                'tax_currency' => '',
                'equiv_amount' => '0.00',
                'equiv_currency' => '',
                'commission_rate' => '0.00',
                'commission_amount' => '0.00',
                'commission_vat' => '0.00',
                'original_number' => '',
                'date_orig' => '',
                'ticket_type' => '',
                'participants_id' => '',
                'valid_carrier' => '',
                'valid_carrier' => '',
                'iatanr_booking_agent' => '',
                'fare_commission' => '',
                'tour_code' => '',
                'conjunctive_flag' => 'false',
                'tour_operator'=>'',
                'partially_paid'=>'false',
                'remaining_amount'=>null,
                'remaining_amount_currency'=>null,
                'company_own_cc'=>'false',

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

    public function resetItemsIndexes(){
        $this->items = array_values($this->items);
    }
}
