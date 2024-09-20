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
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;
use App\Lib\Sabre\Flight\DbHandler;
use App\Ticketdata;
use App\TicketdataRefunds;
use Illuminate\Support\Facades\DB;
use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\EmdDataCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataATCCollection;
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

        $ticketType = $ticketDataArray[0]['ticket_type'];
//        $sql = "SELECT EXISTS (select pnr_id from ticketdata where
//                number like '%$ticketNumber%' and ticket_type = '$ticketType' and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))";
        $sql ="SELECT EXISTS (select pnr_id from ticketdata where 
                number like '%$ticketNumber%' and ticket_type in(2,12,13)  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1'))" ;
        $ticketsFound =  DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);

        if (isset($ticketsFound[0]) && false == $ticketsFound[0]->exists) {


            if ($identCollection->offsetGet('version') == 2) {
                $issueTicket =  DB::connection("pgsql_crs_".$agent)->table('ticketdata')->where('number','like', '%' . $ticketNumber . '%')
                    ->get();
               // $issueTicket = Ticketdata::where('number', 'like', '%' . $ticketNumber . '%')
                   //->get();
            } else {
                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('UKNOWN_VOID_TYPE');
                return false;
            }

            if ($issueTicket->count() ) {
                $sql = "select * from ident where agent = '$agent' and id = ".$issueTicket->offsetGet(0)->pnr_id;
                $identFound = DB::connection('pgsql_crs_'.DbHandler::$agent)->select($sql);
                if(isset($identFound[0]) && !empty($identFound[0])) {
                    $identCollection->offsetSet('pnr_id', $issueTicket->offsetGet(0)->orig_pnr);
                    $identModel = DB::connection('pgsql_crs_'.DbHandler::$agent)->select("select * from ident  where id = ".$issueTicket->offsetGet(0)->pnr_id);

                    //filling ident from previous ticket data
                    $identCollection->put('agent',$identModel[0]->agent);
                    $identCollection->put('office_id',$identModel[0]->office_id);
                    $identCollection->put('tktoffice_id',$identModel[0]->tktoffice_id);
                    $identCollection->put('ticketing_iata',$identModel[0]->ticketing_iata);

                    $identCollection->put('owner_id',$identModel[0]->owner_id);
                    $identCollection->put('affiliate',$identModel[0]->affiliate);
                    $identCollection->put('ticketing_sine',$identModel[0]->ticketing_sine);
                    $identCollection->put('booking_sine',$identModel[0]->booking_sine);
                    $identCollection->put('isdomestic',$identModel[0]->isdomestic);
                    $identCollection->put('valid_carrier',$identModel[0]->valid_carrier);
                    $identCollection->put('total_pnr_passengers',$identModel[0]->total_pnr_passengers);

                    $identCollection->put('booking_date',$identModel[0]->booking_date);


                    //filling participants
                    $participantsModel = DB::connection('pgsql_crs_'.DbHandler::$agent)->select("select * from participants  where id = ".$issueTicket->offsetGet(0)->participants_id);
                    $participantsCollection->putByIndex('first_name', $participantsModel[0]->first_name,0);
                    $participantsCollection->putByIndex('last_name', $participantsModel[0]->last_name,0);
                    $participantsCollection->putByIndex('title', $participantsModel[0]->title,0);

                    //filling ticketdata
                    $ticketDataCollection->putByIndex('isdomestic', $issueTicket[0]->isdomestic, 0);
                    $ticketDataCollection->putByIndex('date', $issueTicket[0]->date, 0);
                    $ticketDataCollection->putByIndex('iatanr', $issueTicket[0]->iatanr, 0);
                    $ticketDataCollection->putByIndex('orig_pnr', $issueTicket[0]->orig_pnr, 0);
                    $ticketDataCollection->putByIndex('valid_carrier', $issueTicket[0]->valid_carrier, 0);
                    $ticketDataCollection->putByIndex('iatanr_booking_agent', $issueTicket[0]->iatanr_booking_agent, 0);
                    $ticketDataCollection->putByIndex('name', $issueTicket[0]->name, 0);

                    $ticketDataCollection->putByIndex('fare_amount', $issueTicket[0]->fare_amount, 0);
                    $ticketDataCollection->putByIndex('fare_currency', $issueTicket[0]->fare_currency, 0);
                    $ticketDataCollection->putByIndex('tax_amount', $issueTicket[0]->tax_amount, 0);
                    $ticketDataCollection->putByIndex('tax_currency', $issueTicket[0]->tax_currency, 0);
                    $ticketDataCollection->putByIndex('equiv_amount', $issueTicket[0]->equiv_amount, 0);
                    $ticketDataCollection->putByIndex('equiv_currency', $issueTicket[0]->equiv_currency, 0);


                    $ticketDataCollection->putByIndex('conjunctive_flag', $issueTicket[0]->conjunctive_flag, 0);
                    $ticketDataCollection->putByIndex('tour_operator', $issueTicket[0]->tour_operator, 0);
                    $ticketDataCollection->putByIndex('commission_amount', '0.00', 0);
                    $ticketDataCollection->putByIndex('fop', $issueTicket[0]->fop, 0);
                    $ticketDataCollection->putByIndex('valid_carrier', $issueTicket[0]->valid_carrier, 0);
                    $ticketDataCollection->putByIndex('commission_rate', $issueTicket[0]->commission_rate, 0);
                    $ticketDataCollection->putByIndex('commission_vat', $issueTicket[0]->commission_vat, 0);
                    $ticketDataCollection->putByIndex('company_own_cc', $issueTicket[0]->company_own_cc, 0);
                    $ticketDataCollection->putByIndex('partially_paid', $issueTicket[0]->partially_paid, 0);






                    $this->notFound = false;

                }else{
                    DbHandler::setFileChecksum($file,$agent,'');
                    throw new \Exception('NO_EMD_OR_ISSUE_OR_REFUND_TO_VOID ' ."TKT: $ticketNumber");
                    return false;
                }

                //we need to gent original pnr from issuance ticket
               /* if ($issueTicket->count() > 1) {
//                    var_dump($issueTicket->toArray());die;
                } else {
                    $identCollection->offsetSet('pnr_id', $issueTicket->offsetGet(0)->orig_pnr);

                    $this->notFound = false;
                }*/

            }else{

                DbHandler::setFileChecksum($file,$agent,'');
                throw new \Exception('NO_EMD_OR_ISSUE_OR_REFUND_TO_VOID '."TKT: $ticketNumber");
                return false;
            }



            DB::connection('pgsql_crs_'.$agent)->beginTransaction();
            try {

                $this->saveIdent($identCollection);
                $this->saveTicketDataAndParticipants($ticketDataCollection, $participantsCollection, $identCollection, $priceCollection);
                $this->saveProcessedFile($identCollection, $file, $agent);
                $this->success($file, $agent);
                DB::connection('pgsql_crs_'.$agent)->commit();

            } catch (\Exception $e) {
                DbHandler::setFileChecksum($file,$agent,'');
                DB::connection('pgsql_crs_'.$agent)->rollBack();
                throw new \Exception($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
//            var_dump('zzzzzzz');die;
            DbHandler::setFileChecksum($file,$agent,'Ignore');


            //throw new \Exception('Duplicated_VOID_ticket');
            return false;
        }

//        throw new \Exception(sprintf('Duplication ticket found for agent %s, file contents: %s',$agent,file_get_contents($file)));

    }



    public static function saveTicketDataAndParticipants(TicketDataCollection &$ticketDataCollection,
                                                         ParticipantsCollection &$participantsCollection,
                                                         IdentCollection &$identCollection,
                                                         PriceCollection &$priceCollection)
    {

        $ticketsCounter = $ticketDataCollection->count();

        for ($i = 0; $i < $ticketsCounter; $i++) {
            $ticketDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
            if ($ticketDataCollection->getByIndex('conjunctive_flag', $i) == false) {


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