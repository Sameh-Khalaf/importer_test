<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/23/19
 * Time: 11:51 AM
 */

namespace App\Lib\Amadeus\Flight\Helpers;

use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Lib\Amadeus\Flight\DbHandler;
use App\Ticketdata;
use App\TicketdataRefunds;
use Illuminate\Support\Facades\DB;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
use App\Ident;
use App\Participants;
use App\ProcessedFiles;
use App\Segments;
use App\TicketdataATC;
use App\TicketdataTaxes;


class VoidHelper extends DbHandler
{

    public $duplication = true;
    private $notFound;

    public function __construct(IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                                PriceCollection &$priceCollection,
                                ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {

        //check first if we have this ticket number before
        DbHandler::$agent = $agent;
        $ticketDataArray = $ticketDataCollection->toArray();
        $ticketNumber = $ticketDataArray[0]['number'];
        $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);

        $ticketType = $ticketDataArray[0]['ticket_type'];

//        $sql = "SELECT EXISTS (select pnr_id from ticketdata where
//                number like '%$ticketNumber%' and ticket_type = '$ticketType' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";
        $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and ticket_type in(2,12,13)  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))" ;
        $ticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) && false == $ticketsFound[0]->exists) {

            $explode = explode('-', $ticketNumber);

            $sql = "select id,pnr_id,agent,valid_carrier,journey_from_date,journey_till_date,booking_date from ident where 
                 agent = '$agent' and crs_id = '1' and id in 
                (select pnr_id from ticketdata where  number like '%$explode[1]%' ) ";
            $issueTicket =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

            if (count($issueTicket)) {

                $identCollection->offsetSet('pnr_id', $issueTicket[0]->pnr_id);
                $identCollection->offsetSet('valid_carrier', $issueTicket[0]->valid_carrier);
                $identCollection->offsetSet('journey_from_date', $issueTicket[0]->journey_from_date);
                $identCollection->offsetSet('journey_till_date', $issueTicket[0]->journey_till_date);
                $identCollection->offsetSet('booking_date', $issueTicket[0]->booking_date);

                $this->notFound = false;


            }else{
                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('NO_EMD_OR_ISSUE_TO_VOID '."TKT: $ticketNumber ");
                return false;
            }

            $sql = "select * from ticketdata where pnr_id = ".$issueTicket[0]->id;
            $issueTicketData =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

            DB::connection('pgsql_crs_'.$agent)->beginTransaction();
            try {


                $this->fillIdent($identCollection, $agent);

                $this->fillTicketdata($ticketDataCollection, $identCollection->offsetGet('pnr_id'), $issueTicketData, $agent, $file);


                $this->fillParticipants($participantsCollection, $priceCollection);


                $this->saveIdent($identCollection);
                $this->saveTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection);
                $this->saveProcessedFile($identCollection, $file, $agent);
                $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
                $this->success($file, $agent);
                DB::connection('pgsql_crs_'.$agent)->commit();

            } catch (\Exception $e) {
                DbHandler::setFileChecksum($file,$agent,'');
                DB::connection('pgsql_crs_'.$agent)->rollBack();
                throw new \Exception($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
            //var_dump('zzzzzzz');die;
            DbHandler::setFileChecksum($file,$agent,'Ignore');

            renameFile($file,'ignore');
            throw new \Exception('Duplicated_VOID_ticket '.json_encode($ticketDataCollection));
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

    }


    private function fillIdent(IdentCollection &$identCollection, $agent)
    {
        $identCollection->put('agent', $agent);
        $identCollection->put('crs_id', '1');

        //$pnrid = $identCollection->offsetGet('pnr_id');

       // $identCollection->put('booking_date', $booking_date);
        //$date = \DateTime::createFromFormat('dM', $booking_date);
//        print_r($date);print_r($booking_date);die;
        //$identCollection->put('booking_date', $date->format('Y-m-d'));
    }

    private function fillTicketdata(TicketDataCollection &$ticketDataCollection, $pnrId, $issueTicket, $agent, $file)
    {


        $ticketType = $ticketDataCollection->getByIndex('ticket_type', 0);

        if ($ticketType == 13) //void refund
        {


            if (!empty($issueTicket)) {



                $ticketData = $issueTicket[0];
                $ticketCounter = $ticketDataCollection->count();
                for ($i = 0; $i < $ticketCounter; $i++) {
                    $ticketDataCollection->putByIndex('fare_amount', $ticketData->fare_amount * -1, $i);
                    $ticketDataCollection->putByIndex('fare_currency', $ticketData->fare_currency, $i);
                    $ticketDataCollection->putByIndex('tax_amount', '0.00', $i);
                    $ticketDataCollection->putByIndex('tax_currency', '', $i);
                    $ticketDataCollection->putByIndex('equiv_amount', '0.00', $i);
                    $ticketDataCollection->putByIndex('equiv_currency', '', $i);
                    $ticketDataCollection->putByIndex('orig_pnr', $pnrId, $i);
                }
            } else {
                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('VREF ticket number #' . $ticketDataCollection->getByIndex('number', 0) . ' doesnt exist');

            }


        } else {
//            $ticketsFound = Ticketdata::where('number', 'like', '%' . $ticketDataCollection->getByIndex('number', 0) . '%')
//                ->get();


            if (!empty($issueTicket)) {


                $ticketData = $issueTicket[0];
                $ticketCounter = $ticketDataCollection->count();

                for ($i = 0; $i < $ticketCounter; $i++) {
                    $ticketDataCollection->putByIndex('fare_amount', $ticketData->fare_amount * -1, $i);
                    $ticketDataCollection->putByIndex('fare_currency', $ticketData->fare_currency, $i);
                    $ticketDataCollection->putByIndex('tax_amount', $ticketData->tax_amount * -1, $i);
                    $ticketDataCollection->putByIndex('tax_currency', $ticketData->tax_currency, $i);
                    $ticketDataCollection->putByIndex('equiv_amount', $ticketData->equiv_amount * -1, $i);
                    $ticketDataCollection->putByIndex('equiv_currency', $ticketData->equiv_currency, $i);
                    $ticketDataCollection->putByIndex('valid_carrier', $ticketData->valid_carrier, $i);
                    $ticketDataCollection->putByIndex('orig_pnr', $pnrId, $i);
                }
            } else {

                throw new \Exception('V ticket number #' . $ticketDataCollection->getByIndex('number', 0) . ' doesnt exist');

            }



        }

    }


    private function fillParticipants(ParticipantsCollection &$participantsCollection, PriceCollection $priceCollection)
    {
        $participantsCounter = $participantsCollection->count();
        for ($i = 0; $i < $participantsCounter; $i++) {
            $participantsCollection->putByIndex('price', $priceCollection->offsetGet('total_amount'), $i);
        }
    }


    public static function saveTicketDataAndParticipants(TicketDataCollection &$ticketDataCollection,
                                                         ParticipantsCollection &$participantsCollection,
                                                         IdentCollection &$identCollection,
                                                         PriceCollection &$priceCollection)
    {
        $ticketsCounter = $ticketDataCollection->count();

        for ($i = 0; $i < $ticketsCounter; $i++) {
            $ticketDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
            $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $i);
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
//                    $participantsModel->saveOrFail();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__.' '.static::$file);
//                    var_dump($participantsCollection->toArray(), $participantsModel->toArray(), static::$file);
                    die;
                }
                $participantsCollection->putByIndex('id', $participantsModel->id, $i);
            }

            $ticketDataCollection->putByIndex('participants_id', $participantsModel->id, $i);
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
                print_r($ticketDataModel);
                die;
            }


            if (!empty(($priceCollection->offsetGet('tax_amount')))) {
                foreach ($priceCollection->offsetGet('tax_amount') as $single) {
                    $single['pnr_id'] = $identCollection->offsetGet('id');
                    $single['ticketdata_id'] = $ticketDataModel->id;
                    $ticketDataTaxesModel = new TicketdataTaxes($single);
                    $ticketDataTaxesModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                    if (static::$output) {
                        print_r($ticketDataTaxesModel->toArray());
                    } else {
                        $ticketDataTaxesModel->saveOrFail();
                    }
//                    $ticketDataTaxesModel->saveOrFail();
                }
            }

        }
    }
}