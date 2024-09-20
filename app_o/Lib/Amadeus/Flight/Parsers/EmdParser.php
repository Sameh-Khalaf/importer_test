<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/16/19
 * Time: 11:37 AM
 */

namespace App\Lib\Amadeus\Flight\Parsers;

use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use Matomo\Ini\IniReader;


class EmdParser
{
    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarksCounter = -1;


    private $icwTickets = [];

    private $invoiceRemarks = [
        ';MANAGEMENT FEES',
        ';DISCOUNT',
        ';MARKUP',
        ';DIP MARKUP',
        'RIFFILE'
    ];

    private $emdOnly = false;

    private $ownCC = false;

    public function parse($file,$agent,IdentCollection &$identCollection,TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection,EmdDataCollection &$emdDataCollection)
    {
        $hfile = fopen($file, "r");
        while (!@feof($hfile)) {


            $singleLine = @fgets($hfile);

            $threeCharsTag = substr($singleLine, 0, 3);
            $twoCharsTag = substr($singleLine, 0, 2);
            if ($threeCharsTag == 'AIR') {
                $ticketType = matchTicketType($singleLine);
                if($ticketType == '7D'){
                    $this->emdOnly = true;
                }
            }

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

            if ($twoCharsTag == 'H-' && strpos($singleLine, 'VOID') === false) {
                if (!isset($segmentCounter)) {
                    $segmentCounter = 0;
                }

                $segmentsCollection->putByIndex('dep_city', matchSegmnetOriginAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_date', matchSegmentsDepartureDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_time', matchSegmentsDepartureTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city', matchSegmentsDestinationAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_date', matchSegmentsArrivalDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_time', matchSegmentsArrivalTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('flight_no', matchSegmentsFlightNumber($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_service', matchSegmentsClassOfService($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_booking', matchSegmentsClassOfBooking($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('status', matchSegmentsStatusCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('segtype', 'AIR', $segmentCounter);
                $segmentsCollection->putByIndex('ticketed', true, $segmentCounter);
                $segmentsCollection->putByIndex('filekey', $identCollection->offsetGet('pnr_id'), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('stop_over', matchSegmnetStopOverIndicator($singleLine), $segmentCounter);

                $segmentCounter++;
                $priceCollection->put('emd_flag',true);
            }

            if ($twoCharsTag == 'U-' && strpos($singleLine, 'VOID') === false && $this->emdOnly) {
                if (!isset($segmentCounter)) {
                    $segmentCounter = 0;
                }

                $segmentsCollection->putByIndex('dep_city', matchSegmnetOriginAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_date', matchSegmentsDepartureDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_time', matchSegmentsDepartureTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city', matchSegmentsDestinationAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_date', matchSegmentsArrivalDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_time', matchSegmentsArrivalTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('flight_no', matchSegmentsFlightNumber($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_service', matchSegmentsClassOfService($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_booking', matchSegmentsClassOfBooking($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('status', matchSegmentsStatusCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('segtype', 'AIR', $segmentCounter);
                $segmentsCollection->putByIndex('ticketed', true, $segmentCounter);
                $segmentsCollection->putByIndex('filekey', $identCollection->offsetGet('pnr_id'), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('stop_over', matchSegmnetStopOverIndicator($singleLine), $segmentCounter);
                $segmentCounter++;
                $priceCollection->put('emd_flag',true);
            }

            if($twoCharsTag == 'Y-' && strpos($singleLine, 'VOID') === false)
            {
                if(isset($segmentCounter)) {
                    $explode = explode(';', $singleLine);
                    $segmentsCollection->putByIndex('equipment', $explode[11], $segmentCounter);
                }
            }

            if($threeCharsTag == 'EMD')
            {
                if(!isset($emdCounter))
                    $emdCounter = 0;

                $explode = explode(';',$singleLine);
                $identCollection->put('valid_carrier', matchEmdAirlineCode($explode[1]));
                $emdDataCollection->putByIndex('tsm_identifier',trim($explode[5]),$emdCounter);
                $emdDataCollection->putByIndex('airline_code',matchEmdAirlineCode($explode[1]),$emdCounter);
                $emdDataCollection->putByIndex('airline_number',matchEmdAirlineNumber($explode[2]),$emdCounter);
                $emdDataCollection->putByIndex('airline_name',matchEmdAirlineName($explode[3]),$emdCounter);
                $emdDataCollection->putByIndex('creation_date',trim($explode[4]),$emdCounter);
                $emdDataCollection->putByIndex('marketing_airline_code',trim($explode[7]),$emdCounter);
                $emdDataCollection->putByIndex('operating_airline_code',trim($explode[8]),$emdCounter);
                $emdDataCollection->putByIndex('carrier_fee_owner',trim($explode[9]),$emdCounter);
                $emdDataCollection->putByIndex('origin_city',trim($explode[10]),$emdCounter);
                $emdDataCollection->putByIndex('destination_city',trim($explode[11]),$emdCounter);
                $emdDataCollection->putByIndex('to_carrier',trim($explode[12]),$emdCounter);
                $emdDataCollection->putByIndex('at_location',trim($explode[13]),$emdCounter);
                $emdDataCollection->putByIndex('emd_type',trim($explode[14]),$emdCounter);
                $emdDataCollection->putByIndex('reason_issuance_code',trim($explode[15]),$emdCounter);
                $emdDataCollection->putByIndex('reason_issuance_code_desc',trim($explode[16]),$emdCounter);
                $emdDataCollection->putByIndex('reason_issuance_sub_code',trim($explode[17]),$emdCounter);
                $emdDataCollection->putByIndex('reason_issuance_sub_code_desc',trim($explode[18]),$emdCounter);
                $emdDataCollection->putByIndex('remarks',trim($explode[19]),$emdCounter);
                $emdDataCollection->putByIndex('service_remarks',trim($explode[20]),$emdCounter);
                $emdDataCollection->putByIndex('not_valid_before_date',trim($explode[21]),$emdCounter);
                $emdDataCollection->putByIndex('not_valid_after_date',trim($explode[22]),$emdCounter);
                $emdDataCollection->putByIndex('coupon_value',trim($explode[26]),$emdCounter);
                $emdDataCollection->putByIndex('issue_identifier',trim($explode[27]),$emdCounter);

                $emdDataCollection->putByIndex('fare_currency',matchEmdAmountCurrency($explode[28]),$emdCounter);
                $emdDataCollection->putByIndex('fare_amount',matchEmdAmount($explode[28]),$emdCounter);

                $emdDataCollection->putByIndex('inclusive_tax_included',trim($explode[29]),$emdCounter);

                $emdDataCollection->putByIndex('equiv_currency',matchEmdAmountCurrency($explode[30]),$emdCounter);
                $emdDataCollection->putByIndex('equiv_amount',matchEmdAmount($explode[30]),$emdCounter);

                $emdDataCollection->putByIndex('refund_currency',matchEmdAmountCurrency($explode[32]),$emdCounter);
                $emdDataCollection->putByIndex('refund_amount',matchEmdAmount($explode[32]),$emdCounter);

                $emdDataCollection->putByIndex('total_currency',matchEmdAmountCurrency($explode[132]),$emdCounter);
                $emdDataCollection->putByIndex('total_amount',matchEmdAmount($explode[132]),$emdCounter);

                //dont overwrite price if 2 EMD exist
                if($priceCollection->get('fare_currency') != ''){
                    $oldAmount = $priceCollection->get('fare_amount') ;
                    $newAmount = matchEmdAmount($explode[28]);
                    $priceCollection->put('fare_amount', $newAmount + $oldAmount);

                    $oldEquivAmount = $priceCollection->get('equiv_amount') ;
//                    var_dump($oldEquivAmount);die;
                    $newEquivAmout = matchEmdAmount($explode[30]);
                    $priceCollection->put('equiv_amount', $oldEquivAmount + $newEquivAmout);

                    $oldTotalAmount = $priceCollection->get('total_amount');
                    $newTotalAmount = matchEmdAmount($explode[132]);

                    $priceCollection->put('total_amount', $oldTotalAmount + $newTotalAmount);




                }else {

                    $priceCollection->put('fare_currency', matchEmdAmountCurrency($explode[28]));
                    $priceCollection->put('fare_amount', matchEmdAmount($explode[28]));
                    $priceCollection->put('equiv_currency', matchEmdAmountCurrency($explode[30]));
                    $priceCollection->put('equiv_amount', matchEmdAmount($explode[30]));
                    $priceCollection->put('total_currency', matchEmdAmountCurrency($explode[132]));
                    $priceCollection->put('total_amount', matchEmdAmount($explode[132]));

                    $priceCollection->put('tax_amount', 0);
                }

//                $fareAmount = matchEmdAmount($explode[28]);
//                $totalAmount = matchEmdAmount($explode[132]);
//                if((int)$totalAmount !== 0) {
//                    $totalTaxAmount = $totalAmount - $fareAmount;
//                    $priceCollection->put('tax_amount', $totalTaxAmount);
////                    var_dump($totalAmount,$fareAmount);die;
//                }

                $emdCounter++;
            }

            if($threeCharsTag == 'ICW')
            {

                $ICWNumber = matchEMDICW($singleLine);
                $explode = explode(';',$singleLine);
                $ICWIdentifier = $explode[1];
                $this->icwTickets[$ICWIdentifier]=[
                    'icwNumber'=>$ICWNumber,
                    ];
            }

            if ($twoCharsTag == 'I-' && strpos($singleLine,'I-GRP') === false) {
                $this->participantCounter++;
                $participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);
            }

            if($threeCharsTag == 'TMC')
            {

                $this->ticketCounter++;

                $ticketNumber = matchEmdAirlineAndTicketNumber($singleLine);

                if(empty($ticketNumber)) continue;

                $ticketNumber = 'T-K'.$ticketNumber;
                $explode = explode(';',$singleLine);


                //get fare amount from $emdDataCollection by tsm_identifier
                $fareAmount = '0.00';
                $fareCurrency = '';
                foreach ($emdDataCollection->toArray() as $single){
                    if($single['tsm_identifier'] == trim($explode[2])){
                        $fareAmount = $single['total_amount'];
                        $fareCurrency = $single['total_currency'];
                        break;
                    }
                }
                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);

                $ticketDataCollection->putByIndex('emd_identifier', trim($explode[2]), $this->ticketCounter);
                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);
                $ticketDataCollection->putByIndex('fare_amount', $fareAmount, $this->ticketCounter);
                $ticketDataCollection->putByIndex('fare_currency', $fareCurrency, $this->ticketCounter);
                $ticketDataCollection->putByIndex('tax_amount',$priceCollection->offsetGet('tax_amount'),$this->ticketCounter);
                $ticketDataCollection->putByIndex('tax_currency',$priceCollection->offsetGet('fare_currency'),$this->ticketCounter);
                $ticketDataCollection->putByIndex('equiv_amount', $priceCollection->offsetGet('equiv_amount'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('equiv_currency', $priceCollection->offsetGet('equiv_currency'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('tour_operator',$identCollection->offsetGet('tktoffice_id'),$this->ticketCounter);

                if(isset($this->icwTickets[trim($explode[2])]) && !empty($this->icwTickets[trim($explode[2])]))
                {
                    $ticketDataCollection->putByIndex('original_number',$this->icwTickets[trim($explode[2])]['icwNumber'],$this->ticketCounter);
                }

                if($this->emdOnly){
                    if(!empty($this->icwTickets)){
                        reset($this->icwTickets);
                        $key = key($this->icwTickets);
                        if(!empty($this->icwTickets[$key]['icwNumber'])){
                            $ticketDataCollection->putByIndex('original_number',$this->icwTickets[$key]['icwNumber'],$this->ticketCounter);
                        }
                    }else {
                        $ticketDataCollection->putByIndex('original_number', '-', $this->ticketCounter);
                    }
                }

            }

            if($threeCharsTag == 'MFE')
            {

            }

            if($threeCharsTag == 'MFP')
            {
                if(!empty($priceCollection->get('form_of_payment'))){
                    $oldFop = $priceCollection->get('form_of_payment');
                    $priceCollection->put('form_of_payment', trim($singleLine).'-'.$oldFop);
                }else {
                    $priceCollection->put('form_of_payment', trim($singleLine));
                }
            }

            if ($threeCharsTag == 'AIT') {
                $identCollection->put('match_code', matchAccountNumber($singleLine));
            }

            if ($threeCharsTag == 'RIS' || $threeCharsTag == 'RIF') {

                foreach ($this->invoiceRemarks as $singleRemark) {
                    if(strpos($singleLine,$singleRemark) !== false){
                        $this->invoiceRemarksCounter++;
                        if($threeCharsTag == 'RIF')
                        {
                            $invoiceRemarksCollection->putByIndex('remark',trim(str_replace($singleRemark,'',$singleLine)),$this->invoiceRemarksCounter);
                        }else {
                            $invoiceRemarksCollection->putByIndex('remark', trim($singleLine), $this->invoiceRemarksCounter);
                        }
                        if($threeCharsTag != 'RIF') {
                            $invoiceRemarksCollection->putByIndex('currency', matchInvoiceCurrency($singleLine), $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('amount', matchInvoiceAmount($singleLine), $this->invoiceRemarksCounter);
                        }
                        $invoiceRemarksCollection->putByIndex('remark_type',str_replace(';','',$singleRemark),$this->invoiceRemarksCounter);
                    }
                }
            }


            if($twoCharsTag == 'RM'  || strpos($singleLine,'RIFIDRF') !== false)
            {

//                if(!defined('customRemarksClassLoaded')) {
//                    var_dump($file);
                if(!isset($customRemarksObj)) {
                    $reader = new IniReader();
                    $ini = $reader->readFile(get_importer_ini_path());
                    $customRemarksObj = load_custom_remarks_class($ini, $agent);
//                }
                }

                if(isset($customRemarksObj) && $customRemarksObj){
                    $customRemarksObj->parseRMLine($singleLine,$customRemarksCollection,$this->participantCounter,$this->ownCC);
                }

            }

            if($threeCharsTag == 'END')
            {
                $priceCollection->put('own_cc',$this->ownCC);
//                print_r($participantsCollection->toArray());die;
            }

        }
        fclose($hfile);
        
    }
}