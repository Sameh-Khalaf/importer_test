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

    private $validParticipants = [];

    private $assignedTicketsParticipants = [];


    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection, $data, $fileDataArray)
    {


        $identCollection->put('crs_id', '7');
        $identCollection->put('pnr_id', Sab_matchPnr($data[0]));
//                $identCollection->put('total_pnr_passengers', matchTotalPassengerInPNR($singleLine));
        $identCollection->put('office_id', Sab_matchBookingAgencyAccountCode($data[0]));
        $identCollection->put('tktoffice_id', Sab_matchTicketingAgencyAccountCode($data[0]));
        //$identCollection->put('booking_iata', matchBookingIATA($singleLine));
        $identCollection->put('ticketing_iata', Sab_matchIATAAgencyNumber($data[0]));


        $identCollection->put('owner_id', '0');
        $identCollection->put('affiliate', '0');

        $identCollection->put('ticketing_sine', Sab_matchTicketingAgentSign($data[0]));
        $ownerData = Sab_getOwnerId(Sab_matchTicketingAgentSign($data[0]), $agent, $identCollection->get('tktoffice_id'), $identCollection->get('office_id'));
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
        if (isset($fileDataArray['M5'])) {



            $totalFare = 0;
            $totalTax = 0;

            foreach ($fileDataArray['M5'] as $singleLine) {


                $priceCollection->put('fare_amount', Sab_matchNewFareAmount($singleLine));
                $priceCollection->put('fare_currency', 'EGP');
                $priceCollection->put('tax_amount', Sab_matchNewTaxAmount($singleLine));
                $priceCollection->put('tax_currency', 'EGP');
                $priceCollection->put('emd_flag', false);

            }

        }


        //Segments
        if (isset($fileDataArray['M3'])) {
            foreach ($fileDataArray['M3'] as $singleLine) {
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


                $segmentsCollection->putByIndex('fare', $priceCollection->offsetGet('fare_amount'), $segmentCounter);
                $segmentsCollection->putByIndex('fare_currency', 'EGP', $segmentCounter);


                $segmentsCollection->putByIndex('total_tax', $priceCollection->offsetGet('tax_amount'), $segmentCounter);
                $segmentsCollection->putByIndex('tax_currency', 'EGP', $segmentCounter);


                $segmentsCollection->putByIndex('payment', 'A', $segmentCounter);

                //form of payment
                if (isset($fileDataArray['M5'])) {

                    $formOfPayment = Sab_matchFormOfPayment($fileDataArray['M5'][0]);
                    if (empty($formOfPayment)) {
                        throw new \Exception('invalid Form of payment ' . __FILE__ . '#' . __LINE__);
                        return;
                    }
                    if (strpos($formOfPayment, 'CC') !== FALSE) {
                        //$segmentsCollection->updateValue('payment', 'D');
                        $segmentsCollection->putByIndex('payment', 'D', $segmentCounter);
                    }

                }

                $segmentCounter++;
            }
        }


        //participants
        if (isset($fileDataArray['M1'])) {
            foreach ($fileDataArray['M1'] as $key => $singleLine) {
                //check if this is valid participant in mx

                $this->participantCounter++;
                $participantsCollection->putByIndex('number', Sab_matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', Sab_matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', Sab_matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', Sab_matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', Sab_matchPassengerTitle($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('price', $priceCollection->offsetGet('fare_amount'), $this->participantCounter);
            }
        }


        //ticketdata

        if (isset($fileDataArray['M5'])) {
            foreach ($fileDataArray['M5'] as $key => $singleLine) {
                //check if this is valid participant in mx
                $participantsId = substr($singleLine, 4, 2);




                    $this->assignedTicketsParticipants[] = $participantsId;
                    $this->ticketCounter++;
                    // Sab_matchCommissionAmount($singleLine);
                    $ticketDataCollection->putByIndex('number', trim(Sab_matchTicketNumber($singleLine)), $this->ticketCounter);

                    $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->ticketCounter), $this->ticketCounter);

                    $ticketDataCollection->putByIndex('fare_amount', $priceCollection->offsetGet('fare_amount'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('fare_currency', 'EGP', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('tax_amount', $priceCollection->offsetGet('tax_amount'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('tax_currency', 'EGP', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('equiv_amount', '0.00', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('equiv_currency', '', $this->ticketCounter);

                    $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);

                    // $ticketDataCollection->putByIndex('commission_amount', Sab_matchCommissionAmount($singleLine), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('fop', Sab_matchFormOfPayment($fileDataArray['M5'][0]), $this->ticketCounter);

                    // $ticketDataCollection->putByIndex('commission_amount', Sab_matchCommissionAmount($singleLine), $this->ticketCounter);
                    $ticketDataCollection->putByIndex('valid_carrier', Sab_matchValidatingCarrier($singleLine), $this->ticketCounter);

                    $ticketDataCollection->putByIndex('original_number', '-',  $this->ticketCounter);

                    $ticketDataCollection->putByIndex('commission_rate', '0.00', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('commission_vat', '0.00', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('company_own_cc', 'false', $this->ticketCounter);
                    $ticketDataCollection->putByIndex('partially_paid', 'false', $this->ticketCounter);
                    $identCollection->put('valid_carrier', Sab_matchValidatingCarrier($singleLine));


            }
        }

    }

}