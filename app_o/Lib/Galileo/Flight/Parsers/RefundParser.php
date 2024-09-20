<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/19/19
 * Time: 1:43 PM
 */

namespace App\Lib\Galileo\Flight\Parsers;

use App\Lib\Galileo\Flight\Collections\CustomRemarksCollection;
use App\Lib\Galileo\Flight\Collections\IdentCollection;
use App\Lib\Galileo\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Galileo\Flight\Collections\ParticipantsCollection;
use App\Lib\Galileo\Flight\Collections\PriceCollection;
use App\Lib\Galileo\Flight\Collections\SegmentsCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataCollection;
use App\Lib\Galileo\Flight\Collections\RefundCollection;

use Matomo\Ini\IniReader;


class RefundParser
{

    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarksCounter = -1;

    //we need to know start position and end position to update ticketdata in case of
    //conjunctive tickets
    private $ticketDataCollectionUpdateIndexArray = [];

    private $invoiceRemarks = [

    ];
    private $ownCC = false;
    private $validAirlineNumber = '';



    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection, RefundCollection &$refundCollection, $data)
    {



        foreach ($data as $singleLine){
            $twoCharsTag = substr($singleLine, 0 , 2);
            $threeCharsTag = substr($singleLine, 0 , 3);


            if($threeCharsTag == 'T51'){
                $identCollection->put('crs_id','14');
                $identCollection->put('pnr_id', Gal_matchPnr($singleLine));
//                $identCollection->put('total_pnr_passengers', matchTotalPassengerInPNR($singleLine));
                $identCollection->put('office_id', Gal_matchBookingAgencyAccountCode($singleLine));
                $identCollection->put('tktoffice_id', Gal_matchTicketingAgencyAccountCode($singleLine));
                //$identCollection->put('booking_iata', matchBookingIATA($singleLine));
                $identCollection->put('ticketing_iata', Gal_matchIATAAgencyNumber($singleLine));

                $identCollection->put('valid_carrier', Gal_matchValidatingAirlineCode($singleLine));

                $identCollection->put('owner_id', '0');
                $identCollection->put('affiliate', '0');

                $identCollection->put('ticketing_sine', Gal_matchTicketingAgentSign($singleLine));
                $ownerData = getOwnerId(Gal_matchTicketingAgentSign($singleLine), $agent,$identCollection->get('tktoffice_id'),$identCollection->get('office_id'),14);
                if (isset($ownerData['ownerId'])) {
                    $identCollection->put('owner_id', $ownerData['ownerId']);
                    $identCollection->put('affiliate', $ownerData['affiliate']);
                }
                $identCollection->put('booking_sine', Gal_matchBookingAgentSign($singleLine));


                $identCollection->put('booking_date', Gal_matchPNRCreationDate($singleLine));


                $identCollection->put('isdomestic', 'false');
                if (Gal_matchDomesticInternational($singleLine) != 'X') {
                    $identCollection->put('isdomestic', 'true');
                }
                $this->validAirlineNumber = Gal_matchValidatingAirlineNumber($singleLine);
            }



            /*if($threeCharsTag == 'A07'){


            }*/

            if ($threeCharsTag == 'A02') {



                $this->participantCounter++;


                //$participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', Gal_matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', Gal_matchPassengerFirstName(Gal_matchPassengerName($singleLine)), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', Gal_matchPassengerLastName(Gal_matchPassengerName($singleLine)), $this->participantCounter);
                //$participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);


                //set price collection
//                $fareNumber = Gal_matchFareNumberForPassenger($singleLine);
//
//                $fareLines = Gal_getIdentifierIndexFromArray($data,'A07'.$fareNumber)[0];
//                Gal_setPriceCollection($priceCollection, $data[$fareLines], $fareNumber);
                $priceCollection->put('emd_flag',false);

                //get refund fareamount line
                //BF:EGP


                //we need to calculate new fare
                $a11Identifier = Gal_getIdentifierIndexFromArray($data,'A11')[0];
                $totalAmount = (float)trim(substr($data[$a11Identifier],5,12));

                $priceCollection->putByIndex('fare_amount', $totalAmount,0);
                $priceCollection->putByIndex('tax_amount', '0.00',0);
                $priceCollection->putByIndex('fare_currency', 'EGP',0);
                $priceCollection->putByIndex('tax_currency', 'EGP',0);

                $participantsCollection->putByIndex('price', $priceCollection->getByIndex('fare_amount', 0), $this->participantCounter);
                //reset it in case on new ticket
                $this->ticketDataCollectionUpdateIndexArray = [];

                $this->ticketCounter++;

                $this->ticketDataCollectionUpdateIndexArray[] = $this->ticketCounter;

                //$explode = explode('-', $singleLine);

                $ticketNumber = $this->validAirlineNumber . '-' . Gal_matchTicketNumber($singleLine);

                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);

                $refundCollection->put('ticket_number', trim($ticketNumber));

                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);

                $ticketDataCollection->putByIndex('fare_amount', $priceCollection->getByIndex('fare_amount',0), $this->ticketCounter);

                $ticketDataCollection->putByIndex('fare_currency', $priceCollection->getByIndex('fare_currency',0), $this->ticketCounter);

                $ticketDataCollection->putByIndex('tax_amount',$priceCollection->getByIndex('tax_amount',0),$this->ticketCounter);

                $ticketDataCollection->putByIndex('tax_currency','EGP',$this->ticketCounter);

                $ticketDataCollection->putByIndex('equiv_amount', '0.00', $this->ticketCounter);

                $ticketDataCollection->putByIndex('equiv_currency', '', $this->ticketCounter);

                $ticketDataCollection->putByIndex('commission_rate', '0.00', $this->ticketCounter);
                $ticketDataCollection->putByIndex('commission_amount', '0.00', $this->ticketCounter);
                $ticketDataCollection->putByIndex('partially_paid', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('company_own_cc', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);


                $fpKey = Gal_getIdentifierIndexFromArray($data,'A11');

                if(count($fpKey) !== 1){
                    print_r('Multi form of payment found! '.__FILE__.__LINE__);
                }
                $formOfPayment = Gal_matchFormOfPayment($data[$fpKey[0]]);
                $ticketDataCollection->putByIndex('fop',$formOfPayment,$this->ticketCounter);
            }


            if($threeCharsTag == "A04"){
                if (!isset($segmentCounter)) {
                    $segmentCounter = 0;
                }

                $segmentsCollection->putByIndex('dep_city', Gal_matchOriginCityCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_city_name', Gal_matchOriginCityName($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_date', Gal_matchSegmentDepartureDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_time', Gal_matchDepartureTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city', Gal_matchDestinationCityCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city_name', Gal_matchDestinationCityName($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_date', Gal_matchArrivalNextDay($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_time', Gal_matchArrivalTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('flight_no', Gal_matchFlightNumber($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_service', Gal_matchClassOfService($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_booking', Gal_matchClassOfService($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('status', Gal_matchSegmentStatus($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('segtype', 'AIR', $segmentCounter);
                $segmentsCollection->putByIndex('ticketed', true, $segmentCounter);
                $segmentsCollection->putByIndex('filekey', $identCollection->offsetGet('pnr_id'), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', Gal_matchAirlineCode($singleLine), $segmentCounter);
//                $segmentsCollection->putByIndex('carrier', matchSegmentsCarrier($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('stop_over', Gal_matchStopOver($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $segmentCounter);
                // $segmentsCollection->putByIndex('equipment', matchSegmentsEquipmentType($singleLine), $segmentCounter);

                $fpKey = Gal_getIdentifierIndexFromArray($data,'A11');

                if(count($fpKey) !== 1){
                    print_r('Multi form of payment found! '.__FILE__.__LINE__);
                }
                $segmentsCollection->putByIndex('payment', 'A', $segmentCounter);
                $formOfPayment = Gal_matchFormOfPayment($data[$fpKey[0]]);
                if (strpos($formOfPayment, 'CC') !== FALSE ) {
                    //$segmentsCollection->updateValue('payment', 'D');
                    $segmentsCollection->putByIndex('payment', 'D', $segmentCounter);
                }


                $segmentsCollection->putByIndex('fare_currency', 'EGP', $segmentCounter);
                $segmentsCollection->putByIndex('fare', $priceCollection->getByIndex('fare_amount',0), $segmentCounter);
                $segmentsCollection->putByIndex('total_tax', '0.00', $segmentCounter);

                $segmentCounter++;
            }

            if($threeCharsTag == 'A23'){
                $refundCollection->put('source', 'G');
                $refundCollection->put('refund_date',  substr($singleLine, 32, 7));
                $refundCollection->put('domestic_flag', ($identCollection->get('isdomestic')) ? "D" : "I");

                //$refundCollection->put('currency', substr($explode[3], 0, 3));
                //$refundCollection->put('currency', substr($explode[3], 0, 3));
                // $refundCollection->put('fare_paid', substr($explode[3], 3, 11));
                // $refundCollection->put('fare_used', !empty($explode[4]) ? $explode[4] : '0.00');
                // $refundCollection->put('fare_refund', !empty($explode[5]) ? $explode[5] : '0.00');
                // $refundCollection->put('net_refund',!empty($explode[7]) ? $explode[7] : '0.00');
                // $refundCollection->put('cancel_fee', !empty($explode[8]) ? $explode[8] : '0.00');
                // $refundCollection->put('cancel_fee_commission', !empty($explode[9]) ? $explode[9] : '0.00');
                // $refundCollection->put('misc_fee', !empty($explode[10]) ? $explode[10] : '0.00');
                // $refundCollection->put('tax_code', substr($explode[11], 0, 2));
                // $refundCollection->put('tax_refund', substr($explode[11], 2, 11));
                // $refundCollection->put('refund_total', $explode[12]*-1);
                // $refundCollection->put('dep_date_first_seg', trim($explode[13]));
            }

            if($threeCharsTag == 'BF:'){
                //BASE FARE AMOUNT OF ORIGINAL ISSUE
                preg_match('/(\w{3})\s*([0-9]+\.[0-9]+)/', $singleLine, $matches);
                // if(!isset($matches[1])){
                //     $file_handle = fopen('/traveloffice/var/www/importer/test.txt', "w");

                //     fwrite($file_handle, $singleLine);

                //     fclose($file_handle);
                // }
                $currency = $matches[1];
                $amount = $matches[2];
            }

            if($threeCharsTag == 'PF:'){
                //PENALTY FEE AMOUN
                preg_match('/([0-9]+\.[0-9]+)/', $singleLine, $matches);
                $amount = $matches[1];
                $refundCollection->put('cancel_fee', $amount);
            }

            if($threeCharsTag == 'RA:'){
                $fareAmountUsed = substr($singleLine, 3, 8);
                $fareRefundable = substr($singleLine, 11, 8);
                $fareCreditUsed = substr($singleLine, 19, 8);
                $fareCreditRefundable = substr($singleLine, 27, 8);
                $currencyCode = substr($singleLine, 35, 3);
                $totalRefund = substr($singleLine, 38, 8);

                $refundCollection->put('currency', $currencyCode);
                $refundCollection->put('fare_used', $fareAmountUsed);
                $refundCollection->put('fare_refund', $fareRefundable);
                $refundCollection->put('fare_refund', $fareRefundable);
                $refundCollection->put('refund_total', $totalRefund*-1);
                $refundCollection->put('tax_refund', $totalRefund-$fareRefundable);
               
            }

            if($threeCharsTag == 'A24'){
//                print_r($ticketDataCollection->toArray());
//                print_r($priceCollection->toArray());
//                print_r($segmentsCollection->toArray());
//                print_r($data);
//                die;
            }

        }

//print_r($priceCollection->getByIndex('tax_amount',$fareNumber));die;
//print_r($data);die;
//        print_r($ticketDataCollection->toArray());die;

    }

}