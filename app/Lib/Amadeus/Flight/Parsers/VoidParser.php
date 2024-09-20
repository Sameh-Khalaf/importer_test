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
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use Matomo\Ini\IniReader;

class VoidParser
{
    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarks = [
        ';MANAGEMENT FEES',
        ';DISCOUNT',
        ';MARKUP',
        ';DIP MARKUP',
        'RIFFILE'
    ];

    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection,$voidType=null)
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
                $explode = explode(';',$singleLine);
                if(isset($explode[2]) && !empty($explode[2])){
                    preg_match('/VOID([\d]{2}[A-Z]{3})/',$explode[2],$matches);
                    if(isset($matches[1]) && !empty($matches[1]))
                    {
                        $identCollection->put('booking_date',$matches[1]);
                    }

                }

                $identCollection->put('owner_id',0);
                $identCollection->put('affiliate',0);
                if(isset($explode[3])){
                    $identCollection->put('ticketing_sine', trim($explode[3]));
//                    $ownerData = getOwnerId(trim($explode[3]),$agent);\
                }

            }

            if ($twoCharsTag == 'I-') {
                $ownerData = getOwnerId(trim($identCollection->get('ticketing_sine')), $agent,$identCollection->get('tktoffice_id'),$identCollection->get('office_id'));
                if(isset($ownerData['ownerId']))
                {
                    $identCollection->put('owner_id',$ownerData['ownerId']);
                    $identCollection->put('affiliate',$ownerData['affiliate']);
                }
                $this->participantCounter++;

                $participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);
            }


            if($threeCharsTag == 'TMC'  && $voidType == 'VEMD')
            {
                $identCollection->put('version',12);

                $this->ticketCounter++;

                $ticketNumber = matchEmdAirlineAndTicketNumber($singleLine);

                if(empty($ticketNumber)) continue;

                $ticketNumber = 'T-K'.$ticketNumber;
                $explode = explode(';',$singleLine);


                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);
                $emdIdentifier = null;
                if(isset($explode[2])){
                    $emdIdentifier = trim($explode[2]);
                }
                $ticketDataCollection->putByIndex('emd_identifier', $emdIdentifier, $this->ticketCounter);
                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
//                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('tour_operator',$identCollection->offsetGet('tktoffice_id'),$this->ticketCounter);
            }


            if($threeCharsTag == 'T-K' || $threeCharsTag == 'T-E' && $voidType == null)
            {
                $identCollection->put('version',2);

                $this->ticketCounter++;

                $ticketNumber = matchEmdAirlineAndTicketNumber($singleLine);

                if(empty($ticketNumber)) continue;

                $ticketNumber = 'T-K'.$ticketNumber;


                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);

                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
//                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('tour_operator',$identCollection->offsetGet('tktoffice_id'),$this->ticketCounter);
            }

            if($twoCharsTag == 'R-' && $voidType == 'VRF')
            {
                $identCollection->put('version',13);

                $this->ticketCounter++;

                $ticketNumber = matchEmdAirlineAndTicketNumber($singleLine);

                if(empty($ticketNumber)) continue;

                $ticketNumber = 'T-K'.$ticketNumber;


                $ticketDataCollection->putByIndex('participants_index', $this->participantCounter, $this->ticketCounter);

                $ticketDataCollection->putByIndex('number', trim($ticketNumber), $this->ticketCounter);
                $ticketDataCollection->putByIndex('ticket_type', $identCollection->offsetGet('version'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('isdomestic', $identCollection->offsetGet('isdomestic'), $this->ticketCounter);
//                $ticketDataCollection->putByIndex('date', $identCollection->offsetGet('booking_date'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr', $identCollection->offsetGet('ticketing_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('orig_pnr', $identCollection->offsetGet('pnr_id'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('valid_carrier', $identCollection->offsetGet('valid_carrier'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('iatanr_booking_agent', $identCollection->offsetGet('booking_iata'), $this->ticketCounter);
                $ticketDataCollection->putByIndex('name', $participantsCollection->offsetGetByIndex('name', $this->participantCounter), $this->ticketCounter);
                $ticketDataCollection->putByIndex('conjunctive_flag', 'false', $this->ticketCounter);

                $ticketDataCollection->putByIndex('tour_operator',$identCollection->offsetGet('tktoffice_id'),$this->ticketCounter);
            }



            if ($twoCharsTag == 'FP') {
                $ticketDataCollection->putByIndex('fop', trim($singleLine), $this->ticketCounter);

                $priceCollection->put('form_of_payment', trim($singleLine));
            }

            if ($twoCharsTag == 'RM') {

//                if(!defined('customRemarksClassLoaded')) {
//                    var_dump($file);
                if (!isset($customRemarksObj)) {
                    $reader = new IniReader();
                    $ini = $reader->readFile(get_importer_ini_path());
                    $customRemarksObj = load_custom_remarks_class($ini, $agent);
//                }
                }

                if (isset($customRemarksObj) && $customRemarksObj) {
                    $customRemarksObj->parseRMLine($singleLine, $customRemarksCollection, $this->participantCounter);
                }

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
            if ($threeCharsTag == 'END') {
//                                print_r($ticketDataCollection->toArray());die;
//                print_r($participantsCollection->toArray());die;
            }
        }
        fclose($hfile);
    }
}