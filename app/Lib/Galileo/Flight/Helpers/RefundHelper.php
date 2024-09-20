<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/23/19
 * Time: 11:51 AM
 */

namespace App\Lib\Galileo\Flight\Helpers;

use App\Lib\Galileo\Flight\Collections\IdentCollection;
use App\Lib\Galileo\Flight\Collections\ParticipantsCollection;
use App\Lib\Galileo\Flight\Collections\RefundCollection;
use App\Lib\Galileo\Flight\DbHandler;
use App\Ticketdata;
use App\TicketdataRefunds;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Galileo\Flight\Collections\CustomRemarksCollection;
use App\Lib\Galileo\Flight\Collections\EmdDataCollection;
use App\Lib\Galileo\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Galileo\Flight\Collections\PriceCollection;
use App\Lib\Galileo\Flight\Collections\SegmentsCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataATCCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataCollection;
use App\Ident;
use App\Participants;
use App\ProcessedFiles;
use App\Segments;
use App\TicketdataATC;
use App\TicketdataTaxes;
use Illuminate\Support\Facades\DB;
/**
 * Class RefundHelper
 * @package App\Lib\Sabre\Flight\Helpers
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
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, SegmentsCollection $segmentsCollection, $agent, $file)
    {

        //check first if we have this ticket number before
        DbHandler::$agent = $agent;
        $ticketNumber = $refundCollection->offsetGet('ticket_number');

        $sql ="SELECT EXISTS (select pnr_id from ticketdata_refunds where 
        ticket_number like '%$ticketNumber%'  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '14') )" ;
        $ticketsFound =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) &&  $ticketsFound[0]->exists == false) {


            //it's not exist so we can continue to import it
            $issueTicket =  DB::connection("pgsql_crs_".$agent)->table('ticketdata')->where('number','like', '%' . $ticketNumber . '%')
                ->get();
            // $issueTicket = Ticketdata::where('number', 'like', '%' . $ticketNumber . '%')
            // ->get();

            if ($issueTicket->count()) {
                $identCollection->put('total_pnr_passengers',$participantsCollection->count());
                //cehck agent is exact
                $sql = "select * from ident where agent = '$agent' and id = ".$issueTicket->offsetGet(0)->pnr_id;
                $identFound =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);
                if(isset($identFound[0]) && !empty($identFound[0])) {
                    $identCollection->offsetSet('pnr_id', $issueTicket->offsetGet(0)->orig_pnr);

                    $this->notFound = false;
                }else{
                    DbHandler::setFileChecksum($file,$agent,'');
                    throw new \Exception('NO_EMD_OR_ISSUE_TO_REFUND '."TKT: $ticketNumber");
                    return false;
                }

            }else{
                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('NO_EMD_OR_ISSUE_TO_REFUND '."TKT: $ticketNumber");
                return false;
            }

            $this->fillIdent($identCollection, $agent,$segmentsCollection);


            DB::connection('pgsql_crs_'.$agent)->beginTransaction();
            try {
                $this->saveIdent($identCollection);
                $this->saveTicketDataAndParticipants($refundCollection, $participantsCollection, $identCollection);
                $this->saveProcessedFile($identCollection, $file, $agent);
                $this->success($file, $agent);
                DB::connection('pgsql_crs_'.$agent)->commit();
            }catch(\Exception $e)
            {
                DbHandler::setFileChecksum($file,$agent,'');
                DB::connection('pgsql_crs_'.$agent)->rollBack();
                throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
            }

        }else {

            DbHandler::setFileChecksum($file,$agent,'Ignore');

            //throw new \Exception('Duplicated_REFUND_ticket');
            return false;
        }

    }


    /**
     * @param IdentCollection $identCollection
     * @param $agent
     * @throws \Exception
     */
    private function fillIdent(IdentCollection &$identCollection, $agent,$segmentsCollection)
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
    }






    public static function saveTicketDataAndParticipants(RefundCollection &$refundCollection,
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