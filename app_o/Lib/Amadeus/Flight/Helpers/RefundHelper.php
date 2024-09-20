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
use App\Lib\Amadeus\Flight\Collections\RefundCollection;
use App\Lib\Amadeus\Flight\DbHandler;
use App\Ticketdata;
use App\TicketdataRefunds;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Ident;
use App\Participants;
use App\ProcessedFiles;
use App\Segments;
use App\TicketdataATC;
use App\TicketdataTaxes;
use Illuminate\Support\Facades\DB;

/**
 * Class RefundHelper
 * @package App\Lib\Amadeus\Flight\Helpers
 */
class RefundHelper extends DbHandler
{

    /**
     * @var bool
     */
    public $duplication = true;
    public $notFound = true;

    /**
     * RefundHelper constructor.
     * @param IdentCollection $identCollection
     * @param ParticipantsCollection $participantsCollection
     * @param RefundCollection $refundCollection
     * @param $agent
     * @param $file
     * @throws \Exception
     */
    public function __construct(IdentCollection &$identCollection,
                                ParticipantsCollection &$participantsCollection, RefundCollection &$refundCollection,
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {


        //check first if we have this ticket number before
        DbHandler::$agent = $agent;
        $ticketNumber = $refundCollection->offsetGet('ticket_number');
        $ticketNumber = matchAirlineAndTicketNumberOnly($ticketNumber);

//        $sql ="SELECT EXISTS (select pnr_id from ticketdata_refunds where
//                ticket_number like '%$ticketNumber%' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))" ;
        $sql ="SELECT EXISTS (select pnr_id from ticketdata_refunds where 
                ticket_number like '%$ticketNumber%'  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1') )" ;
        $ticketsFound =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        //we need to check if this refund is cancel void refund
        //so we need to check if there is a void refund exists or not
        $f = fopen($file, 'r');
        $line = fgets($f);
        $line2 = fgets($f);
        fclose($f);

        //we need to check if file is retransmitted in second line
        if(strpos($line2,'AMDR') === false) {
            $sql = "SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%'  and ticket_type=13 and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1') )";
            $voidRefundFound = DB::connection('pgsql_crs_' . DbHandler::$agent)->select($sql);

            //if void refund does exist we can proceed to use handle the cancel void refund type

            if (isset($voidRefundFound[0]) && $voidRefundFound[0]->exists == true) {
                //we need to overwrite this value
                $ticketsFound[0]->exists = false;
            }
        }

        if (isset($ticketsFound[0]) &&  $ticketsFound[0]->exists == false) {


            //it's not exist so we can continue to import it


            $explode = explode('-', $ticketNumber);
            $sql = "select id,pnr_id,agent,valid_carrier,journey_from_date,journey_till_date from ident where 
                 agent = '$agent' and crs_id = '1' and id in 
                (select pnr_id from ticketdata where  number like '%$explode[1]%' ) ";
            $issueTicket =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

            if (count($issueTicket)) {

                $identCollection->offsetSet('pnr_id', $issueTicket[0]->pnr_id);
                $identCollection->offsetSet('valid_carrier', $issueTicket[0]->valid_carrier);
                $identCollection->offsetSet('journey_from_date', $issueTicket[0]->journey_from_date);
                $identCollection->offsetSet('journey_till_date', $issueTicket[0]->journey_till_date);

                $this->notFound = false;

                $id = $issueTicket[0]->id;
                $sql = "select * from segments where pnr_id = $id order by  id asc ";
                $res = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);
                $refundSegmentsIds = $refundCollection->get('refund_segments');

                if(count($refundSegmentsIds) && count($res)) {
                    $refundSegments = [];
                    $segmentsCollection = new SegmentsCollection();
                    foreach ($refundSegmentsIds as $singleItem) {
                        if(!isset($res[$singleItem - 1])){
                            $refundSegments[] = (array)$res[$singleItem - count($res)];
                            $segment = (array)$res[$singleItem - count($res)];
                            unset($segment['id']);
                            $segment['pnr_id'] = '';
                            $segmentsCollection->put($singleItem - count($res), $segment);
                        }else {
                            $refundSegments[] = (array)$res[$singleItem - 1];
                            $segment = (array)$res[$singleItem - 1];
                            unset($segment['id']);
                            $segment['pnr_id'] = '';
                            $segmentsCollection->put($singleItem - 1, $segment);
                        }
                    }
                }
                //print_r($segmentsCollection->toArray());die;
                $refundCollection->offsetUnset('refund_segments');

            }else{
                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('NO_EMD_OR_ISSUE_TO_REFUND '."TKT: $ticketNumber ");
                return false;
            }

            $this->fillIdent($identCollection, $agent);

            $this->fillParticipants($participantsCollection, $refundCollection);

            DB::connection('pgsql_crs_'.$agent)->beginTransaction();
            try {
                $this->saveIdent($identCollection);
                if(isset($segmentsCollection)) {
                    $this->saveSegments($segmentsCollection, $identCollection);
                }
                $this->saveRefundTicketDataAndParticipants($refundCollection, $participantsCollection, $identCollection);
                $this->saveProcessedFile($identCollection, $file, $agent);
                $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
                $this->success($file, $agent);
                DB::connection('pgsql_crs_'.$agent)->commit();
            }catch(\Exception $e)
            {
                DbHandler::setFileChecksum($file,$agent,'');
                DB::connection('pgsql_crs_'.$agent)->rollBack();
                throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
            }

        }else {
            //var_dump('zzzzzzz');die;
            DbHandler::setFileChecksum($file,$agent,'Ignore');
            renameFile($file,'ignore');
            throw new \Exception('Duplicated_REFUND_ticket '.json_encode($refundCollection));
            return false;
        }

    }


    /**
     * @param IdentCollection $identCollection
     * @param $agent
     * @throws \Exception
     */
    private function fillIdent(IdentCollection &$identCollection, $agent)
    {
        $identCollection->put('agent', $agent);
        $identCollection->put('crs_id', '1');


        $booking_date = $identCollection->offsetGet('booking_date');
        //$identCollection->put('booking_date', $booking_date);
        $date = \DateTime::createFromFormat('ymd', $booking_date);
        $identCollection->put('booking_date', $date->format('Y-m-d'));
    }


    /**
     * @param ParticipantsCollection $participantsCollection
     * @param RefundCollection $refundCollection
     * @throws \Exception
     */
    private function fillParticipants(ParticipantsCollection &$participantsCollection, RefundCollection $refundCollection)
    {
        $participantsCollection->putByIndex('price', $refundCollection->offsetGet('refund_total'), 0);
    }


    public function saveRefundTicketDataAndParticipants(RefundCollection &$refundCollection,
                                                        ParticipantsCollection &$participantsCollection,
                                                        IdentCollection &$identCollection)
    {

        $refundCollection->offsetSet('pnr_id', $identCollection->offsetGet('id'));
        $participantsCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), 0);

        $participantsModel = new Participants($participantsCollection->offsetGet(0));
        $participantsModel->setConnection('pgsql_crs_'.DbHandler::$agent);
        $participantsCollection->putByIndex('id', $participantsModel->id, 0);

        if (static::$output) {
            print_r($participantsModel->toArray());
        } else {
            $participantsModel->saveOrFail();
        }

        $ticketType = '3';
        if ($identCollection->offsetGet('version') == 14) {
            $ticketType = '14';
        }


        $ticketDataModel = new Ticketdata(
            [
                'number' => $refundCollection->offsetGet('ticket_number'),
                'participants_id' => $participantsModel->id,
                'ticket_type' => $ticketType,
                'pnr_id' => $identCollection->offsetGet('id'),
                'orig_pnr' => $identCollection->offsetGet('pnr_id'),
                'fare_amount' => $refundCollection->offsetGet('refund_total'),
                'fare_currency' => $refundCollection->offsetGet('currency'),
                'fop' => $refundCollection->offsetGet('fop'),
                'name' => $participantsCollection->offsetGetByIndex('name',0)
            ]
        );
        $ticketDataModel->setConnection('pgsql_crs_'.DbHandler::$agent);
        $refundCollection->offsetSet('orig_pnr',$identCollection->offsetGet('pnr_id'));

        $refundCollection->offsetUnset('fop');

        if (static::$output) {
            print_r($ticketDataModel->toArray());
        } else {
            $ticketDataModel->saveOrFail();
        }
        $ticketDataRefundsModel = new TicketdataRefunds($refundCollection->toArray());
        $ticketDataRefundsModel->setConnection('pgsql_crs_'.DbHandler::$agent);
        try {
            if (static::$output) {
                print_r($ticketDataRefundsModel->toArray());
            } else {
                $ticketDataRefundsModel->saveOrFail();
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            print_r($ticketDataRefundsModel);
            die;
        }


    }


}