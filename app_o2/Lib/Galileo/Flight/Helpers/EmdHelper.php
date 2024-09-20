<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 6:18 PM
 */

namespace App\Lib\Galileo\Flight\Helpers;


use App\Lib\Galileo\Flight\Collections\EmdDataCollection;
use App\Lib\Galileo\Flight\Collections\IdentCollection;
use App\Lib\Galileo\Flight\Collections\ParticipantsCollection;
use App\Lib\Galileo\Flight\Collections\PriceCollection;
use App\Lib\Galileo\Flight\Collections\SegmentsCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataCollection;
use App\Lib\Galileo\Flight\DbHandler;
use App\Ticketdata;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Galileo\Flight\Collections\CustomRemarksCollection;
use App\Lib\Galileo\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
use App\Ident;
use App\Participants;
use App\ProcessedFiles;
use App\Segments;
use App\TicketdataATC;
use App\TicketdataTaxes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use test\Mockery\MockingInternalModuleClassWithOptionalParameterByReferenceTest;

class EmdHelper extends DbHandler
{

    public $duplication = true;

    public $notFound = false;

    public function __construct(IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                                SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                                ParticipantsCollection &$participantsCollection, EmdDataCollection &$emdDataCollection,
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {

        DbHandler::$agent = $agent;
        $ticketDataArray = $ticketDataCollection->toArray();
        $ticketNumber = $ticketDataArray[0]['number'];
        $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);

        //check ticket existence
        $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and ticket_type = '10' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";

        $ticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) && false == $ticketsFound[0]->exists || static::$output == true) {

            //Ticketdata::updateOrigPnrIfSplitPnr($ticketDataCollection->getByIndex('original_number', 0), $identCollection);

            $icw =  $ticketDataArray[0]['original_number'];
            if (empty($icw)) {
                return null;
            }
            $icw = str_replace(['T-K','T-L','T-E'],'',$icw);

            $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$icw%' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '14'))";

            $icwticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

            if (isset($icwticketsFound[0]) && true == $icwticketsFound[0]->exists || static::$output == true) {

                $identCollection->put('total_pnr_passengers',$participantsCollection->count());
                $this->fillIdent($identCollection, $segmentsCollection, $agent);


                DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                try {
                    $this->saveIdent($identCollection);
                    $this->saveSegments($segmentsCollection, $identCollection);
                    $this->saveTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection, $emdDataCollection);
                    $this->saveProcessedFile($identCollection, $file, $agent);

                    if (static::$output) {

                    } else {

                        $this->success($file, $agent);
                        DB::connection('pgsql_crs_'.$agent)->commit();
                    }
                } catch (\Exception $e) {
                    DbHandler::setFileChecksum($file, $agent, '');
                    DB::connection('pgsql_crs_'.$agent)->rollBack();
                    throw new \Exception($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
            }else{

                DbHandler::setFileChecksum($file, $agent, '');
                throw  new \Exception("ICW ticket doesn't exist ".$icw);

            }

        } else {

            DbHandler::setFileChecksum($file,$agent,'Ignore');


            throw new \Exception('Duplicated_EMD_ticket_  '.$ticketNumber);
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

    }


    private function fillIdent(IdentCollection &$identCollection, SegmentsCollection $segmentsCollection, $agent)
    {
        $identCollection->put('agent', $agent);
        $identCollection->put('crs_id', '14');


        //convert booking date
        $booking_date = $identCollection->offsetGet('booking_date');

        $date = \DateTime::createFromFormat('dMy', $booking_date);
        $identCollection->put('booking_date', $date->format('Y-m-d'));

        $segments = $segmentsCollection->toArray();

        reset($segments);
        $firstKey = key($segments);
        $from = $segments[$firstKey]['dep_date'];
        $identCollection->put('journey_from_date', $from);

        end($segments);
        $key = key($segments);
        $till = $segments[$key]['arr_date'];
        if($till == "0"){
            $till = $from;
        }

        if($till == "1"){
            $date = \DateTime::createFromFormat('dM', $from);
            $date->add(new \DateInterval('P1D'));
            $till = $date->format('dM');
        }
        if($till == "2"){
            $date = \DateTime::createFromFormat('dM', $from);
            $date->add(new \DateInterval('P2D'));
            $till = $date->format('dM');
        }
        if($till == "3"){
            $date = \DateTime::createFromFormat('dM', $from);
            $date->add(new \DateInterval('P3D'));
            $till = $date->format('dM');
        }
        if($till == "4"){
            $date = \DateTime::createFromFormat('dM', $from);
            $date->add(new \DateInterval('P4D'));
            $till = $date->format('dM');
        }
        if($till == "5"){
            $date = \DateTime::createFromFormat('dM', $from);
            $date->add(new \DateInterval('P5D'));
            $till = $date->format('dM');
        }

        $identCollection->put('journey_till_date', $till);
//        $identCollection->filter(function ($value){
//           return $value != '';
//        });
    }




    public static function saveTicketDataAndParticipants(TicketDataCollection &$ticketDataCollection,
                                                              ParticipantsCollection &$participantsCollection,
                                                              IdentCollection &$identCollection,
                                                              PriceCollection &$priceCollection)
{

    $ticketsCounter = $ticketDataCollection->count();

    for ($i = 0; $i < $ticketsCounter; $i++) {

        $ticketDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
        if ($ticketDataCollection->getByIndex('conjunctive_flag', $i) == 'false') {


            $participantsCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);

            $participantsModel = new Participants($participantsCollection->offsetGet($i));
            $participantsModel->setConnection('pgsql_crs_'.DbHandler::$agent);
            try {
                if (static::$output) {
                    print_r($participantsModel->toArray());
                } else {
                    $participantsModel->saveOrFail();
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__.' '.static::$file);
                die;
            }
            $participantsCollection->putByIndex('id', $participantsModel->id, $i);
        }

        $ticketDataCollection->putByIndex('participants_id', $participantsModel->id, $i);


        $amount = $ticketDataCollection->getByIndex('commission_amount',$i);
        if($amount == 0){
            $ticketDataCollection->putByIndex('commission_amount','0.00',$i);
        }
        $ticketDataModel = new Ticketdata($ticketDataCollection->offsetGet($i));
        $ticketDataModel->setConnection('pgsql_crs_'.DbHandler::$agent);
        try {
            if (static::$output) {
                print_r($ticketDataModel->toArray());
            } else {
                $ticketDataModel->saveOrFail();
            }
//                $ticketDataModel->saveOrFail();
        } catch (\Exception $e) {

            print_r($e->getMessage());
            var_dump($ticketDataCollection->toArray());
            print_r($ticketDataModel);
            die;
        }



    }
}


}