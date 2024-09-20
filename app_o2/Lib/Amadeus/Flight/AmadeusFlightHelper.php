<?php


function matchTicketType($line)
{
//    $line = 'AIR-BLK207;BT;;231;0000000000;1A1408681;001001';
    $re = '/(?:7D|7A|MA|CA|RF|7A|IA)/';
    preg_match($re, $line, $matches);
    if (isset($matches[0])) {
        if (!empty($matches[0])) {
            return $matches[0];
        }
        return '';
    }
    return '';
}

function matchReissueTicketType($data)
{
    $re = '/(^K-R.*$)|(^FO[0-9]{3}-[0-9]{10})/m';
    preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0]) && !empty($matches[0]) &&
        isset($matches[0][0]) && !empty($matches[0][0]) || (
        isset($matches[1]) && !empty($matches[1]) &&
        isset($matches[1][0]) && !empty($matches[1][0]) )
    ) {


        return 'EXCH';
    }
    return '';
}

function matchEMDTicketType($data)
{
    $re = '/(EMD[\d]{1,3};)/m';
    preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0]) && !empty($matches[0]) &&
        isset($matches[0][0]) && !empty($matches[0][0]) || (
            isset($matches[1]) && !empty($matches[1]) &&
            isset($matches[1][0]) && !empty($matches[1][0]) )
    ) {


        return 'EMD';
    }
    return '';
}


function matchVoidRefundType($data)
{
    $re = '/(R-[\d]{1,3}-[\d]{10})/m';
    preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0]) && !empty($matches[0]) &&
        isset($matches[0][0]) && !empty($matches[0][0]) || (
            isset($matches[1]) && !empty($matches[1]) &&
            isset($matches[1][0]) && !empty($matches[1][0]) )
    ) {


        return 'VRF';
    }
    return '';
}


function matchVoidEmdType($data)
{
    $re = '/(TMCD[\d]{1,3}-[\d]{10})/m';
    preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);

    if (isset($matches[0]) && !empty($matches[0]) &&
        isset($matches[0][0]) && !empty($matches[0][0]) || (
            isset($matches[1]) && !empty($matches[1]) &&
            isset($matches[1][0]) && !empty($matches[1][0]) )
    ) {


        return 'VEMD';
    }
    return '';
}


function matchPNR($line)
{
    $explode = explode(';',$line);
//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $re = '/\b(?:\w*MUC1A*\w)\b.([[:alnum:]]{6})/';
    preg_match($re, $explode[0], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1]) && strlen($matches[1]) == 6) {
            return $matches[1];
        }
        return '';
    }
    return '';
}


function matchTotalPassengerInPNR($line)
{
//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $explode = explode(';',$line);
    $re = '/([\d]{2})/';
    preg_match($re, $explode[1], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchBookingOfficeId($line)
{
//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $explode = explode(';',$line);
    $re = '/([[:alnum:]]{9})/';
    preg_match($re, $explode[2], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchBookingIATA($line)
{
//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $explode = explode(';',$line);
    $re = '/([\d]{8})/';
    preg_match($re, $explode[3], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchTicketingOfficeId($line)
{

//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $explode = explode(';',$line);
    $re = '/([[:alnum:]]{9})/';
    preg_match($re, $explode[8], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchTicketingIATA($line)
{
//    $line = 'MUC1A V55FKX003;0202;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;CAIEG2521;90202302;;;;;;;;;;;;;;;;;;;;;;MS V55FKX';
    $explode = explode(';',$line);
    $re = '/([\d]{8})/';
    preg_match($re, $explode[9], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchValidAirline($line)
{
//    $line = 'A-EGYPTAIR;MS 0770';
    $re = '/(?:.*;)([[:alnum:]]{2,3})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchPNRCreatorSign($line)
{
//    $line = 'C-7906/ 1048IZSU-1048IZSU-I-0--';
    $re = '/(?:.*\s)([[:alnum:]]{8})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchPNRTicketingSign($line)
{
//    $line = 'C-7906/ 1048IZSU-1048IZSU-I-0--';
    $re = '/(?:.*-)([[:alnum:]]{8})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}



function matchPNRCreationDate($line)
{
//    $line = 'D-190421;190501;190501';
    $line = explode(';',$line);
    return trim($line[2]);
    $re = '/(?:D-)(\d{6})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}



function matchDomesticInternationalFlag($line)
{
//    $line = 'G-X  ;;AMSAMS;';
    $re = '/(?:G-)([A-Z]{1,3})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchSegmnetStopOverIndicator($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $explode = explode(';',$line);
    $re = '/(?:[\d]{3})([A-Z]{1})/';
    preg_match($re, $explode[1], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchSegmnetOriginAirportCode($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $explode = explode(';',$line);
    $re = '/(?:[\d]{3}[A-Z]{1})([[:alnum:]]{3})/';
    preg_match($re, $explode[1], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmnetOriginCityName($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $explode = explode(';',$line);
    $re = '/([[:alnum:]]{1,17})/';
    preg_match($re, $explode[2], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchSegmentsNumberOfStops($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([\d]{1})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[9], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}
function matchSegmentsEquipmentType($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{1,3})/';
    $explode = explode(';',$line);
    if(!isset($explode[10])) return '';
    preg_match($re, $explode[10], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchSegmentsMealCode($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{1,2})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[8], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}
function matchSegmentsPNRStatusCode($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{4})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[7], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsStatusCode($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{4})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[6], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsArrivalDate($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
//    $re = '/(?:[[:alnum:]]{5})(?:[[:alnum:]]{4,5}\s*)(?:[[:alnum:]]{4,5}\s*)([[:alnum:]]{5})/';
    $re = '/([\d]{2}[A-Z]{3})/';
    $explode = explode(';',$line);
    preg_match_all($re, $explode[5], $matches);
    if (isset($matches[0])) {
        if (!empty($matches[0][1])) {
            return $matches[0][1];
        }
        return '';
    }
}

function matchSegmentsArrivalTime($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(?:[[:alnum:]]{5})(?:[[:alnum:]]{4,5}\s*)([[:alnum:]]{4,5})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsDepartureTime($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(?:[[:alnum:]]{5})([[:alnum:]]{4,5})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsDepartureDate($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([\d]{2}[A-Z]{3})/';
    $explode = explode(';',$line);
    preg_match_all($re, $explode[5], $matches);
    if (isset($matches[0])) {
        if (!empty($matches[0][0])) {
            return $matches[0][0];
        }
        return '';
    }
}

function matchSegmentsClassOfBooking($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(?:[[:alnum:]]{2}\s*)(?:[\d]{4,5}\s*)(?:[[:alnum:]]{1,2}\s*)([[:alnum:]]{1,2})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsClassOfService($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(?:[[:alnum:]]{2}\s*)(?:[\d]{4,5}\s*)([[:alnum:]]{1,2})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsFlightNumber($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(?:[[:alnum:]]{2}\s*)([\d]{4,5})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsCarrier($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/(^[[:alnum:]]{2})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[5], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsDestinationAirportCode($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{3})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[3], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchSegmentsDestinationCityName($line)
{
//    $line = 'H-007;002OAMS;AMSTERDAM        ;CAI;CAIRO            ;MS    0758 L L 15JUN1540 2010 15JUN;OK01;HK01;M ;0;738;;;2PC;;;ET;0430 ;N;2043;NL;EG;3 ';
    $re = '/([[:alnum:]]{1,17})/';
    $explode = explode(';',$line);
    preg_match($re, $explode[4], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchFareType($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/[-]([FIU]{1})/'; //F,I,U Issue
    preg_match($re, $explode[0], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }

    $re = '/(?:-)([RYW]{1})/'; //R,Y,W Reissue
    preg_match($re, $explode[0], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchBaseFareCurrencyCode($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/[-].([A-Z]{3}).*/';
    preg_match($re, $explode[0], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchBaseFareAmount($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/(?!^.{6})([\d]+(\.[\d]{1,2}))|([\d]+).*/';
    preg_match($re, $explode[0], $matches);

    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }else{
            if(isset($matches[3]) && !empty($matches[3]))
            {
                if(strpos($matches[3],'.') === false)
                {
                    return (float)$matches[3].'.00';
                }
                return $matches[3];
            }
        }
        return '';
    }
}


function matchEquivelantFareCuurency($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/([A-Z]{3}).*/';
    preg_match($re, $explode[1], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchEquivelantFareAmount($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/([\d]+(\.[\d]{1,2})).*/';
    preg_match($re, $explode[1], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchTotalAmount($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/([\d]+(\.[\d]{1,2})).*/';
    preg_match($re, $explode[12], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchTotalAmountCurrency($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/([A-Z]{3}).*/';
    preg_match($re, $explode[12], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchBuySellRate($line)
{
//    $line = 'K-FEUR240.00     ;EGP4615.00    ;;;;;;;;;;;EGP8728.00    ;19.227769  ;;';
    $explode = explode(';',$line);
    $re = '/([\d]+(\.[\d]{1,11})).*/';
    preg_match($re, $explode[13], $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchTaxAmount($line)
{
    $taxCodes = [];
//    $line = 'KFTF; EGP208.00   YQ AC; EGP156.00   YQ AD; EGP2756.00  YR VA; EGP50.00    JK DC; EGP431.00   QH EB; EGP18.00    EQ GO; EGP224.00   CJ SO; EGP270.00   RN DP;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;';
    $explode = explode(";",$line);
    $x = 0;
    for($i=0;$i<count($explode);$i++)
    {

        if(isset($explode[$i]) && !empty($explode[$i])){
//            if($explode[$i] == "KFTR") {
                preg_match('/(^O)/',$explode[$i],$omatches);

                if(empty($omatches)){
                    $re = '/([A-Z]{3}).*?([\d]+\.[\d]{1,9}).*?([[:alnum:]]{2}).([[:alnum:]]{2})?/';
                    preg_match($re, $explode[$i], $matches);

                    if (isset($matches[1]) && !empty($matches[1])) {

                        if (!empty($matches[1])) {
                            $taxCodes[$x]['currency'] = $matches[1];
                            $taxCodes[$x]['amount'] = $matches[2];
                            $taxCodes[$x]['code1'] = $matches[3];
                            $taxCodes[$x]['code2'] = isset($matches[4]) ? $matches[4] : '';
                            $x++;
                        }
                    }
                }
//            }

        }
    }
    return $taxCodes;

}

function matchPassengerNumberInPNR($line)
{
//    $line = 'I-002;01JORGEY/ARSANY(ADT);;APE-GDSADMIN@FLYIN.COM//M-+2001275856000;;'
    $explode = explode(';',$line);
    if(isset($explode[1]) && !empty($explode[1])) {
        $re = '/(\d{2}).*/';
        preg_match($re, $explode[1], $matches);
        if (isset($matches[1])) {
            if (!empty($matches[1])) {
                return $matches[1];
            }
            return '';
        }
    }
}

function matchPassengerFirstName($line)
{
//    $line = 'I-002;01JORGEY/ARSANY(ADT);;APE-GDSADMIN@FLYIN.COM//M-+2001275856000;;'
//    $explode = explode(';',$line);
    $re = '/(?:I-\d{3};\d{2})([A-Z ]{2,64})(?:\/)/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchPassengerTitle($line)
{
    $re = '/(MR;)|(MRS;)|(MISS;)|(CHD;)|(CH;)|(INF;)|(CNN;)|(MS;)|(DR;)/';
    preg_match($re, $line, $matches);

    if (isset($matches[0])) {
        if (!empty($matches[0])) {

            return trim(str_replace(';','',$matches[0]));
        }
        return '';
    }
}


function matchPassengerLastName($line)
{
//    $line = 'I-002;01JORGEY/ARSANY(ADT);;APE-GDSADMIN@FLYIN.COM//M-+2001275856000;;'
//    $explode = explode(';',$line);
    $line = preg_replace('/(MR;)|(MRS;)|(MISS;)|(CHD;)|(CH;)|(INF;)|(CNN;)|(MS;)|(DR;)/','',$line);
    $re = '/(?:I-\d{3};\d{2})(?:[A-Z ]{2,64})(?:\/)([A-Z ]{2,64})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}
function matchPassengerName($line)
{
    $explode = explode(';',$line);
    return $explode[1];
}

function matchAirlineAndTicketNumberOnly($number)
{
    //T-K077-1618498363

    $re = '/[A-Z]-[A-Z](\d{3}-\d{10})/';
    preg_match($re, $number, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}


function matchAccountNumber($line)
{
    //$line = 'AITANSUN001048;CC;IC;CR';

    $re = '/(?:.{5})([[:alnum:]]+;)/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return str_replace(';','',$matches[1]);
        }
        return '';
    }
}

function matchInvoiceCurrency($line)
{
//    $line = 'RISEGP; 114.00;;;MANAGEMENT FEES';
    $re = '/(?:.{3})([A-Z]{3})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchInvoiceAmount($line){
//    $line = 'RISEGP; 114.00;;;MANAGEMENT FEES';
//    $re = '/(?:.{7})(.*[\d]+\.[\d]{1,11})|([\d]+)/';
    $re = '/(?:.{7})([\d]+\.[\d]{2})/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }else{
            if(isset($matches[2]) && !empty($matches[2])){
                return $matches[2];
            }
        }
        return '';
    }

    $pattern = '/\b\d{1,5}\b/';
    $matches = array();

    if (preg_match($pattern, $line, $matches)) {
        $amount = $matches[0];
        return $amount; 
    }
}

function matchOriginalTicketNumber($line)
{
    $re = ' /^(?:[A-Z]{2})([\d]{3}-[\d]{10}(-[\d]{2})?)/';
    preg_match($re, $line, $matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return 'T-K'.$matches[1];
        }
        return '';
    }
}

function matchEmdAirlineCode($text)
{
    $re = '/[\d]{3}([[:alnum:]]{2})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchEmdAirlineNumber($text)
{
    $re = '/^([\d]{3})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return '';
    }
}

function matchEmdAirlineName($text)
{
    $re = '/^([[:alnum:]]{1,24})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return trim($matches[1]);
        }
        return '';
    }
}

function matchEmdAmountCurrency($text)
{
    $re = '/([A-Z]{3})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return trim($matches[1]);
        }
        return '';
    }
}

function matchEmdAmount($text)
{
    $re = '/([\d]+(\.[\d]{1,2}))/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return trim($matches[1]);
        }
        return '0';
    }
    return '0';
}

function matchEmdAirlineAndTicketNumber($text)
{
    $re = '/([\d]{3}-[\d]{10})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            return trim($matches[1]);
        }
        return '';
    }
}

function matchEMDICW($text)
{
    $re = '/([\d]{13})/';
    preg_match($re,$text,$matches);
    if (isset($matches[1])) {
        if (!empty($matches[1])) {
            $str = substr_replace($matches[1],'-',3,0);

            return 'T-K'.$str;
        }
        return '';
    }
}

function matchDigits($text)
{
    //(:?RIFFILE)([\d]+)
    //check if there is special characters
    $re = '/[^A-Za-z0-9]/';
    preg_match($re,$text,$charMatches);

    if(isset($charMatches[0]) && !empty(trim($charMatches[0]))){
        return '';
    }

    $re = '/(:?RIFFILE)([\d]+)/';
    preg_match($re,$text,$matches);

    if (isset($matches[2])) {
        if (!empty($matches[2])) {
            return trim($matches[2]);
        }
        return '';
    }
}


function getOwnerId($signIn,$agent,$ticketingOfficeId,$bookingOfficeId,$crsId=1,$checkTravelAgency=false)
{
    $params['signIn'] = $signIn;
    $params['crsId'] = $crsId;
    $params['agent'] = $agent;
    $params['tktOfficeId'] = trim($ticketingOfficeId);
    $params['bkOfficeId'] = trim($bookingOfficeId);
    $params['checkTravelAgency'] = $checkTravelAgency;
    $client = new \GuzzleHttp\Client();
    $response = $client->request('post',
        env('traveloffice').'/api/crs-inbox/get-account-id',
        ['form_params' => $params,'verify' => false]);
    $res = $response->getBody()->getContents();
// print_r($res);die;
    $decode = json_decode($res,true);

    return $decode;
}


function getCustomerInvoiceSettingByMatchCode($matchCode,$agent)
{
    $params['matchCode'] = $matchCode;
    $params['agent'] = $agent;
    $client = new \GuzzleHttp\Client();
    $response = $client->request('post',
        env('traveloffice').'/api/crs-inbox/get-customer-invoice-settings',
        ['form_params' => $params,'verify' => false]);
    $res = $response->getBody()->getContents();
    $decode = json_decode($res,true);

    return $decode;
}

function renameFile($filename='',$newExt='done')
{
    //var_dump($filename);;
    if(file_exists($filename)){
        if($newExt != '' && $newExt == 'done') {

            $newfilename = str_replace('.queue', '', $filename);

            rename($filename, $newfilename . '.done');
        }elseif ($newExt != '' && $newExt == 'ignore'){

            $newfilename = str_replace('.queue', '', $filename);

            rename($filename, $newfilename . '.ignore');
        }else{
            $newfilename = str_replace('.queue','',$filename);
//var_dump($newfilename);die;
            rename($filename,$newfilename);
        }
    }


}


function matchFareCommission($singleLine){
    $return = [];
    $explode = explode(';',$singleLine);
    $comm = trim($explode[0]);
    $re = '/(FM\*F\*)+(.*[\d]+\.[\d]{1,11})|([\d]+)$/';
    preg_match($re,$comm,$matches);
    if(isset($matches[2]) && !empty($matches[2]))
    {
        $return['type'] = 'percentage';
        $return['amount'] = $matches[2];
        return $return;
    }

    $re = '/(FM\*M\*)+(.*[\d]+\.[\d]{1,11})|([\d]+)A$/';
    preg_match($re,$comm,$matches);
    if(isset($matches[3]) && !empty($matches[3]))
    {
        $return['type'] = 'amount';
        $return['amount'] = $matches[3];
        return $return;
    }

    $re = '/(FM\*M\*)+([\d]+)$/';
    preg_match($re,$comm,$matches);

    if(isset($matches[2]) && !empty($matches[2]))
    {
        $return['type'] = 'percentage';
        $return['amount'] = $matches[2];
        return $return;
    }

}


function matchPartialyPaidFP($singleLine){

    $re = '/(\+CASH\/)/';

    preg_match($re,$singleLine,$matches);
    if(isset($matches[0]) && !empty($matches[0]) && strpos($singleLine, 'CC') !== FALSE){
        $re = '/(\/[A-Z]{3}+\d+(\.\d+)?)/';
        preg_match($re,$singleLine,$matches);
        if(isset($matches[0]) && !empty($matches[0])){
            $amount = str_replace('/','',$matches[0]);
            if(!empty($amount)){
                return $amount;
            }else{
                throw new Exception('Unable to handle amount '.__LINE__.' '.__FILE__);
            }
        }else{
            throw new Exception('Unable to handle amount '.__LINE__.' '.__FILE__);
        }
    }else{
        return 'FullPaid';
    }
}

function matchInsName($singleLine){
    $explode = explode(';',$singleLine);

    $name = str_replace(['NM-','/'],'',$explode[2]);
    $nameExplode = explode(' ',trim($name));
    $nameExplode = array_reverse($nameExplode);
    $name = implode(' ',$nameExplode);
    return trim($name);
}

function matchInsBeneficiary($singleLine){
    $explode = explode(';',$singleLine);

    $name = str_replace(['NB-','/'],'',$explode[3]);
    $nameExplode = explode(' ',trim($name));
    $nameExplode = array_reverse($nameExplode);
    $name = implode(' ',$nameExplode);
    return trim($name);
}
function matchInsNoppl($singleLine){
    $explode = explode(';',$singleLine);

    return (int) trim($explode[4]);
}

function matchInsAddress($singleLine){
    $explode = explode(';',$singleLine);
    $address = str_replace('AM-','',$explode[5]);
    return trim($address);
}

function matchInsEmergency($singleLine){
    $explode = explode(';',$singleLine);
    $emergency = str_replace('AE-','',$explode[6]);
    return trim($emergency);
}
function matchInsDepdate($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[7]);
}

function matchInsArrdate($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[8]);
}
function matchInsTrip($singleLine){
    $explode = explode(';',$singleLine);
    $trip = str_replace('TT-','',$explode[9]);
    return trim($trip);
}


function matchInsTripValue($singleLine){
    $explode = explode(';',$singleLine);
    $trip = str_replace('TV-','',$explode[10]);
    return trim($trip);
}


function matchInsGeoZone($singleLine){
    $explode = explode(';',$singleLine);
    $geo = str_replace('ZN-','',$explode[11]);
    return trim($geo);
}

function matchInsToCode($singleLine){
    $explode = explode(';',$singleLine);
    $tocode = str_replace('TO-','',$explode[12]);
    return trim($tocode);
}

function matchInsProviderCode($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[13]);
}

function matchInsProviderName($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[14]);
}

function matchInsProductCode($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[15]);
}


function matchInsProductName($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[16]);
}



function matchInsProductDetails($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[17]);
}


function matchInsExtension($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[18]);
}

function matchInsSubscriptionDate($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[25]);
}

function matchInsSubscriptionTime($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[26]);
}

function matchInsDepositeTime($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[27]);
}

function matchInsDepTime($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[28]);
}

function matchInsReductionCode($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('FD-','',$explode[29]);
    return trim($redCode);
}

function matchInsSubstitue($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('NS-','',$explode[30]);
    return trim($redCode);
}

function matchInsBabySit($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('NN-','',$explode[31]);
    return trim($redCode);
}

function matchInsSIID($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('SI-','',$explode[32]);
    return trim($redCode);
}


function matchInsPolicyNumber($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('CF-','',$explode[33]);
    return trim($redCode);
}

function matchInsApprisalNumber($singleLine){
    $explode = explode(';',$singleLine);
    $redCode = str_replace('XR-','',$explode[34]);
    return trim($redCode);
}

function matchInsPremiumCurrency($singleLine){
    $explode = explode(';',$singleLine);
    $currency = substr($explode[35],0,3);
    return trim($currency);
}

function matchInsPremiumAmount($singleLine){
    $explode = explode(';',$singleLine);
    $amount = substr($explode[35],3,10);
    return trim($amount);
}

function matchInsCommsissionPercentage($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[36]);
}

function matchInsCommsissionAmount($singleLine){
    $explode = explode(';',$singleLine);
    return trim($explode[37]);
}

function matchInsTaxCodes($singleLine){
    $taxCodes = [];
    $explode = explode(';',$singleLine);
    $re = '/[A-Z]+/';
    preg_match($re,$explode[38],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxCodes[] = $matches[0];
    }

    preg_match($re,$explode[39],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxCodes[] = $matches[0];
    }

    preg_match($re,$explode[40],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxCodes[] = $matches[0];
    }


    return implode(';',$taxCodes);
}

function matchInsTaxAmounts($singleLine){
    $taxAmounts = [];
    $explode = explode(';',$singleLine);
    $re = '/([\d]+(\.[\d]{1,2})).*/';
    preg_match($re,$explode[38],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxAmounts[] = $matches[0];
    }

    preg_match($re,$explode[39],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxAmounts[] = $matches[0];
    }

    preg_match($re,$explode[40],$matches);
    if(isset($matches[0]) && !empty($matches[0])){
        $taxAmounts[] = $matches[0];
    }


    return implode(';',$taxAmounts);
}

function matchInsTotalCurrency($singleLine){
    $explode = explode(';',$singleLine);
    $currency = substr($explode[41],0,3);
    return trim($currency);
}

function matchInsTotalAmount($singleLine){
    $explode = explode(';',$singleLine);
    $amount = substr($explode[41],3,10);
    return trim($amount);
}

function matchInsIdnetifier($singleLine){
    $explode = explode(';',$singleLine);
    $id = substr($explode[1],0,3);

    return (int)trim($id);
}


function matchInsPPIdentifier($singleLine){
    $explode = explode(';',$singleLine);

    $id = substr($explode[12],1,10);
    return trim($id);
}
































































