<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/23/19
 * Time: 11:40 AM
 */

namespace App\Lib\Amadeus\Flight\Parsers;


use App\EmdData;
use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\RefundCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use Matomo\Ini\IniReader;

/**
 * Class RefundParser
 * @package App\Lib\Amadeus\Flight\Parsers
 */
class RefundParser
{
    /**
     * @var int
     */
    private $ticketCounter = -1;

    /**
     * @var int
     */
    private $participantCounter = -1;

    /**
     * @var array
     */
    private $invoiceRemarks = [
        ';MANAGEMENT FEES',
        ';DISCOUNT',
        ';MARKUP',
        ';DIP MARKUP',
        'RIFFILE'
    ];

    /**
     * @param $file
     * @param $agent
     * @param IdentCollection $identCollection
     * @param TicketDataCollection $ticketDataCollection
     * @param PriceCollection $priceCollection
     * @param ParticipantsCollection $participantsCollection
     * @param InvoiceRemarksCollection $invoiceRemarksCollection
     * @param CustomRemarksCollection $customRemarksCollection
     * @param RefundCollection $refundCollection
     * @throws \Matomo\Ini\IniReadingException
     */
    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection, RefundCollection &$refundCollection)
    {
        $hfile = fopen($file, "r");
        while (!@feof($hfile)) {


            $singleLine = @fgets($hfile);

            $threeCharsTag = substr($singleLine, 0, 3);
            $twoCharsTag = substr($singleLine, 0, 2);
            if ($threeCharsTag == 'MUC') {
                $identCollection->put('crs_id','1');
                $identCollection->put('pnr_id', matchPNR($singleLine));
                $identCollection->put('total_pnr_passengers', matchTotalPassengerInPNR($singleLine));
                $identCollection->put('office_id', matchBookingOfficeId($singleLine));
                $identCollection->put('tktoffice_id', matchTicketingOfficeId($singleLine));
                $identCollection->put('booking_iata', matchBookingIATA($singleLine));
                $identCollection->put('ticketing_iata', matchTicketingIATA($singleLine));
                $identCollection->put('isdomestic', 'false');
            }

            if ($threeCharsTag == 'AMD') {
                $explode = explode(';', $singleLine);
                if (isset($explode[2]) && !empty($explode[2])) {
                    preg_match('/VOID([\d]{2}[A-Z]{3})/', $explode[2], $matches);
                    if (isset($matches[1]) && !empty($matches[1])) {
                        $identCollection->put('booking_date', $matches[1]);
                    }
                }
            }

            if ($twoCharsTag == 'C-') {
                $identCollection->put('owner_id', '0');
                $identCollection->put('affiliate', '0');

                $identCollection->put('ticketing_sine', matchPNRTicketingSign($singleLine));

                $ownerData = getOwnerId(matchPNRTicketingSign($singleLine), $agent,$identCollection->get('tktoffice_id'),$identCollection->get('office_id'));
                if(isset($ownerData['ownerId']))
                {
                    $identCollection->put('owner_id',$ownerData['ownerId']);
                    $identCollection->put('affiliate',$ownerData['affiliate']);
                }
                $identCollection->put('booking_sine', matchPNRCreatorSign($singleLine));
            }

            if ($twoCharsTag == 'D-') {
                $identCollection->put('booking_date', matchPNRCreationDate($singleLine));
            }

            if ($threeCharsTag == 'RFD') {
                $explode = explode(';', $singleLine);
                $refundCollection->put('source', substr($explode[0], 3, 1));
                $refundCollection->put('refund_date', $explode[1]);
                $refundCollection->put('domestic_flag', $explode[2]);
                $refundCollection->put('currency', substr($explode[3], 0, 3));
                $refundCollection->put('currency', substr($explode[3], 0, 3));
                $refundCollection->put('fare_paid', substr($explode[3], 3, 11));
                $refundCollection->put('fare_used', !empty($explode[4]) ? $explode[4] : '0.00');
                $refundCollection->put('fare_refund', !empty($explode[5]) ? $explode[5] : '0.00');
                $refundCollection->put('net_refund',!empty($explode[7]) ? $explode[7] : '0.00');
                $refundCollection->put('cancel_fee', !empty($explode[8]) ? $explode[8] : '0.00');
                $refundCollection->put('cancel_fee_commission', !empty($explode[9]) ? $explode[9] : '0.00');
                $refundCollection->put('misc_fee', !empty($explode[10]) ? $explode[10] : '0.00');
                $refundCollection->put('tax_code', substr($explode[11], 0, 2));
                $refundCollection->put('tax_refund', substr($explode[11], 2, 11));
                $refundCollection->put('refund_total', $explode[12]*-1);
                $refundCollection->put('dep_date_first_seg', trim($explode[13]));
            }

            if ($twoCharsTag == 'I-') {

                $this->participantCounter++;

                $participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);
            }


            if ($twoCharsTag == 'R-' ) {

                //reset it in case on new ticket
                $this->ticketDataCollectionUpdateIndexArray = [];

                $this->ticketCounter++;

                $this->ticketDataCollectionUpdateIndexArray[] = $this->ticketCounter;

                $explode = explode('-', $singleLine);
                if (isset($explode[3])) {
                    print_r($explode);
                    die;
                }

                $ticketNumber = matchEmdAirlineAndTicketNumber($singleLine);

                if(empty($ticketNumber)) continue;

                $ticketNumber = 'T-K'.$ticketNumber;
                $refundCollection->put('ticket_number', $ticketNumber);

            }

            if($threeCharsTag == 'TBS'){
                $explode = explode('-',$singleLine);
                if(!empty($explode[1]) ){

                    $refundedSegments = [];
                    for($i=0;$i<strlen($explode[1]);$i++) {
                        $segmentNum = substr($explode[1], $i, 1);
                        if($segmentNum != 0){
                            $refundedSegments[] = $segmentNum;

                        }
                    }
                    $refundCollection->put('refund_segments',$refundedSegments);
                }
            }


            if ($twoCharsTag == 'FP') {

                $refundCollection->put('fop', trim($singleLine));

            }

            if($threeCharsTag == 'MFP')
            {
                $identCollection->put('version',14);
                $refundCollection->put('fop', trim($singleLine));
            }

            if ($threeCharsTag == 'RIS' || $threeCharsTag == 'RIF') {

                foreach ($this->invoiceRemarks as $singleRemark) {
                    if (strpos($singleLine, $singleRemark) !== false) {
                        $this->invoiceRemarksCounter++;
                        if ($threeCharsTag == 'RIF') {
                            $invoiceRemarksCollection->putByIndex('remark', trim(str_replace($singleRemark, '', $singleLine)), $this->invoiceRemarksCounter);
                        } else {
                            $invoiceRemarksCollection->putByIndex('remark', trim($singleLine), $this->invoiceRemarksCounter);
                        }
                        if ($threeCharsTag != 'RIF') {
                            $invoiceRemarksCollection->putByIndex('currency', matchInvoiceCurrency($singleLine), $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('amount', matchInvoiceAmount($singleLine), $this->invoiceRemarksCounter);
                        }
                        $invoiceRemarksCollection->putByIndex('remark_type', str_replace(';', '', $singleRemark), $this->invoiceRemarksCounter);
                    }
                }
            }
            if ($twoCharsTag == 'RM' || strpos($singleLine,'RIFIDRF') !== false) {

                if (!isset($customRemarksObj)) {
                    $reader = new IniReader();
                    $ini = $reader->readFile(get_importer_ini_path());
                    $customRemarksObj = load_custom_remarks_class($ini, $agent);
                }

                if (isset($customRemarksObj) && $customRemarksObj) {
                    $customRemarksObj->parseRMLine($singleLine, $customRemarksCollection, $this->participantCounter);
                }

            }

            if ($threeCharsTag == 'END') {
//                print_r($ticketDataCollection->toArray());die;
//                print_r($refundCollection->toArray());
//                print_r($identCollection->toArray());
//                print_r($participantsCollection->toArray());die;
            }
        }
        fclose($hfile);
    }
}