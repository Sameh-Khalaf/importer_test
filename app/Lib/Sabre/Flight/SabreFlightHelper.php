<?php


function Sab_getOwnerId($signIn,$agent,$ticketingOfficeId,$bookingOfficeId)
{
    $params['signIn'] = $signIn;
    $params['crsId'] = 7;
    $params['agent'] = $agent;
    $params['tktOfficeId'] = trim($ticketingOfficeId);
    $params['bkOfficeId'] = trim($bookingOfficeId);
    $params['checkTravelAgency'] = true;
    $client = new \GuzzleHttp\Client();
    $response = $client->request('post',
        env('traveloffice').'/api/crs-inbox/get-account-id',
        ['form_params' => $params,'verify' => false]);
    $res = $response->getBody()->getContents();

    $decode = json_decode($res,true);

    return $decode;
}

function Sab_matchMatchCode($line){
    preg_match('/EXLGT\d{5}/',$line,$matches);
    if(isset($matches[0]) && !empty($matches[0]) ) {
        return $matches[0];
    }
    return NULL;
}


function Sab_matchTicketType($line){

    $ticketType = trim(substr($line,13,1));

    return $ticketType;
}


function isPricesInMx($line){
    $string = (string)trim(substr($line,84,1));
    if($string == '1'){
        return true;
    }
    return false;
}
function Sab_matchM1Count($line){
    $string = trim(substr($line,186,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM2Count($line){
    $string = trim(substr($line,189,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM3Count($line){
    $string = trim(substr($line,192,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM4Count($line){
    $string = trim(substr($line,195,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM5Count($line){
    $string = trim(substr($line,198,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM6Count($line){
    $string = trim(substr($line,201,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM7Count($line){
    $string = trim(substr($line,204,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}
function Sab_matchM8Count($line){
    $string = trim(substr($line,207,3));
    if(!empty($string)){
        return (int)$string;
    }
    return '';
}


function Sab_matchPnr($line){
    $string = trim(substr($line,53,8));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchVoidPnr($line){
    $string = trim(substr($line,42,8));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchVoidTicketNumber($line){
    $string = trim(substr($line,29,10));
    if(!empty($string)){
        return $string;
    }
    return '';
}
function Sab_matchVoidCount($line){
    $string = trim(substr($line,40,2));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchBookingAgencyAccountCode($line){
    $string = trim(substr($line,88,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchTicketingAgencyAccountCode($line){
    $string = trim(substr($line,126,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}
function Sab_matchIATAAgencyNumber($line){
    $string = trim(substr($line,43,10));
    $string = str_replace(' ', '',$string);
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchTicketingAgentSign($line){
    $string = trim(substr($line,133,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}
function Sab_matchBookingAgentSign($line){
    $string = trim(substr($line,94,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchPNRCreationDate($line){
    $string = trim(substr($line,225,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchDepartureDate($line){
    $string = trim(substr($line,141,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}

//function Sab_matchOriginCityCode($line){
//    $string = trim(substr($line,146,3));
//    if(!empty($string)){
//        return $string;
//    }
//    return '';
//}
//function Sab_matchOriginCityName($line){
//    $string = trim(substr($line,149,17));
//    if(!empty($string)){
//        return $string;
//    }
//    return '';
//}
//function Sab_matchDestinationCityCode($line){
//    $string = trim(substr($line,166,3));
//    if(!empty($string)){
//        return $string;
//    }
//    return '';
//}
//function Sab_matchDestinationCityName($line){
//    $string = trim(substr($line,169,17));
//    if(!empty($string)){
//        return $string;
//    }
//    return '';
//}

function Sab_matchNumberOfPassengers($line){
    $string = trim(substr($line,186,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchNumberOfSegments($line){
    $string = trim(substr($line,192,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchPricesInMXRecord($line){
    $string = trim(substr($line,84,1));
    if(!empty($string)){
        return $string;
    }
    return '';
}


//M1
function Sab_matchPassengerNumberInPNR($line){
    $string = trim(substr($line,2,2));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchPassengerName($line){
    $string = trim(substr($line,4,64));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchPassengerFirstName($line){
    $string = trim(substr($line,4,64));
    if(!empty($string)){
        $ex = explode('/',$string);
        $ex = explode(' ',$ex[1]);
        return trim($ex[0]);

    }
    return '';
}


function Sab_matchPassengerLastName($line){
    $string = trim(substr($line,4,64));
    if(!empty($string)){
        $ex = explode('/',$string);
        return trim($ex[0]);

    }
    return '';
}

function Sab_matchPassengerTitle($line){
    $string = trim(substr($line,4,64));
    if(!empty($string)){
        $ex = explode('/',$string);
        $ex = explode(' ',$ex[1]);
        end($ex);
        $key = key($ex);

        return trim($ex[$key]);

    }
    return '';
}


//M2
function Sab_matchDomesticInternational($line){
    $string = trim(substr($line,18,1));
    if(!empty($string)){
        return $string;
    }
    return '';
}



//M3
function Sab_matchOriginCityCode($line){
    $string = trim(substr($line,18,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchOriginCityName($line){
    $string = trim(substr($line,21,17));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchSegmentDepartureDate($line){
    $string = trim(substr($line,9,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchDestinationCityCode($line){
    $string = trim(substr($line,38,3));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchDestinationCityName($line){
    $string = trim(substr($line,41,17));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchSegmentsCarrier($line){
    $string = trim(substr($line,58,2));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchFlightNumber($line){
    $string = trim(substr($line,60,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchClassOfService($line){
    $string = trim(substr($line,65,2));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchDepartureTime($line){
    $string = trim(substr($line,67,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchArrivalTime($line){
    $string = trim(substr($line,72,5));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchArrivalNextDay($line){ //IU3DCH
    $string = (string) trim(substr($line,90,1));
    if($string != ''){
        return (string)$string;
    }
    return '';
}



//M5
function Sab_matchFormOfPayment($line){
    $singleLine = $line;
    $ex = explode('/',$singleLine);
    $ex = explode(' ',$ex[5]);
    if(isset($ex[0]) && !empty($ex[0])){
        return trim($ex[0]);
    }
    return '';
}




function Sab_matchValidatingCarrier($line){
    $string = trim(substr($line,8,2));
    if(!empty($string)){
        return $string;
    }
    return '';
}


function Sab_matchTicketNumber($line){
    $string = trim(substr($line,11,10));
    if(!empty($string)){
        return $string;
    }
    return '';
}

function Sab_matchCommissionAmount($line){
    $ex = explode('/',$line);
    if(isset($ex[1]) && !empty(trim($ex[1]))){
        if(is_float((float)trim($ex[1]))) {
            return (float)trim($ex[1]);
        }else{
            return (float)0.00;
        }
    }
    return (float)0.00;
}



function Sab_matchNewFareAmount($line){
    $ex = explode('/',$line);
    if(isset($ex[2]) && !empty(trim($ex[2]))){
        if(is_float((float)trim($ex[2]))) {
            return (float)trim($ex[2]);
        }else{
            return (float)0.00;
        }
    }
    return (float)0.00;
}

function Sab_matchNewTaxAmount($line){
    $ex = explode('/',$line);
    if(isset($ex[3]) && !empty(trim($ex[3]))){
        if(is_float((float)trim($ex[3]))) {
            return (float)trim($ex[3]);
        }else{
            return (float)0.00;
        }
    }
    return (float)0.00;
}


function Sab_matchOriginalTikcetNumber($line){
    preg_match('/(\/E-@)([0-9]{13})\//',$line,$matches);
    if(isset($matches[2]) && !empty($matches[2]) && strlen($matches[2]) == 13) {
        $tkt = substr($matches[2],3,10);
        return trim($tkt);
    }else{
        var_dump($line);die;
        throw  new \Exception('ReIssue cant be handled '.__FILE__.' '.__LINE__);
    }
}




//MX
function Sab_matchFareAmount($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/F   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[1])){
                    preg_match('/[0-9]{10}/',$single,$ticketMatch);
                    print_r($ticketMatch);die;
                    $fareAmount = $matches[1];
                    return $fareAmount;
                }
            }

        }
    }
    return '';
}

function Sab_matchFareCurrency($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/F   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[2])){
                    $string = $matches[2];
                    return $string;
                }
            }

        }
    }
    return '';
}


function Sab_matchEquivalentAmount($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/Q   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[1])){
                    $fareAmount = $matches[1];
                    return $fareAmount;
                }
            }

        }
    }
    return '0.00';
}

function Sab_matchEquivalentCurrency($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/Q   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[2])){
                    $string = $matches[2];
                    return $string;
                }
            }

        }
    }
    return '';
}


function Sab_matchTaxAmount($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/X   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[1])){
                    $fareAmount = $matches[1];
                    return $fareAmount;
                }
            }

        }
    }
    return '0.00';
}

function Sab_matchTaxCurrency($data){
    foreach ($data as $item){
        foreach ($item as $single) {
            preg_match('/X   +(.*[\d]{1,11})([A-Z]{3})/', $single, $matches);
            if(!empty($matches)){
                if(isset($matches[2])){
                    $string = $matches[2];
                    return $string;
                }
            }

        }
    }
    return '';
}