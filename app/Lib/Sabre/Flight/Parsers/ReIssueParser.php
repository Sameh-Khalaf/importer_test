<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/19/19
 * Time: 1:43 PM
 */

namespace App\Lib\Sabre\Flight\Parsers;

use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;
use Matomo\Ini\IniReader;
use Mockery\Exception;


class ReIssueParser
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
    /**
     * @var array
     */
    private $validParticipants;


    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection, $data, $fileDataArray)
    {






        $identCollection->put('crs_id','7');
        $identCollection->put('pnr_id', Sab_matchPnr($data[0]));
//                $identCollection->put('total_pnr_passengers', matchTotalPassengerInPNR($singleLine));
        $identCollection->put('office_id', Sab_matchBookingAgencyAccountCode($data[0]));
        $identCollection->put('tktoffice_id', Sab_matchTicketingAgencyAccountCode($data[0]));
        //$identCollection->put('booking_iata', matchBookingIATA($singleLine));
        $identCollection->put('ticketing_iata', Sab_matchIATAAgencyNumber($data[0]));



        $identCollection->put('owner_id', '0');
        $identCollection->put('affiliate', '0');

        $identCollection->put('ticketing_sine', Sab_matchTicketingAgentSign($data[0]));
        $ownerData = Sab_getOwnerId(Sab_matchTicketingAgentSign($data[0]), $agent,$identCollection->get('tktoffice_id'),$identCollection->get('office_id'));
        if (isset($ownerData['ownerId'])) {
            $identCollection->put('owner_id', $ownerData['ownerId']);
            $identCollection->put('affiliate', $ownerData['affiliate']);
        }
        $identCollection->put('booking_sine', Sab_matchBookingAgentSign($data[0]));


        $identCollection->put('booking_date', Sab_matchPNRCreationDate($data[0]));

        $identCollection->put('isdomestic', 'false');
        if (Sab_matchDomesticInternational($data[0]) != 'X') {
            $identCollection->put('isdomestic', 'true');
        }


        //Prices MX
        if(isset($fileDataArray['M5'])){

            if(isPricesInMx($data[0])) {
                //first we need to get MX to find correct prices for travllers
                if (isset($fileDataArray['MX'])) {
                    foreach ($fileDataArray['MX'] as $item) {

                        foreach ($item as $singleLine) {

                            preg_match('/F\s+(.*[\d]{1,11})([A-Z]{3})/', $singleLine, $matches);
                            if (isset($matches[1]) && !empty(trim($matches[1]))) {
                                //get participant identifier
                                $partId = substr($singleLine, 7, 2);
                                $this->validParticipants[] = $partId;
                            }
                        }
                    }
                }
            }else{
                if(isset($fileDataArray['M2'])) {
                    foreach ($fileDataArray['M2'] as $single) {
                        $partId = substr($single, 2, 2);
                        $this->validParticipants[] = $partId;
                    }
                }elseif (isset($fileDataArray['M1'])) {
                    foreach ($fileDataArray['M1'] as $single) {
                        $partId = substr($single, 2, 2);
                        $this->validParticipants[] = $partId;
                    }
                }else{
                    throw new \Exception('No validParticipants found '. __LINE__.' '.__FILE__);
                }
            }
            $totalFare = 0;
            $totalTax= 0;
            foreach ($fileDataArray['M5'] as $key=>$single) {

                if(preg_match('/\/E-@[0-9]{13}\//',$single) == false) continue;

                //check if this is valid participant in mx
                $participantsId = substr($single,4,2);
                $ticketIdentifier = substr($single,2,2);

                if(in_array($participantsId,$this->validParticipants)) {
                    $price['fare_amount'] = Sab_matchNewFareAmount($single);
                    $price['fare_currency'] = 'EGP';
                    $price['tax_amount'] = Sab_matchNewTaxAmount($single);
                    $price['tax_currency'] = 'EGP';
                    $price['participant'] = $ticketIdentifier.$participantsId;
                    $priceCollection->prepend($price);
//                    $priceCollection->put('fare_amount', Sab_matchNewFareAmount($single));
//                    $priceCollection->put('fare_currency', 'EGP');
//                    $priceCollection->put('tax_amount', Sab_matchNewTaxAmount($single));
//                    $priceCollection->put('tax_currency', 'EGP');
//                    $priceCollection->put('participant',$participantsId);
                    $priceCollection->put('emd_flag', false);
                    $totalFare += Sab_matchNewFareAmount($single);
                    $totalTax += Sab_matchNewTaxAmount($single);
                    $priceCollection->put('total_amount', $totalFare);
                    $priceCollection->put('total_tax', $totalTax);

                }

            }

        }





        //Segments
        if(isset($fileDataArray['M3'])){
            foreach ($fileDataArray['M3'] as $singleLine){
                if (!isset($segmentCounter)) {
                    $segmentCounter = 0;
                }
                if(strlen(Sab_matchOriginCityCode($singleLine)) != 3) continue;

                $segmentsCollection->putByIndex('dep_city', Sab_matchOriginCityCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_city_name', Sab_matchOriginCityName($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_date', Sab_matchSegmentDepartureDate($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('dep_time', Sab_matchDepartureTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city', Sab_matchDestinationCityCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_city_name', Sab_matchDestinationCityName($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_date', Sab_matchArrivalNextDay($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('arr_time', Sab_matchArrivalTime($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('flight_no', Sab_matchFlightNumber($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_service', Sab_matchClassOfService($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('class_of_booking', Sab_matchClassOfService($singleLine), $segmentCounter);
//                $segmentsCollection->putByIndex('status', Sab_matchSegmentStatus($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('segtype', 'AIR', $segmentCounter);
                $segmentsCollection->putByIndex('ticketed', true, $segmentCounter);
                $segmentsCollection->putByIndex('filekey', $identCollection->offsetGet('pnr_id'), $segmentCounter);
//                $segmentsCollection->putByIndex('carrier', Sab_matchAirlineCode($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('carrier', Sab_matchSegmentsCarrier($singleLine), $segmentCounter);
//                $segmentsCollection->putByIndex('stop_over', Sab_matchStopOver($singleLine), $segmentCounter);
                $segmentsCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $segmentCounter);


//                if($priceCollection->offsetGet('fare_currency') != 'EGP'){
//                    $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('equiv_amount'), $segmentCounter);
//                    $segmentsCollection->putByIndex('fare_currency', $priceCollection->offsetGet('equiv_currency'), $segmentCounter);
//                }else{
//                    $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('fare_amount'), $segmentCounter);
//                    $segmentsCollection->putByIndex('fare_currency', $priceCollection->offsetGet('fare_currency'), $segmentCounter);
//                }
                $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('total_amount'), $segmentCounter);
                $segmentsCollection->putByIndex('fare_currency', 'EGP', $segmentCounter);

                $segmentsCollection->putByIndex('total_tax', $priceCollection->offsetGet('total_tax'), $segmentCounter);
                $segmentsCollection->putByIndex('tax_currency', 'EGP', $segmentCounter);




                $segmentsCollection->putByIndex('payment', 'A', $segmentCounter);

                //form of payment
                if(isset($fileDataArray['M5'])){

                    $formOfPayment = Sab_matchFormOfPayment($fileDataArray['M5'][0]);
                    if(empty($formOfPayment)){
                        throw new \Exception('invalid Form of payment '.__FILE__.'#'.__LINE__);
                        return;
                    }
                    if (strpos($formOfPayment, 'CC') !== FALSE ) {
                        //$segmentsCollection->updateValue('payment', 'D');
                        $segmentsCollection->putByIndex('payment', 'D', $segmentCounter);
                    }

                }

                $segmentCounter++;
            }
        }






        //participants
        if(isset($fileDataArray['M1'])){
            foreach ($fileDataArray['M1'] as $singleLine) {

                $this->participantCounter++;
                $participantsCollection->putByIndex('number', Sab_matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', Sab_matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', Sab_matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', Sab_matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', Sab_matchPassengerTitle($singleLine), $this->participantCounter);
            }
        }


        //ticketdata

        if(isset($fileDataArray['M5'])){
            foreach ($fileDataArray['M5'] as $singleLine){

                $participantsId = substr($singleLine,4,2);
                $ticketIdentifier = substr($singleLine,2,2);

                if(!in_array($participantsId,$this->validParticipants) ) {
                    continue;
                }

//                if(strpos($singleLine,'/E-@') == false) continue;
                if(preg_match('/\/E-@[0-9]{13}\//',$singleLine) == false) continue;
                $this->ticketCounter++;

                $participantsId = substr($singleLine,4,2);

                $identCollection->put('version', 7);
               // Sab_matchCommissionAmount($singleLine);
                $ticketDataCollection->putByIndex('number', trim(Sab_matchTicketNumber($singleLine)), $this->ticketCounter);

                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndexNumber('name', $participantsId), $this->ticketCounter);

                $ticketDataCollection->putByIndex('fare_amount', $priceCollection->getByIndex('fare_amount',$ticketIdentifier.$participantsId), $this->ticketCounter);
                $ticketDataCollection->putByIndex('fare_currency', 'EGP', $this->ticketCounter);
                $ticketDataCollection->putByIndex('tax_amount',$priceCollection->getByIndex('tax_amount',$ticketIdentifier.$participantsId),$this->ticketCounter);
                $ticketDataCollection->putByIndex('tax_currency','EGP',$this->ticketCounter);

                $ticketDataCollection->putByIndex('equiv_amount', '0.00', $this->ticketCounter);
                $ticketDataCollection->putByIndex('equiv_currency', '', $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);

                //$ticketDataCollection->putByIndex('commission_amount', Sab_matchCommissionAmount($singleLine), $this->ticketCounter);
                $ticketDataCollection->putByIndex('fop', Sab_matchFormOfPayment($fileDataArray['M5'][0]), $this->ticketCounter);

                //$ticketDataCollection->putByIndex('commission_amount', Sab_matchCommissionAmount($singleLine), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', Sab_matchValidatingCarrier($singleLine), $this->ticketCounter);


                $ticketDataCollection->putByIndex('commission_rate', '0.00',  $this->ticketCounter);
                $ticketDataCollection->putByIndex('commission_vat', '0.00',  $this->ticketCounter);
                $ticketDataCollection->putByIndex('company_own_cc', 'false',  $this->ticketCounter);
                $ticketDataCollection->putByIndex('partially_paid', 'false',  $this->ticketCounter);

                $ticketDataCollection->putByIndex('original_number', Sab_matchOriginalTikcetNumber($singleLine),  $this->ticketCounter);

                $identCollection->put('valid_carrier', Sab_matchValidatingCarrier($singleLine));


            }
        }


        if(isset($fileDataArray['M8'])) {
            if (!isset($customRemarksObj)) {
                $reader = new IniReader();
                $ini = $reader->readFile(get_importer_ini_path());
                $customRemarksObj = load_custom_remarks_class($ini, $agent,'Sabre');
            }


            foreach ($fileDataArray['M8'] as $key=>$singleLine){
                if (isset($customRemarksObj) && $customRemarksObj) {
                    $customRemarksObj->parseRMLine($singleLine, $customRemarksCollection, $invoiceRemarksCollection);
                }else{
                    throw new \Exception('Unable to load custom remarks class in '.__CLASS__.'# '.__LINE__);
                    //break;
                }
            }
            $invoiceRemarks = [
                'MARKUP-EGP',
                'DIP-EGP',
                'DISC-EGP-',
                'SVF-EGP',
                'BCKOFF-',
            ];

            foreach ($fileDataArray['M8'] as $key=>$singleLine) {
                foreach ($invoiceRemarks as $singleCode) {
                    $pos = strpos($singleLine, $singleCode);

                    if ($pos !== false) {
                        $substring = substr($singleLine, $pos + strlen($singleCode));
                        if ($singleCode == 'MARKUP-EGP' || $singleCode == 'DIP-EGP' || $singleCode == 'DISC-EGP-' || $singleCode == 'SVF-EGP' || $singleCode = 'BCKOFF-') {
                            $this->invoiceRemarksCounter++;

                            $pos = strpos($singleLine, $singleCode);
                            $substring = substr($singleLine, $pos + strlen($singleCode));

                            $invoiceRemarksCollection->putByIndex('remark', $singleLine, $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('currency', 'EGP', $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('amount', $substring, $this->invoiceRemarksCounter);
                            $invoiceRemarksCollection->putByIndex('remark_type', $singleCode, $this->invoiceRemarksCounter);
                        }
                    }
                }
            }

            $identCollection->put('has_online_invoice', false);
            $identCollection->put('has_online_voucher', false);

            $identCollection->put('auto_import_order', $customRemarksObj->autoImportOrder);
            //$identCollection->put('match_code', $customRemarksObj->matchCode);
            if($customRemarksObj->hasAutoInvoice) {
                $identCollection->put('has_online_invoice', true);
            }
            if($customRemarksObj->hasAutoVoucher) {
                $identCollection->put('has_online_voucher', true);
            }
        }


//        print_r($ticketDataCollection->toArray());die;

    }

}