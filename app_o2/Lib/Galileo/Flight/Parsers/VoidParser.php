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
use Matomo\Ini\IniReader;


class VoidParser
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
                          CustomRemarksCollection &$customRemarksCollection, $data)
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
                $fareNumber = 0;


                $fareNumber = Gal_matchFareNumberForPassenger($singleLine);

                $fareLines = Gal_getIdentifierIndexFromArray($data,'A07'.$fareNumber)[0];
                Gal_setPriceCollection($priceCollection, $data[$fareLines], $fareNumber);
                $priceCollection->put('emd_flag',false);


                //we need to calculate new fare
                $a11Identifier = Gal_getIdentifierIndexFromArray($data,'A11')[0];
                $totalAmount = (float)trim(substr($data[$a11Identifier],5,12));

                if(is_float($totalAmount) && $totalAmount !=0){
                    $taxAmount = $priceCollection->getByIndex('tax_amount',$fareNumber);
                    if($taxAmount == $totalAmount){
                        $priceCollection->putByIndex('fare_amount', '0.00',$fareNumber);
                        $priceCollection->putByIndex('fare_currency', 'EGP',$fareNumber);
                    }
                    if($totalAmount > $taxAmount){
                        $newFare = $totalAmount - $taxAmount;
                        $priceCollection->putByIndex('fare_amount', $newFare,$fareNumber);
                        $priceCollection->putByIndex('fare_currency', 'EGP',$fareNumber);
                    }
                }

                if($priceCollection->getByIndex('fare_currency',$fareNumber) == 'EGP') {
                    $participantsCollection->putByIndex('price', $priceCollection->getByIndex('fare_amount', $fareNumber), $this->participantCounter);
                }elseif ($priceCollection->getByIndex('equiv_currency',$fareNumber) == 'EGP'){
                    $participantsCollection->putByIndex('price', $priceCollection->getByIndex('equiv_amount', $fareNumber), $this->participantCounter);
                }else{
                    throw new \Exception('Galileo price must be checked '.__FILE__.' '.__LINE__);
                    die;
                }
                //reset it in case on new ticket
                $this->ticketDataCollectionUpdateIndexArray = [];

                $this->ticketCounter++;

                $this->ticketDataCollectionUpdateIndexArray[] = $this->ticketCounter;

                //$explode = explode('-', $singleLine);

                $ticketNumber = $this->validAirlineNumber . '-' . Gal_matchTicketNumber($singleLine);

                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);

                $ticketDataCollection->putByIndex('fare_amount', $priceCollection->getByIndex('fare_amount',$fareNumber), $this->ticketCounter);

                $ticketDataCollection->putByIndex('fare_currency', $priceCollection->getByIndex('fare_currency',$fareNumber), $this->ticketCounter);

                $ticketDataCollection->putByIndex('tax_amount',$priceCollection->getByIndex('tax_amount',$fareNumber),$this->ticketCounter);

                $ticketDataCollection->putByIndex('tax_currency','EGP',$this->ticketCounter);

                $ticketDataCollection->putByIndex('equiv_amount', $priceCollection->getByIndex('equiv_amount',$fareNumber), $this->ticketCounter);

                $ticketDataCollection->putByIndex('equiv_currency', $priceCollection->getByIndex('equiv_currency',$fareNumber), $this->ticketCounter);

                $ticketDataCollection->putByIndex('commission_rate', '0.00', $this->ticketCounter);
                $ticketDataCollection->putByIndex('commission_amount', '0.00', $this->ticketCounter);
                $ticketDataCollection->putByIndex('partially_paid', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('company_own_cc', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);
                $ticketDataCollection->putByIndex('tour_operator', $identCollection->offsetGet('tktoffice_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);


                $formOfPayment = Gal_matchFormOfPaymentEMD($data[$fareLines]);
                $ticketDataCollection->putByIndex('fop',$formOfPayment,$this->ticketCounter);




            }

            if($threeCharsTag == 'A11'){

            }

        }

//print_r($priceCollection->getByIndex('tax_amount',$fareNumber));die;
//print_r($data);die;
//        print_r($ticketDataCollection->toArray());die;

    }

}