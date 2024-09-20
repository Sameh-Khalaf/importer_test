<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 6:18 PM
 */

namespace App\Lib\Amadeus\Flight\Helpers;


use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Lib\Amadeus\Flight\DbHandler;
use App\Ticketdata;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
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

        DbHandler::$agent = $agent;DbHandler::$agent = $agent;
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

            if (!empty($icw) && $icw === '-') {
                //handle emd only


                $this->fillIdent($identCollection, $segmentsCollection, $agent);

                $this->fillTicketdata($ticketDataCollection, $priceCollection);

                $this->fillSegments($segmentsCollection, $identCollection, $priceCollection);

                $this->fillParticipants($participantsCollection, $priceCollection);

                DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                try {
                    $this->saveIdent($identCollection);
                    $this->saveSegments($segmentsCollection, $identCollection);
                    $this->saveEMDTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection, $emdDataCollection);
                    $this->saveProcessedFile($identCollection, $file, $agent);

                    //we need to remove fees if that emd inside reissue
                    if ($priceCollection->offsetGet('emd_flag')) {

                        foreach ($invoiceRemarksCollection as $k => $single) {
                            if ($single['remark_type'] !== 'DIP MARKUP') {
                                $invoiceRemarksCollection->forget($k);
                            }
                        }
                        $invoiceRemarksCollection = new InvoiceRemarksCollection(array_values($invoiceRemarksCollection->toArray()));
                    }

                    $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                    $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
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


            }else {
                $icw = str_replace(['T-K', 'T-L', 'T-E'], '', $icw);

                $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$icw%' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";

                $icwticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

                if (isset($icwticketsFound[0]) && true == $icwticketsFound[0]->exists || static::$output == true) {


                    $this->fillIdent($identCollection, $segmentsCollection, $agent);

                    $this->fillTicketdata($ticketDataCollection, $priceCollection);

                    $this->fillParticipants($participantsCollection, $priceCollection);

                    DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                    try {
                        $this->saveIdent($identCollection);
                        $this->saveEMDTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection, $emdDataCollection);
                        $this->saveProcessedFile($identCollection, $file, $agent);

                        //we need to remove fees if that emd inside reissue
                        if ($priceCollection->offsetGet('emd_flag')) {

                            foreach ($invoiceRemarksCollection as $k => $single) {
                                if ($single['remark_type'] !== 'DIP MARKUP') {
                                    $invoiceRemarksCollection->forget($k);
                                }
                            }
                            $invoiceRemarksCollection = new InvoiceRemarksCollection(array_values($invoiceRemarksCollection->toArray()));
                        }

                        $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                        $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
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
                } else {

                    DbHandler::setFileChecksum($file, $agent, '');
                    throw  new \Exception("ICW ticket doesn't exist TKT: $icw");

                }
            }

        } else {

            DbHandler::setFileChecksum($file,$agent,'Ignore');
            renameFile($file,'ignore');

            throw new \Exception('Duplicated_EMD_ticket_  '.$ticketNumber);
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

    }
    private function fillSegments(SegmentsCollection &$segmentsCollection, IdentCollection $identCollection, PriceCollection $priceCollection)
    {
        $segmentsCounter = $segmentsCollection->count();
        $taxes = $priceCollection->offsetGet('tax_amount');
        if (empty($taxes)) {
            $totalTaxesAmount = '0.0';
        } else {
            $totalTaxesAmount = array_sum(array_column($taxes, 'amount'));
        }

        for ($i = 0; $i < $segmentsCounter; $i++) {
            if(empty($priceCollection->offsetGet('equiv_amount'))){
                $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('fare_amount'), $i);
                $segmentsCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $i);
            }else{
                $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('equiv_amount'), $i);
                $segmentsCollection->putByIndex('fare_currency', $priceCollection->offsetGet('equiv_currency'), $i);
            }

            $segmentsCollection->putByIndex('total_tax', $totalTaxesAmount, $i);
            $segmentsCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $i);
        }
    }

    private function fillIdent(IdentCollection &$identCollection, SegmentsCollection $segmentsCollection, $agent)
    {
        $identCollection->put('agent', $agent);
        $identCollection->put('crs_id', '1');


        //convert booking date
        $booking_date = $identCollection->offsetGet('booking_date');
        $date = \DateTime::createFromFormat('ymd', $booking_date);
        $identCollection->put('booking_date', $date->format('Y-m-d'));

        $segments = $segmentsCollection->toArray();
        reset($segments);
        $firstKey = key($segments);
        $from = $segments[$firstKey]['dep_date'];
        $identCollection->put('journey_from_date', $from);

        end($segments);
        $key = key($segments);
        $till = $segments[$key]['arr_date'];
        $identCollection->put('journey_till_date', $till);

//        $identCollection->filter(function ($value){
//           return $value != '';
//        });
    }

    private function fillTicketdata(TicketDataCollection &$ticketDataCollection, PriceCollection $priceCollection)
    {
        $totalTaxesAmount = $priceCollection->offsetGet('tax_amount');
        $taxCurrency = $priceCollection->offsetGet('total_currency');
        if (empty($totalTaxesAmount)) {
            $totalTaxesAmount = '0.00';
            $taxCurrency = '';
        }
        $ticketCounter = $ticketDataCollection->count();

        $fop = $priceCollection->offsetGet('form_of_payment');
        for ($i = 0; $i < $ticketCounter; $i++) {
            $fopExplode = explode('-',$fop);
            $identifier = $ticketDataCollection->getByIndex('emd_identifier',$i);
            foreach ($fopExplode as $singleFop){
                $singleFopExplode = explode(';',$singleFop);
                if(isset($singleFopExplode[3]) && $singleFopExplode[3] == $identifier){
                    $ticketDataCollection->putByIndex('fop', $singleFopExplode[0], $i);
                }
            }
//            $ticketDataCollection->putByIndex('fop', $priceCollection->offsetGet('form_of_payment'), $i);
            $ticketDataCollection->putByIndex('tax_amount', number_format($totalTaxesAmount,2,'.',''), $i);
            $ticketDataCollection->putByIndex('tax_currency', $taxCurrency, $i);
            $ticketDataCollection->putByIndex('commission_rate', '0.00', $i);
            $ticketDataCollection->putByIndex('commission_amount', '0.00', $i);
            $ticketDataCollection->putByIndex('commission_amount', '0.00', $i);
            $ticketDataCollection->putByIndex('partially_paid', 'false', $i);
            $equivAmount = $ticketDataCollection->getByIndex('equiv_amount',$i);
            if(!is_float($equivAmount)){
                $equivAmount = number_format($equivAmount,2,'.','');
                $ticketDataCollection->putByIndex('equiv_amount',$equivAmount,$i);
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


    /**
     * @param TicketDataCollection $ticketDataCollection
     * @param ParticipantsCollection $participantsCollection
     * @param IdentCollection $identCollection
     * @param PriceCollection $priceCollection
     * @param EmdDataCollection $emdDataCollection
     * @throws \Throwable
     */
    public function saveEMDTicketDataAndParticipants(TicketDataCollection &$ticketDataCollection,
                                                     ParticipantsCollection &$participantsCollection,
                                                     IdentCollection &$identCollection,
                                                     PriceCollection &$priceCollection,
                                                     EmdDataCollection &$emdDataCollection)
    {
        $ticketsCounter = $ticketDataCollection->count();
        $savedParticipantIndex = -1;
        for ($i = 0; $i < $ticketsCounter; $i++) {
            $ticketDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);

            $participantsCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);


            $participantIndex = $ticketDataCollection->getByIndex('participants_index', $i);
            if ($participantIndex > -1 && $participantIndex != $savedParticipantIndex) {

                $participantsModel = new Participants($participantsCollection->offsetGet($participantIndex));
                $participantsModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                if (static::$output) {
                    print_r('Participants: ');
                    print_r($participantsModel->toArray());
                } else {
                    $participantsModel->saveOrFail();
                }
                $participantsCollection->putByIndex('id', $participantsModel->id, $i);

                $savedParticipantIndex = $participantIndex;

            }

            if (isset($participantsModel)) {
                $ticketDataCollection->putByIndex('participants_id', $participantsModel->id, $i);
                $ticketDataCollection->putByIndex('company_own_cc', $priceCollection->get('own_cc'), $i);
                $ticketDataModel = new Ticketdata($ticketDataCollection->offsetGet($i));
                $ticketDataModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                if (static::$output) {
                    print_r('TicketData: ');
                    print_r($ticketDataModel->toArray());
                } else {
                    try {
                        $ticketDataModel->saveOrFail();
                    }catch (\Exception $e){
                        print_r($e->getMessage());
                        print_r($ticketDataCollection->offsetGet($i));die;
                    }
                }

                //saving emd data
                $emdIdentifier = $ticketDataCollection->getByIndex('emd_identifier', $i);
                foreach ($emdDataCollection->toArray() as $singleEmd) {
                    if ($singleEmd['tsm_identifier'] == $emdIdentifier) {
                        $singleEmd['ticketdata_id'] = $ticketDataModel->id;
                        $singleEmd['pnr_id'] = $identCollection->offsetGet('id');
//                    $emdDataCollection->putByIndex('ticketdata_id', $ticketDataModel->id, $i);
//                    $emdDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
                        $emdDataModel = new EmdData($singleEmd);
                        $emdDataModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                        if (static::$output) {
                            print_r('EmdData: ');
                            print_r($emdDataModel->toArray());
                        } else {
                            $emdDataModel->saveOrFail();
                        }
                    }
                }
            } else {
                throw new \Exception('Participants not set!');
            }

        }
    }

}