<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 6:18 PM
 */

namespace App\Lib\Sabre\Flight\Helpers;


use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;
use App\Lib\Sabre\Flight\DbHandler;
use App\Ticketdata;
use App\Lib\Sabre\Flight\Collections\TicketDataATCCollection;
use App\Participants;
use App\TicketdataATC;
use App\TicketdataTaxes;
use Illuminate\Support\Facades\DB;

class ReIssueHelper extends DbHandler
{

    public $duplication = true;

    public function __construct(IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                                SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                                ParticipantsCollection &$participantsCollection, TicketDataATCCollection $ticketDataATCCOllection,
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {


        //check ticket existence
        DbHandler::$agent = $agent;
        $ticketDataArray = $ticketDataCollection->toArray();


        if(count($ticketDataArray) >1){

            foreach ($ticketDataArray as $key=>$singleTicket){
                $ticketNumber = $singleTicket['number'];
                $originalTicketNumber = $singleTicket['original_number'];
                $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and original_number like '%$originalTicketNumber%' and ticket_type in(1,7)  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '7'))" ;
                $ticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);
                if (isset($ticketsFound[0]) && true == $ticketsFound[0]->exists ){
                    unset($ticketDataArray[$key]);
                    $ticketDataCollection->remove($key);
                }

            }

            $ticketDataArrayreset = array_values($ticketDataArray);
            $ticketDataArray = $ticketDataArrayreset;

            $ticketDataCollection = new TicketDataCollection();
            $ticketDataCollection->put(0,$ticketDataArray[0]);

        }


        $ticketNumber = $ticketDataArray[0]['number'];
        $originalTicketNumber = $ticketDataArray[0]['original_number'];


        $ticketType = $ticketDataArray[0]['ticket_type'];

//        $sql = "SELECT EXISTS (select pnr_id from ticketdata where
//                number like '%$ticketNumber%' and ticket_type = '$ticketType' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";

        $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and original_number like '%$originalTicketNumber%' and ticket_type in(1,7)  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '7'))" ;
        $ticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) && false == $ticketsFound[0]->exists || (static::$output == true)) {

            if ($ticketDataCollection->count() != 0 && Ticketdata::checkIssueTicketExist($ticketDataCollection, $agent,'7') == true) {

                Ticketdata::updateOrigPnrIfSplitPnr($ticketDataCollection->getByIndex('original_number', 0), $identCollection,$agent);

                $identCollection->put('total_pnr_passengers',$participantsCollection->count());

                $this->fillIdent($identCollection, $segmentsCollection, $agent);

                $this->fillParticipants($participantsCollection, $priceCollection);

                DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                try {

                    $this->saveIdent($identCollection);
                    $this->saveSegments($segmentsCollection, $identCollection);
                    $this->saveTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection, $ticketDataATCCOllection);
                    $this->saveProcessedFile($identCollection, $file, $agent);
                    $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                    $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
                    if ($priceCollection->offsetGet('emd_flag')) //we will not mark file as processed until we add emd tickets
                    {

                    } else {
                        $this->success($file, $agent);
                    }


                    if (static::$output == false) {
                        DB::connection('pgsql_crs_'.$agent)->commit();
                    }

                } catch (\Exception $e) {

                    DbHandler::setFileChecksum($file,$agent,'');
                    DB::connection('pgsql_crs_'.$agent)->rollBack();
                    throw new \Exception($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }


            }else{

                if ($priceCollection->offsetGet('emd_flag'))
                {
                    return;

                }else {

                    DbHandler::setFileChecksum($file, $agent, '');
                }
//                DbHandler::setFileChecksum($file, $agent, '');

                throw new \Exception('Issue_ticket_no_exist #'.$originalTicketNumber);
                return false;
            }
        } else {
            DbHandler::setFileChecksum($file,$agent,'Ignore');


            //throw new \Exception('Duplicated_ReIssue_ticket');
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

    }


    private function fillIdent(IdentCollection &$identCollection, SegmentsCollection $segmentsCollection, $agent)
    {
        $identCollection->put('agent', $agent);


        //convert booking date
        //convert booking date
        $booking_date = $identCollection->offsetGet('booking_date').date('y');
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

    }



    private function fillParticipants(ParticipantsCollection &$participantsCollection, PriceCollection $priceCollection)
    {
        $participantsCounter = $participantsCollection->count();


        for ($i = 0; $i < $participantsCounter; $i++) {
            if($priceCollection->offsetGet('fare_currency') != 'EGP'){
                $participantsCollection->putByIndex('price', $priceCollection->offsetGet('equiv_amount'), $i);
            }else{
                $participantsCollection->putByIndex('price', $priceCollection->offsetGet('fare_amount'), $i);
            }
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

            $ticketDataCollection->putByIndex('farebase', $priceCollection->get('fare_basis'), $i);
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


            if (!empty(($priceCollection->offsetGet('tax_codes')))) {
                foreach ($priceCollection->offsetGet('tax_codes') as $single) {
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