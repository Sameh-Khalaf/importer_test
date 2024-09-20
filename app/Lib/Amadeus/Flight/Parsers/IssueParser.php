<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/19/19
 * Time: 1:43 PM
 */

namespace App\Lib\Amadeus\Flight\Parsers;

use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use Matomo\Ini\IniReader;


class IssueParser
{

    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarksCounter = -1;

    //we need to know start position and end position to update ticketdata in case of
    //conjunctive tickets
    private $ticketDataCollectionUpdateIndexArray = [];

    private $invoiceRemarks = [
        ';MANAGEMENT FEES',
        ';DISCOUNT',
        ';MARKUP',
        ';DIP MARKUP',
        'RIFFILE'
    ];
    private $ownCC = false;

    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection)
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

            }

            if ($twoCharsTag == 'A-') {
                $identCollection->put('valid_carrier', matchValidAirline($singleLine));
            }

            if ($twoCharsTag == 'C-') {
                $identCollection->put('owner_id', '0');
                $identCollection->put('affiliate', '0');

                $identCollection->put('ticketing_sine', matchPNRTicketingSign($singleLine));
                $ownerData = getOwnerId(matchPNRTicketingSign($singleLine), $agent,$identCollection->get('tktoffice_id'),$identCollection->get('office_id'));
                if (isset($ownerData['ownerId'])) {
                    $identCollection->put('owner_id', $ownerData['ownerId']);
                    $identCollection->put('affiliate', $ownerData['affiliate']);
                }
                $identCollection->put('booking_sine', matchPNRCreatorSign($singleLine));
            }

            if ($twoCharsTag == 'D-') {
                $identCollection->put('booking_date', matchPNRCreationDate($singleLine));
            }

            if ($twoCharsTag == 'G-') {
                $identCollection->put('isdomestic', 'false');
                if (matchDomesticInternationalFlag($singleLine) != 'X') {
                    $identCollection->put('isdomestic', 'true');
                }
            }

            if ($twoCharsTag == 'H-' && strpos($singleLine, 'VOID') === false) {

                if (!isset($segmentCounter)) {
                    $segmentCounter = 0;
                }

                $segmentsCollection->putByIndex('dep_city', matchSegmnetOriginAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_city_name', matchSegmnetOriginCityName($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_date', matchSegmentsDepartureDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_time', matchSegmentsDepartureTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city', matchSegmentsDestinationAirportCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city_name', matchSegmentsDestinationCityName($singleLine), $segmentCounter);
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
//                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('stop_over', matchSegmnetStopOverIndicator($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('equipment', matchSegmentsEquipmentType($singleLine), $segmentCounter);
                $segmentCounter++;
            }

//            if($twoCharsTag == 'Y-' && strpos($singleLine, 'VOID') === false)
//            {
//
//
//                    $explode = explode(';', $singleLine);
//                    $segmentsCollection->putByIndex('equipment', $explode[11], $segmentCounter--);
//                print_r($segmentsCollection->toArray());die;
//            }

            if ($twoCharsTag == "K-") {
                if (strlen($singleLine) < 5) continue;

//                $fareType = matchFareType($singleLine);

                $priceCollection->put('fare_currency', matchBaseFareCurrencyCode($singleLine));
                $priceCollection->put('fare_amount', matchBaseFareAmount($singleLine));
                $priceCollection->put('equiv_currency', matchEquivelantFareCuurency($singleLine));
                $priceCollection->put('equiv_amount', matchEquivelantFareAmount($singleLine));
                $priceCollection->put('total_amount', matchTotalAmount($singleLine));
                $priceCollection->put('rate', matchBuySellRate($singleLine));
            }

            if ($threeCharsTag == 'KS-') {
                if (strlen($singleLine) < 5) continue;

//                $fareType = matchFareType($singleLine);

                $priceCollection->put('fare_currency', matchBaseFareCurrencyCode($singleLine));
                $priceCollection->put('fare_amount', matchBaseFareAmount($singleLine));
                $priceCollection->put('equiv_currency', matchEquivelantFareCuurency($singleLine));
                $priceCollection->put('equiv_amount', matchEquivelantFareAmount($singleLine));
                $priceCollection->put('total_amount', matchTotalAmount($singleLine));
                $priceCollection->put('rate', matchBuySellRate($singleLine));
            }


//            if($threeCharsTag == 'KN-' && empty($priceCollection->offsetGet('fare_amount')))
//            {
//                if(strlen($singleLine) < 4) continue;
//
//                $fareType = matchFareType($singleLine);
//
//                $priceCollection->put('fare_currency',matchBaseFareCurrencyCode($singleLine));
//                $priceCollection->put('fare_amount',matchBaseFareAmount($singleLine));
//                $priceCollection->put('equiv_currency',matchEquivelantFareCuurency($singleLine));
//                $priceCollection->put('equiv_amount',matchEquivelantFareAmount($singleLine));
//                $priceCollection->put('total_amount',matchTotalAmount($singleLine));
//                $priceCollection->put('rate',matchBuySellRate($singleLine));
//            }

            if ($threeCharsTag == 'KFT') {
                if (strlen($singleLine) < 5) continue;
                $priceCollection->put('tax_amount', matchTaxAmount($singleLine));


            }

            if ($threeCharsTag == 'KST') {
                if (strlen($singleLine) < 5) continue;
                $priceCollection->put('tax_amount', matchTaxAmount($singleLine));

            }

            if($twoCharsTag == 'M-'){
                $priceCollection->put('fare_basis', str_replace(['M-',' '],'',$singleLine));
            }

            if ($twoCharsTag == 'I-') {

                $this->participantCounter++;

//                if (!isset($participantCounter)) {
//                    $participantCounter = 0;
//                }
                $participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);
//                $participantCounter++;
            }

            if ($threeCharsTag == 'EMD') {
                $priceCollection->put('emd_flag', true);
            }

            if ($threeCharsTag == 'T-K' || $threeCharsTag == 'T-L' || $threeCharsTag == 'T-O') {

                //reset it in case on new ticket
                $this->ticketDataCollectionUpdateIndexArray = [];

                $this->ticketCounter++;

                $this->ticketDataCollectionUpdateIndexArray[] = $this->ticketCounter;

                $explode = explode('-', $singleLine);

                $ticketNumber = trim($explode[0] . '-' . $explode[1] . '-' . $explode[2]);

                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);
                $ticketDataCollection->putByIndex('fare_amount', $priceCollection->offsetGet('fare_amount'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $this->ticketCounter);
//                $ticketDataCollection->putByIndex('tax_amount',$priceCollection->offsetGet('tax_amount'),$this->ticketCounter);
//                $ticketDataCollection->putByIndex('tax_currency',$priceCollection->offsetGet('fare_currency'),$this->ticketCounter);
                $ticketDataCollection->putByIndex('equiv_amount', $priceCollection->offsetGet('equiv_amount'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('equiv_currency', $priceCollection->offsetGet('equiv_currency'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);
                $ticketDataCollection->putByIndex('commission_amount', '0.0', $this->ticketCounter);
                $ticketDataCollection->putByIndex('commission_rate', '0.0', $this->ticketCounter);
                if (isset($explode[3]) && !empty(trim($explode[3]))) {
                    $substr = substr($explode[2], 0, 8);
                    $conjunctiveNumber = trim($explode[0] . '-' . $explode[1] . '-' . $substr . trim($explode[3]));

                    $counter = $this->ticketCounter + 1;
                    $this->ticketDataCollectionUpdateIndexArray[] = $counter;


                    $ticketDataCollection->putByIndex('number', $conjunctiveNumber, $counter);
                    $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $counter);
                    $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $counter);
                    $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $counter);
                    $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $counter);
                    $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $counter);
                    $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $counter);
                    $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $counter);
                    $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $counter);
                    $ticketDataCollection->putByIndex('fare_amount', $priceCollection->offsetGet('fare_amount'), $counter);
                    $ticketDataCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $counter);
                    $ticketDataCollection->putByIndex('equiv_amount', $priceCollection->offsetGet('equiv_amount'), $counter);
                    $ticketDataCollection->putByIndex('equiv_currency', $priceCollection->offsetGet('equiv_currency'), $counter);
                    $ticketDataCollection->putByIndex('conjunctive_flag', 'true', $counter);
                    $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $counter);
                    $ticketDataCollection->putByIndex('participants_index',$this->participantCounter, $counter);
                    $ticketDataCollection->putByIndex('commission_amount', '0.0', $counter);
                    $ticketDataCollection->putByIndex('commission_rate', '0.0', $counter);
                    $this->ticketCounter++;
                }

            }

            if ($twoCharsTag == 'FM') {
                $fareCommission = matchFareCommission($singleLine);
                foreach ($this->ticketDataCollectionUpdateIndexArray as $index) {
                    $ticketDataCollection->putByIndex('fare_commission', trim($singleLine), $index);

                    if(null !== $fareCommission && $fareCommission['type'] == 'amount'){
                        $ticketDataCollection->putByIndex('commission_amount', number_format($fareCommission['amount'],2,'.',''), $index);
                        $ticketDataCollection->putByIndex('commission_rate', '0.00', $index);
                    }elseif (null !== $fareCommission && $fareCommission['type'] == 'percentage'){
                        $equivAmount = $priceCollection->offsetGet('equiv_amount');
                        if(!empty($equivAmount)){
                            $fareAmount = $equivAmount;
                        }else{
                            $fareAmount = $priceCollection->offsetGet('fare_amount');
                        }
                        $amount = $fareCommission['amount'] / 100 * $fareAmount;;
                        $ticketDataCollection->putByIndex('commission_amount', number_format($amount,2,'.',''), $index);
                        $ticketDataCollection->putByIndex('commission_rate', number_format($fareCommission['amount'],2,'.',''), $index);
                    }else{
                        $ticketDataCollection->putByIndex('commission_amount', '0.0', $index);
                        $ticketDataCollection->putByIndex('commission_rate', '0.0', $index);
                    }
                }

            }

            if ($twoCharsTag == 'FP') {
                $partialyPaid = matchPartialyPaidFP($singleLine);
                if($partialyPaid !== 'FullPaid'){
                    $priceCollection->put('remaining_amount',$partialyPaid);
                }
                foreach ($this->ticketDataCollectionUpdateIndexArray as $index) {

                    $ticketDataCollection->putByIndex('fop', trim($singleLine), $index);
                    if($partialyPaid !== 'FullPaid'){
                        $ticketDataCollection->putByIndex('remaining_amount',substr($partialyPaid,3,10),$index);
                        $ticketDataCollection->putByIndex('partially_paid','true',$index);
                        $ticketDataCollection->putByIndex('remaining_amount_currency',
                            substr($partialyPaid,0,3),$index);


                    }else{
                        $ticketDataCollection->putByIndex('partially_paid','false',$index);
                    }
                }
                $priceCollection->put('form_of_payment', trim($singleLine));
                if (strpos($singleLine, 'CC') !== FALSE && strpos($singleLine, 'CCTP') === false && $partialyPaid == 'FullPaid') {
                    $segmentsCollection->updateValue('payment', 'D');
                }
            }

            if ($twoCharsTag == 'FT') {
                foreach ($this->ticketDataCollectionUpdateIndexArray as $index) {
                    $ticketDataCollection->putByIndex('tour_code', trim($singleLine), $index);
                }
            }

            if ($threeCharsTag == 'AIT') {
                $identCollection->put('match_code', matchAccountNumber($singleLine));
            }

            if ($threeCharsTag == 'RIS' || $threeCharsTag == 'RIF') {

                foreach ($this->invoiceRemarks as $singleRemark) {

                    if (strpos($singleLine, $singleRemark) !== false) {
                        $this->invoiceRemarksCounter++;
                        if ($threeCharsTag == 'RIF') {

                            $orderNumber = matchDigits($singleLine);
                            $identCollection->put('auto_import_order', $orderNumber);

                            // $invoiceRemarksCollection->putByIndex('remark',trim(str_replace($singleRemark,'',$singleLine)),$this->invoiceRemarksCounter);
                            // var_dump($invoiceRemarksCollection);die;
                        } else {
                            $invoiceRemarksCollection->putByIndex('remark', trim($singleLine), $this->invoiceRemarksCounter);


                            $invoiceRemarksCollection->putByIndex('currency', matchInvoiceCurrency($singleLine), $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('amount', matchInvoiceAmount($singleLine), $this->invoiceRemarksCounter);
                        }
                        $invoiceRemarksCollection->putByIndex('remark_type', str_replace(';', '', $singleRemark), $this->invoiceRemarksCounter);
                    }
                }
            }



            if ($twoCharsTag == 'RM' || strpos($singleLine,'RIFIDRF') !== false ) {

//                if(!defined('customRemarksClassLoaded')) {
//                    var_dump($file);
                if (!isset($customRemarksObj)) {
                    $reader = new IniReader();
                    $ini = $reader->readFile(get_importer_ini_path());
                    $customRemarksObj = load_custom_remarks_class($ini, $agent);
//                }
                }

                if (isset($customRemarksObj) && $customRemarksObj) {
                    $customRemarksObj->parseRMLine($singleLine, $customRemarksCollection, $this->participantCounter,$this->ownCC);
                }

                if(strpos($singleLine,'RM FILE') !== false){
                    $re = '/([\d].*)/';
                    preg_match($re, $singleLine, $matches);
                    if (isset($matches[1]) && !empty($matches[1])) {
                        $identCollection->put('auto_import_order', $matches[1]);
                    }

                }

            }

            if ($threeCharsTag == 'END') {
                // print_r($customRemarksCollection);die;
                $priceCollection->put('own_cc',$this->ownCC);
                                //print_r($customRemarksCollection->toArray());die;
//                print_r($participantsCollection->toArray());die;
            }
        }
        fclose($hfile);
    }

}