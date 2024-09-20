<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/23/19
 * Time: 11:51 AM
 */

namespace App\Lib\Amadeus\Flight\Helpers;

use App\InsuranceData;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InsuranceDataCollection;
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
class InsuranceHelper extends DbHandler
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
                                ParticipantsCollection &$participantsCollection, InsuranceDataCollection &$insuranceDataCollection,
                                InvoiceRemarksCollection $invoiceRemarksCollection, CustomRemarksCollection $customRemarksCollection, $agent, $file)
    {



        $policyNumber = $insuranceDataCollection->getByIndex('policy_number',0);;



        $sql ="SELECT EXISTS (select pnr_id from insurance_data where 
                policy_number like '%$policyNumber%'  and pnr_id in (select id from ident where  agent = '$agent' and crs_id = '1') )" ;
        $ticketsFound = DB::select($sql);

        if (isset($ticketsFound[0]) &&  $ticketsFound[0]->exists == false) {


            $identCollection->put('journey_from_date',$insuranceDataCollection->getByIndex('depdate',0));
            $this->fillIdent($identCollection, $agent);

            $this->fillParticipants($participantsCollection, $insuranceDataCollection);

                DB::beginTransaction();
                try {
                    $identCollection->put('isdomestic','false');
                    //   print_r($identCollection->toArray());die;
                    $this->saveIdent($identCollection);
                    $this->saveInsuranceDataAndParticipants($insuranceDataCollection, $participantsCollection, $identCollection);
                    $this->saveProcessedFile($identCollection, $file, $agent);
                    $this->saveInvoiceRemarks($identCollection, $invoiceRemarksCollection);
                    $this->saveCustomRemarks($identCollection, $participantsCollection, $customRemarksCollection);
                    $this->success($file, $agent);
                    DB::commit();
                }catch(\Exception $e)
                {
                    DbHandler::setFileChecksum($file,$agent,'');
                    DB::rollBack();
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }

        }else {
//var_dump('zzzzzzz');die;
            DbHandler::setFileChecksum($file,$agent,'Ignore');

            throw new \Exception('Duplicated_REFUND_ticket');
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
    private function fillParticipants(ParticipantsCollection &$participantsCollection, InsuranceDataCollection $insuranceDataCollection)
    {
        foreach ($insuranceDataCollection->toArray() as $single){
            $identifier = $single['idnetifier'];
            $count = 0;
            $partKeys = [];
            foreach ($participantsCollection as $key=>$participant){
                if($participant['number'] == $identifier){
                    $count++;
                    $partKeys[] = $key;
                }
            }
            foreach ($partKeys as $key){
                $participantsCollection->putByIndex('price',$single['total_amount']/$count,$key);
            }
        }


    }


    public function saveInsuranceDataAndParticipants(InsuranceDataCollection &$insuranceDataCollection,
                                                        ParticipantsCollection &$participantsCollection,
                                                        IdentCollection &$identCollection)
    {

        foreach ($insuranceDataCollection as $key=>$singlePolicy){
            $insuranceDataCollection->putByIndex('pnr_id', $identCollection->offsetGet('id') ,$key);
            $partsIds = [];
            foreach ($participantsCollection as $keyPart=>$participant){
                if($participant['number'] == $singlePolicy['idnetifier']){
                    $participant['pnr_id'] = $identCollection->offsetGet('id');

                    $participantsModel = new Participants($participant);
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
                    $participantsCollection->putByIndex('id', $participantsModel->id, $keyPart);
                    $partsIds[] = $participantsModel->id;
                }
            }

            $singlePolicy['pnr_id']  =  $identCollection->offsetGet('id');
            $singlePolicy['participants_id'] = implode(';',$partsIds);

            $insuranceDataModel = new InsuranceData($singlePolicy);
            try {
                if (static::$output) {
                    print_r($insuranceDataModel->toArray());
                } else {
                    $insuranceDataModel->saveOrFail();
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__.' '.static::$file);
                die;
            }
        }

    }


}