<?php


function Gal_setPriceCollection(&$priceCollection, $singleLine,$index){

    $index = $index;
    $priceCollection->putByIndex('fare_currency', Gal_matchBaseFareCurrency($singleLine),$index);
    $priceCollection->putByIndex('fare_amount', Gal_matchBaseFareAmount($singleLine),$index);
    $priceCollection->putByIndex('equiv_currency', Gal_matchEquivalentCurrency($singleLine),$index);
    $priceCollection->putByIndex('equiv_amount', Gal_matchEquivalentAmount($singleLine),$index);
    $priceCollection->putByIndex('total_amount', Gal_matchTotalAmount($singleLine),$index);


    $tax1 = Gal_matchTax1Amount($singleLine);
    $tax2 = Gal_matchTax2Amount($singleLine);
    $tax3 = Gal_matchTax3Amount($singleLine);
    if(!is_float( (float) $tax1 )){
        $tax1 = 0.00;
    }
    if(!is_float( (float) $tax2 )){
        $tax2 = 0.00;
    }
    if(!is_float( (float) $tax3 )){
        $tax3 = 0.00;
    }

    $taxAmount = $tax1 + $tax2 + $tax3;
    $priceCollection->putByIndex('tax_amount', $taxAmount,$index);
    $priceCollection->putByIndex('tax_currency', 'EGP',$index);

}

function Gal_setPriceCollectionEmd(&$priceCollection, $singleLine,$index){

    $index = $index;
    $priceCollection->putByIndex('fare_currency', Gal_matchBaseFareCurrencyEMD($singleLine),$index);
    $priceCollection->putByIndex('fare_amount', Gal_matchBaseFareAmountEMD($singleLine),$index);
    $priceCollection->putByIndex('equiv_currency', Gal_matchEquivalentCurrencyEMD($singleLine),$index);
    $priceCollection->putByIndex('equiv_amount', Gal_matchEquivalentAmountEMD($singleLine),$index);
    $priceCollection->putByIndex('tax_amount', Gal_matchTaxAmountEMD($singleLine),$index);
    $priceCollection->putByIndex('tax_currency', Gal_matchTaxCurrncyEMD($singleLine),$index);


}

function Gal_getIdentifierIndexFromArray($array,$identifier){
    $foundKeys = [];
    foreach ($array as $key=>$single){
        $substrTxt = substr($single,0,strlen($identifier));
        if($substrTxt === $identifier){
            $foundKeys[] = $key;
//            return $array[$key];
        }
    }
    return empty($foundKeys) ? null : $foundKeys;
}

function Gal_matchTicketType($data)
{

    $T50IN12 = substr($data, 253, 1);
    if (isset($T50IN12)) {
        if (!empty(trim($T50IN12)) && $T50IN12 != 'S') {
            return $T50IN12;
        }
        return '';
    }
    return '';
}


function Gal_matchPnr($line){
    $pnr = trim(substr($line,98,6));
    if(!empty($pnr)){
        return $pnr;
    }
    return '';
}

function Gal_matchPNRCreationDate($line){
    $date = trim(substr($line,20,7));
    if(!empty($date)){
        return $date;
    }
    return '';
}

function Gal_matchValidatingAirlineCode($line){
    $airlineCode = trim(substr($line,32,2));
    if(!empty($airlineCode)){
        return $airlineCode;
    }
    return '';
}
function Gal_matchValidatingAirlineNumber($line){
    $airlineCode = trim(substr($line,34,3));
    if(!empty($airlineCode)){
        return $airlineCode;
    }
    return '';
}

function Gal_matchValidatingAirlineName($line){
    $string = trim(substr($line,37,24));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchDepartureDate($line){
    $string = trim(substr($line,61,7));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchBookingAgencyAccountCode($line){
    $string = trim(substr($line,81,4));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTicketingAgencyAccountCode($line){
    $string = trim(substr($line,85,4));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchIATAAgencyNumber($line){
    $string = trim(substr($line,89,9));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchBookingAgentSign($line){
    $string = trim(substr($line,113,6));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTicketingAgentSign($line){
    $string = trim(substr($line,120,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchBookingFileCreationDate($line){
    $string = trim(substr($line,124,7));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchPartyFareCurrencyCode($line){
    $string = trim(substr($line,145,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchPartyFare($line){
    $string = trim(substr($line, 148,12));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchTaxesCommisionsCurrencyCode($line){
    $string = trim(substr($line,161,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchPassengerCommissionAmount($line){
    $string = trim(substr($line,214,8));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchPassengerCommissionRate($line){
    $string = trim(substr($line,222,4));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTourCode($line){
    $string = trim(substr($line,226,15));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchRetransmission($line){
    $string = trim(substr($line,242,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchManualPricing($line){
    $string = trim(substr($line,243,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_match360FarePricing($line){
    $string = trim(substr($line,244,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_match1FareForAllPassengers($line){
    $string = trim(substr($line,245,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchExchangeTicketInfo($line){
    $string = trim(substr($line,248,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchPnrDomesticInternational($line){
    $string = trim(substr($line,258,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchPlatingCarrierCode($line){
    $string = trim(substr($line,259,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchDualMIRIdentifier($line){
    $string = trim(substr($line,265,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchNumberOfFrequentFlyer($line){
    $string = trim(substr($line,303,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchNumberOfTicketedSegments($line){
    $string = trim(substr($line,306,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchNumberOfUnTicketedSegments($line){
    $string = trim(substr($line,309,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchNumberOfFareSections($line){
    $string = trim(substr($line,315,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchNumberOfFormOfPayments($line){
    $string = trim(substr($line,321,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchNumberOfBackofficeRemarks($line){
    $string = trim(substr($line,330,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchPassengerName($line){
    $string = trim(substr($line,3,33));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchPassengerFirstName($line)
{
//    $explode = explode(';',$line);
    $re = '/([A-Z ]{2,64})(?:\/)/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function Gal_matchPassengerLastName($line)
{
//    $explode = explode(';',$line);
    //$line = preg_replace('/(MR;)|(MRS;)|(MISS;)|(CHD;)|(CH;)|(INF;)|(CNN;)|(MS;)|(DR;)/','',$line);
    $re = '/(?:[A-Z ]{2,64})(?:\/)([A-Z ]{2,64})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function Gal_matchTicketNumber($line){
    $string = trim(substr($line,48,10));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchInvoiceNumber($line){
    $string = trim(substr($line,60,9));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchFareNumberForPassenger($line){
    $string = trim(substr($line,75,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchExchangeNumberForPassenger($line){
    $string = trim(substr($line,77,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchMultipleFare($line){
    $string = trim(substr($line,79,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

//A02PN1
//todo: A03SEC frequentflyer

//A04
function Gal_matchSegmentIndexNumber($line){
    $string = trim(substr($line,3,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchAirlineCode($line){
    $string = trim(substr($line,5,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchAirlineNumber($line){
    $string = trim(substr($line,7,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchAirlineName($line){
    $string = trim(substr($line,10,12));
    if(!empty($string)){
        return '';
    }
}

function Gal_matchFlightNumber($line){
    $string = trim(substr($line,22,4));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchClassOfService($line){
    $string = trim(substr($line,26,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchSegmentStatus($line){
    $string = trim(substr($line,28,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchSegmentDepartureDate($line){
    $string = trim(substr($line,30,5));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchDepartureTime($line){
    $string = trim(substr($line,35,5));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchArrivalTime($line){
    $string = trim(substr($line,40,5));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchArrivalNextDay($line){
    $string = trim(substr($line,45,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchOriginCityCode($line){
    $string = trim(substr($line,46,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchOriginCityName($line){
    $string = trim(substr($line,49,13));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchDestinationCityCode($line){
    $string = trim(substr($line,62,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchDestinationCityName($line){
    $string = trim(substr($line,65,13));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchDomesticInternational($line){
    $string = trim(substr($line,78,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchStopOver($line){
    $string = trim(substr($line,84,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchNoOfStops($line){
    $string = trim(substr($line,85,1));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchBaggageAllowance($line){
    $string = trim(substr($line,86,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

//a07
function Gal_matchFareIndicator($line){
    $string = trim(substr($line,3,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchBaseFareCurrency($line){
    $string = trim(substr($line,5,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchBaseFareAmount($line){
    $string = trim(substr($line,8,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0';
}

function Gal_matchTotalCurrency($line){
    $string = trim(substr($line,20,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


function Gal_matchTotalAmount($line){
    $string = trim(substr($line,23,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0';
}

function Gal_matchEquivalentCurrency($line){
    $string = trim(substr($line,35,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchEquivalentAmount($line){
    $string = trim(substr($line,38,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0.00';
}

function Gal_matchTaxCurrency($line){
    $string = trim(substr($line,50,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTax1Amount($line){
    $string = trim(substr($line,56,8));
    if(!empty($string) && $string != 'EXEMPT'){
        return trim($string);
    }
    return '0.00';
}

function Gal_matchTax1Code($line){
    $string = trim(substr($line,64,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTax2Amount($line){
    $string = trim(substr($line,69,8));
    if(!empty($string) && $string != 'EXEMPT'){
        return trim($string);
    }
    return '0.00';
}

function Gal_matchTax2Code($line){
    $string = trim(substr($line,77,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTax3Amount($line){
    $string = trim(substr($line,82,8));
    if(!empty($string) && $string != 'EXEMPT'){
        return trim($string);
    }
    return '0.00';
}

function Gal_matchTax3Code($line){
    $string = trim(substr($line,90,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

//A08
function Gal_matchFareBasis($line){
    $string = trim(substr($line,45,15));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


//todo A10 exchange

//A11
function Gal_matchFormOfPayment($line){
    $string = trim(substr($line,3,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchCollectedOrRefundedAmount($line){
    $string = trim(substr($line,5,12));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}


//a29
function Gal_matchBaseFareCurrencyEMD($line){
    $string = trim(substr($line,97,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchBaseFareAmountEMD($line){
    $string = trim(substr($line,100,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0';
}

function Gal_matchEquivalentCurrencyEMD($line){
    $string = trim(substr($line,112,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchEquivalentAmountEMD($line){
    $string = trim(substr($line,115,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0.00';
}


function Gal_matchTaxCurrncyEMD($line){
    $string = trim(substr($line,127,3));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}
function Gal_matchTaxAmountEMD($line){
    $string = trim(substr($line,130,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0.00';
}

function Gal_matchTicketNumberEMD($line){
    $string = trim(substr($line,69,10));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchValidCarrierEMD($line){
    $string = trim(substr($line,95,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchFormOfPaymentEMD($line){
    $string = trim(substr($line,170,2));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

function Gal_matchTicketNumberOriginalEMD($line){
    $string = trim(substr($line,10,10));
    if(!empty($string)){
        return trim($string);
    }
    return '';
}

//a23
function Gal_matchFareAmountRefund($line){
    $string = trim(substr($line,130,12));
    if(!empty($string)){
        return trim($string);
    }
    return '0.00';
}