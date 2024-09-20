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
use App\Lib\Amadeus\Flight\Collections\InsuranceDataCollection;
use Matomo\Ini\IniReader;


class InsuranceParser
{

    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarksCounter = -1;

    private $validIns = false;
    private $insIdentifiers = [];
    //we need to know start position and end position to update ticketdata in case of
    //conjunctive tickets
    private $ticketDataCollectionUpdateIndexArray = [];

    private $invoiceRemarks = [
        ';MANAGEMENT FEES',
        ';DISCOUNT',
        ';MARKUP',
        ';DIP MARKUP',
        'RIF FILE'
    ];

    public function parse($file, $agent, IdentCollection &$identCollection, InsuranceDataCollection &$insuranceDataCollection,
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
                $identCollection->put('version',20);

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



            if ($twoCharsTag == 'U-' && strpos($singleLine,'INS;')!== false) {
                $this->validIns = true;
                $this->ticketCounter++;
                $insuranceDataCollection->putByIndex('idnetifier',matchInsIdnetifier($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('name',matchInsName($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('beneficiary',matchInsBeneficiary($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('no_ppl',matchInsNoppl($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('address',matchInsAddress($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('emergency',matchInsEmergency($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('depdate',matchInsDepdate($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('arrdate',matchInsArrdate($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('trip',matchInsTrip($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('tripvalue',matchInsTripValue($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('geozone',matchInsGeoZone($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('tocode',matchInsToCode($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('insurance_provider_code',matchInsProviderCode($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('insurance_provider_name',matchInsProviderName($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('insurance_product_code',matchInsProductCode($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('insurance_product_name',matchInsProductName($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('product_details',matchInsProductDetails($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('extension',matchInsExtension($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('subscription_date',matchInsSubscriptionDate($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('subscribtion_time',matchInsSubscriptionTime($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('deposit_date',matchInsDepositeTime($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('departure_time',matchInsDepTime($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('reduction_code',matchInsReductionCode($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('substitute',matchInsSubstitue($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('babysit',matchInsBabySit($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('siid',matchInsSIID($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('policy_number',matchInsPolicyNumber($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('appraisal_number',matchInsApprisalNumber($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('net_premium_currency',matchInsPremiumCurrency($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('net_premium_amount',matchInsPremiumAmount($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('commission_percentage',matchInsCommsissionPercentage($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('commission_amount',matchInsCommsissionAmount($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('tax_codes',matchInsTaxCodes($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('tax_amounts',matchInsTaxAmounts($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('total_currency',matchInsTotalCurrency($singleLine),$this->ticketCounter);
                $insuranceDataCollection->putByIndex('total_amount',matchInsTotalAmount($singleLine),$this->ticketCounter);

                $identCollection->put('tktoffice_id',matchInsProviderCode($singleLine));

            }



            if ($twoCharsTag == 'I-') {

                $this->participantCounter++;

                $participantsCollection->putByIndex('number', matchPassengerNumberInPNR($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('name', matchPassengerName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('first_name', matchPassengerFirstName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('last_name', matchPassengerLastName($singleLine), $this->participantCounter);
                $participantsCollection->putByIndex('title', matchPassengerTitle($singleLine), $this->participantCounter);
            }

            if ($twoCharsTag == 'V-') {


            }
            if ($threeCharsTag == 'PPI') {

                $this->insIdentifiers[] = matchInsPPIdentifier($singleLine);
//                foreach ($this->p)

            }
            if ($threeCharsTag == 'MFP') {


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


            if ($twoCharsTag == 'RM' || strpos($singleLine,'RIFIDRF') !== false) {

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

            if ($threeCharsTag == 'END') {
                $i=0;
                foreach ($this->insIdentifiers as $id){
                    $participantsCollection->putByIndex('number',$id,$i);
                    $i++;
                }

                //clean unassingend policies

                foreach ($insuranceDataCollection->toArray() as $key=>$single){
                    $idnetifier = $single['idnetifier'];
                    if(!in_array($idnetifier,$this->insIdentifiers)){
                        $insuranceDataCollection->remove($key);
                    }

                }

//print_r($this->insIdentifiers);
               // print_r($insuranceDataCollection->toArray());

                //print_r($participantsCollection->toArray());die;
            }
        }

        fclose($hfile);
    }

}