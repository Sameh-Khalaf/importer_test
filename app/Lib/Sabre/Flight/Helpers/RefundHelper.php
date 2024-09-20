<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/23/19
 * Time: 11:51 AM
 */

namespace App\Lib\Sabre\Flight\Helpers;

use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Sabre\Flight\Collections\RefundCollection;
use App\Lib\Sabre\Flight\DbHandler;
use App\Ticketdata;
use App\TicketdataRefunds;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\EmdDataCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataATCCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;
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
                                ParticipantsCollection &$participantsCollection, TicketDataCollection &$ticketDataCollection,
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {


        //check first if we have this ticket number before
        DbHandler::$agent = $agent;
        $ticketNumber = $ticketDataCollection->getByIndex('number',0);

        $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and ticket_type='3' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '7') )" ;
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

            $this->fillIdent($identCollection, $agent);


            DB::connection('pgsql_crs_'.$agent)->beginTransaction();
                try {
                    $this->saveIdent($identCollection);
                    $this->saveTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection);
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
    private function fillIdent(IdentCollection &$identCollection, $agent)
    {
        $identCollection->put('agent', $agent);
        $identCollection->put('crs_id', '7');


        $booking_date = $identCollection->offsetGet('booking_date').date('y');
        $date = \DateTime::createFromFormat('dMy', $booking_date);
        $identCollection->put('booking_date', $date->format('Y-m-d'));
    }






    public static function saveTicketDataAndParticipants(TicketDataCollection &$ticketDataCollection,
                                                         ParticipantsCollection &$participantsCollection,
                                                         IdentCollection &$identCollection)
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