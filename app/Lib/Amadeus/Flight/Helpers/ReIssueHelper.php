<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 6:18 PM
 */

namespace App\Lib\Amadeus\Flight\Helpers;


use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Lib\Amadeus\Flight\DbHandler;
use App\Ticketdata;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
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
        $ticketNumber = $ticketDataArray[0]['number'];
        $originalTicketNumber = $ticketDataArray[0]['original_number'];
        $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);

        $originalTicketNumber = str_replace(['T-K','T-L','T-E'],'',$originalTicketNumber);


        $ticketType = $ticketDataArray[0]['ticket_type'];

//        $sql = "SELECT EXISTS (select pnr_id from ticketdata where
//                number like '%$ticketNumber%' and ticket_type = '$ticketType' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";
        $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and original_number like '%$originalTicketNumber%' and ticket_type in(1,7)  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))" ;
        $ticketsFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) && false == $ticketsFound[0]->exists || (static::$output == true)) {

            if ($ticketDataCollection->count() != 0 && Ticketdata::checkIssueTicketExist($ticketDataCollection, $agent) == true) {

                Ticketdata::updateOrigPnrIfSplitPnr($ticketDataCollection->getByIndex('original_number', 0), $identCollection,$agent);

                $this->fillIdent($identCollection, $segmentsCollection, $agent);

                $this->fillTicketdata($ticketDataCollection, $priceCollection);

                $this->fillSegments($segmentsCollection, $identCollection, $priceCollection);

                $this->fillParticipants($participantsCollection, $priceCollection);

                DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                try {
                    $this->saveIdent($identCollection);
                    $this->saveSegments($segmentsCollection, $identCollection);
                    $this->saveTicketDataAndParticipantsReissue($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection, $ticketDataATCCOllection);
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

                throw new \Exception('Issue_ticket_no_exist '."Orignal: $originalTicketNumber => NEW: $ticketNumber");
                return false;
            }
        } else {

            DbHandler::setFileChecksum($file,$agent,'Ignore');
            renameFile($file,'ignore');

            throw new \Exception('Duplicated_ReIssue_ticket'.json_encode($ticketDataCollection));
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

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

    }

    private function fillTicketdata(TicketDataCollection &$ticketDataCollection, PriceCollection &$priceCollection)
    {
        $taxes = $priceCollection->offsetGet('tax_amount');

        if (empty($taxes)) {
            $totalTaxesAmount = '0.0';
            $taxCurrency = '';

        } else {
            $totalTaxesAmount = array_sum(array_column($taxes, 'amount'));
            $taxCurrency = $taxes[0]['currency'];
        }


        $totalAmount = $priceCollection->offsetGet('total_amount');
        $totalCurrency = $priceCollection->offsetGet('total_currency');

//        $fareAmount = $priceCollection->offsetGet('fare_amount');
        $fareCurrency = $priceCollection->offsetGet('fare_currency');

//        $equivAmount = $priceCollection->offsetGet('equiv_amount');
        $equivCurrency = $priceCollection->offsetGet('equiv_currency');

        if ($fareCurrency == $totalCurrency && empty($equivCurrency)) {

            $newFareAmount = $totalAmount - $totalTaxesAmount;

            $priceCollection->put('fare_amount', $newFareAmount);

        } elseif ($equivCurrency == $totalCurrency) {

            $newFareAmount = $totalAmount - $totalTaxesAmount;

            $priceCollection->put('fare_amount', $newFareAmount);
            $priceCollection->put('fare_currency', $equivCurrency);

        } elseif (is_null($equivCurrency) && $fareCurrency !== $totalCurrency) {
            $newFareAmount = $totalAmount - $totalTaxesAmount;

            $priceCollection->put('fare_amount', $newFareAmount);
            $priceCollection->put('fare_currency', $totalCurrency);

        } else {
            var_dump($priceCollection);
            die;
        }
        $ticketDataCollectionCounter = $ticketDataCollection->count();
        for ($i = 0; $i < $ticketDataCollectionCounter; $i++) {
            $ticketDataCollection->putByIndex('fop', $priceCollection->offsetGet('form_of_payment'), $i);
            $ticketDataCollection->putByIndex('tax_amount', $totalTaxesAmount, $i);
            $ticketDataCollection->putByIndex('tax_currency', $taxCurrency, $i);
            $ticketDataCollection->putByIndex('tax_currency', $taxCurrency, $i);
            $ticketDataCollection->putByIndex('equiv_amount', $priceCollection->offsetGet('equiv_amount'), $i);
            $ticketDataCollection->putByIndex('fare_amount', $priceCollection->offsetGet('fare_amount'), $i);
            $ticketDataCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $i);
        }


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

//            $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('fare_amount'), $i);
            $segmentsCollection->putByIndex('total_tax', $totalTaxesAmount, $i);
            $segmentsCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $i);
//            $segmentsCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $i);
//            $segmentsCollection->putByIndex('fare_currency',$priceCollection->offsetGet('fare_currency'),$i);
        }
    }


    private function fillParticipants(ParticipantsCollection &$participantsCollection, PriceCollection $priceCollection)
    {

        $participantsCounter = $participantsCollection->count();
        for ($i = 0; $i < $participantsCounter; $i++) {
            $participantsCollection->putByIndex('price', $priceCollection->offsetGet('total_amount'), $i);
        }
    }

    public static function saveTicketDataAndParticipantsReissue(TicketDataCollection &$ticketDataCollection,
                                                                ParticipantsCollection &$participantsCollection,
                                                                IdentCollection &$identCollection,
                                                                PriceCollection &$priceCollection,
                                                                TicketDataATCCollection &$ticketDataATCCOllection)
    {
        $participantsInsertedFlag = false;
        $ticketDataCollectionCounter = $ticketDataCollection->count();
        for ($i = 0; $i < $ticketDataCollectionCounter; $i++) {
            $ticketDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
            if ($ticketDataCollection->getByIndex('conjunctive_flag', 0) == 'false') {

                if($participantsInsertedFlag == false) {
                    $participantsCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), 0);

                    $participantsModel = new Participants($participantsCollection->offsetGet(0));
                    $participantsModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                    try {

                        if (static::$output) {
                            print_r($participantsCollection);
                        } else {
                            $participantsModel->saveOrFail();
                        }
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__.' '.static::$file);
                        //var_dump($participantsCollection->toArray(), $participantsModel->toArray(), static::$file);
                        die;
                    }
                    $participantsCollection->putByIndex('id', $participantsModel->id, 0);
                    $participantsInsertedFlag = true;
                }

                $ticketDataCollection->putByIndex('participants_id', $participantsModel->id, $i);
                $ticketDataCollection->putByIndex('company_own_cc', $priceCollection->get('own_cc'), $i);
                $ticketDataModel = new Ticketdata($ticketDataCollection->offsetGet($i));
                $ticketDataModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                try {
                    if (static::$output) {
                        print_r($ticketDataCollection);
                    } else {
                        $ticketDataModel->saveOrFail();
                    }
//                $ticketDataModel->saveOrFail();
                } catch (\Exception $e) {
                    print_r($e->getMessage());
                    print_r($ticketDataModel);
                    die;
                }

                if (!empty($ticketDataATCCOllection->offsetGet('old_base_fare')) && $ticketDataATCCOllection->offsetGet('old_base_fare_currency') != '') {
                    $ticketDataATCCOllection->put('ticketdata_id', $ticketDataModel->id);
                    $ticketDataATCCOllection->put('pnr_id', $identCollection->offsetGet('id'));
                    $ticketDataATCModel = new TicketdataATC($ticketDataATCCOllection->toArray());
                    $ticketDataATCModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                    $ticketDataATCModel->saveOrFail();
                }
            }
            if (!empty(($priceCollection->offsetGet('tax_amount')))) {
                foreach ($priceCollection->offsetGet('tax_amount') as $single) {
                    $single['pnr_id'] = $identCollection->offsetGet('id');
                    $single['ticketdata_id'] = $ticketDataModel->id;
                    $ticketDataTaxesModel = new TicketdataTaxes($single);
                    $ticketDataTaxesModel->setConnection('pgsql_crs_'.DbHandler::$agent);
                    if (static::$output) {
                        print_r($priceCollection);
                    } else {
                        $ticketDataTaxesModel->saveOrFail();
                    }
//                    $ticketDataTaxesModel->saveOrFail();
                }
            }

        }
    }
}