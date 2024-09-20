<?php

namespace App;

use App\Lib\Amadeus\Flight\Collections\RefundCollection;
use Illuminate\Database\Eloquent\Model;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use Illuminate\Http\Request;

class TicketdataRefunds extends Model
{
    protected $table = 'ticketdata_refunds';
    protected $guarded = [];
    public $timestamps = false;

    public static function findByTicketNumberAndAgentAndTypeAndCrsId(RefundCollection &$refundCollection, IdentCollection $identCollection, $agent)
    {

        $ticketNumber = $refundCollection->offsetGet('ticket_number');


        $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);

        if (!empty($ticketNumber)) {
            $ticketsFound = Ticketdata::where('number', 'like', '%' . $ticketNumber . '%')
                ->get();

            if (!empty($ticketsFound->toArray())) {

                //check if it's the same agent
                foreach ($ticketsFound as $singleTicket) {
                    $pnrid = $singleTicket->pnr_id;
                    $identFound = Ident::where('agent', $agent)
                        ->where('id', $pnrid)
                        ->get();
//                    var_dump($identFound);die;
                    if (!empty($identFound->toArray())) {

//                            throw new \Exception('ticket number #'.$ticketNumber.' does exist!');
                        return true;
                        //return true;
                    }
                }
            }
        }
        return false;
    }



}
