<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 4:56 PM
 */

namespace App\Lib\Sabre\Flight\Collections;



use Illuminate\Support\Collection;

class IdentCollection extends Collection
{

    protected $items =
        [
            'pnr_id' => '',
            'agent' => '',
            'office_id' => '',
            'tktoffice_id' => '',
            'booking_iata' => '',
            'ticketing_iata' => '',
//            'processed' => '',
            'crs_id' => '',
            'user_id' => '0',
            'booking_date' => '',
            'journey_from_date' => '',
            'journey_till_date' => '',
            'owner_id' => '',
            'affiliate' => '',
            'match_code' => '',
            'ticketing_sine' => '',
            'booking_sine' => '',
            'pnr_original' => '',
            'total_pnr_passengers' => '',
            'valid_carrier' => '',
            'isdomestic' => '',
            'version'=>'',
            'auto_import_order'=>'',
            'has_online_invoice'=>'false',
            'has_online_voucher'=>'false',
            'sabre_inv_number'=> ''
        ];

    public function __construct()
    {
        parent::__construct($this->items);
    }

    public function put($key, $value)
    {
        if ($this->offsetExists($key)) {
            $this->offsetSet($key, $value);
            return $this;
        }
        throw new \Exception('Key {' . $key . '} does not exist in the ' . get_class($this) . ' ' . __FILE__);
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    public function putNotExistKeyWithVal($key, $value)
    {
        if ($this->offsetExists($key)) {
            throw new \Exception('Key {' . $key . '} does exist in the ' . get_class($this) . ' ' . __FILE__);
        }
        $this->offsetSet($key, $value);
        return $this;
    }
}
