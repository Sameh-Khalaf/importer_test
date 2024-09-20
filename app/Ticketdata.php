<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Ticketdata extends Model
{
    //
    protected $table = 'ticketdata';
    protected $guarded = ['emd_identifier', 'participants_index'];
    public $timestamps = false;

    public static function findByTicketNumberAndAgentAndTypeAndCrsId( &$ticketDataCollection,  $identCollection, $agent)
    {
        $ticketCounter = $ticketDataCollection->count();
        $duplicateTickets = 0;
        for ($i = 0; $i < $ticketCounter; $i++) {

            $ticketNumber = $ticketDataCollection->getByIndex('number', $i);
            $ticketType = $ticketDataCollection->getByIndex('ticket_type', $i);
            $fareamount = $ticketDataCollection->getByIndex('fare_amount', $i);

            if($identCollection->offsetGet('crs_id') == '1') {
                $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);
            }

            //fare_amount= $fareamount
            if (!empty($ticketNumber)) {
                $crsId= $identCollection->offsetGet('crs_id');
                $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and ticket_type = $ticketType 
                                               and pnr_id in (select id from ident where  agent = '$agent' and crs_id = $crsId))";
                $ticketsFound = DB::connection("pgsql_crs_".$agent)->select($sql);

                if (isset($ticketsFound[0]) && true == $ticketsFound[0]->exists)
                {
//                    $logger = new \Monolog\Logger("ImporterLog");
//                    $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/ImporterLog_Amadeus' . date('Y-m-d') . '.log'));
//                    $logger->critical($ticketNumber.' #sql: '.$sql);
                    $duplicateTickets++;
                    $ticketDataCollection->remove($i);
                }

            }

        }
        if($duplicateTickets == $ticketCounter){
            return true;
        }
        $ticketDataCollection->resetItemsIndexes();
        return false;
    }

    public static function updateOrigPnrIfSplitPnr($originalTicketNumber,  &$identCollection,$agent)
    {
        $ticketData = DB::connection("pgsql_crs_".$agent)->table('ticketdata')->where('number','like', '%' . $originalTicketNumber . '%')

            ->get();
      //  $ticketData = Ticketdata::where('number', 'like', '%' . $originalTicketNumber . '%')
       //     /*->where('ticket_type', '1')*/->get();


        if (!empty($ticketData->toArray())) {
            $ticketData->each(function ($singleTicket) use ($identCollection) {

                if ($identCollection->offsetGet('pnr_id') !== $singleTicket->orig_pnr) {
                    $identCollection->put('pnr_original', $singleTicket->orig_pnr);
                }
            });
        }
    }


    public static function checkIssueTicketExist($ticketDataCollection, $agent,$crsId = 1)
    {
        $issueTicketNumber = $ticketDataCollection->getByIndex('original_number', 0);

        $icw = str_replace(['T-K','T-L','T-E'],'',$issueTicketNumber);

        $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$icw%' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '$crsId'))";

        $icwticketsFound = DB::connection("pgsql_crs_".$agent)->select($sql);

        if (isset($icwticketsFound[0]) && true == $icwticketsFound[0]->exists)
        {
            return true;
        }
        return false;

    }


    public static function findICWTicketExist(TicketDataCollection &$ticketDataCollection, $agent)
    {
//        $issueTicketNumber = $ticketDataCollection->getByIndex('original_number',0);
        $ticketCounter = $ticketDataCollection->count();
        for ($i = 0; $i < $ticketCounter; $i++) {

            $issueTicketNumber = $ticketDataCollection->getByIndex('original_number', $i);
            if (empty($issueTicketNumber)) {
                return null;
            }
            $issueTicketNumber = str_replace(['T-K','T-L','T-E'],'',$issueTicketNumber);

            $ticketsFound = DB::connection("pgsql_crs_".$agent)->table('ticketdata')->where('number','like', '%' . $issueTicketNumber . '%')
                ->get();
            //$ticketsFound = Ticketdata::where('number', 'like', '%' . $issueTicketNumber . '%')->get();

            if (!empty($ticketsFound->toArray())) {
                //check if it's the same agent
                foreach ($ticketsFound as $singleTicket) {
                    $pnrid = $singleTicket->pnr_id;
                    $identFound =  DB::connection("pgsql_crs_".$agent)->table('ident')->where('agent', $agent)->get();
                    /*$identFound = Ident::where('agent', $agent)
                        ->where('id', $pnrid)
                        ->get();*/

                    if (empty($identFound->toArray())) {
                        $ticketDataCollection->remove($i);
                    }
                }

            }else {
//                print_r($ticketDataCollection->toArray());die;
                throw new \Exception('Emd ticket number #'.$issueTicketNumber.' does not exist!');
                return;
//                $ticketDataCollection->remove($i);
//                print_r($ticketDataCollection->toArray());die;
//                $ticketDataCollection = new TicketDataCollection($ticketDataCollection->values());
            }
        }
        if($ticketDataCollection->count() == 0)
        {
            return false;
        }
        return null;

    }
}
