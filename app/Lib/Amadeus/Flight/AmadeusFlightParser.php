<?php
/*
 * AMADEUS IMPORTER
 * AUTHOR: AYMEN AHMED <FARR3LL@ROCKETMAIL.COM>
 * ===========================================
 * TODO:
 * connect to each customer db acording to corp_auth information
 * CHANGE LOG:
 * -define working directory
 * -change farebasis *used code according to documentation instead of the ticket amount*
 * -add debuging functionality
 * -move processed files to *bak* directory
 * -insert to ident refund, void tickets if issue tickets not found
 * -add crs userid to the ident table
 * -remove from the traveller (inf) or (chd) because it cause a problem to the last name
 * -check if the orig pnr exist so it to will add the other air file to the same pnr
 * -fixed issue if the last name of the traveller is long
 * -added the owner_id to able to filter tickets by user who issued it
 * -fixed issue with first name and the last name
 * -fixed issue with the traveler as a contact in conf agency selection
 * -fixed issue with the equalivant amount
 * -huge fix in when multiple air files come with the same pnr
 * -added new feature: that when air file has processed then it will save the filename and content to db to prevent duplication
 * -added check if db connection exist then return connection instead of connecting from the begging
 * -added close db connection
 * -fixed the foreach of the credit_card info
 * -added feature to loop through customers dbs
 * -added ftpHome dir which moves the air files from this dir to the working dir
 * -added ticket_type 0 in the ident, ticket_data
 * -devided each amadeus type in a class(refund,issue,void,emd)
 * -lot of changes done in the logic
 * -added mail to track errors
 * -added destination to the ident table
 * -fixed issue with the EMD tickets if it's not a penalty fee
 * -fixed issue if ticket has 3 segments and dep_city, arr_city are different in each segment
 * -fixed undefined index error in ticketdata
 * -added config file for travel agencies directories and to tell which agent this dir belong to
 * -added db config in the config file
 * -added file path in the config file
 * -checked the ticket number against agent
 *
 * -LastChanges: 26th July 2016
 */

namespace App\Lib\Amadeus\Flight;

use App\Jobs\HandleData;
use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\InsuranceDataCollection;
use App\Lib\Amadeus\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\RefundCollection;
use App\Lib\Amadeus\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;

class AmadeusFlightParser
{


    private $ticketTypesInDB = array(
        '7A' => '1',
        '7D' => '10',
        'MA' => '2',
        'CA' => '2',
        'RF' => '3',
        'R' => '3',
        'EXCH' => '7',
        'EMD' => '10',
        'VoidEmd' => '12',
        'IA' => '20',

    );

    private $parserClasses = [
        '1' => 'App\Lib\Amadeus\Flight\Parsers\IssueParser',
        '7' => 'App\Lib\Amadeus\Flight\Parsers\ReIssueParser',
        '10' => 'App\Lib\Amadeus\Flight\Parsers\EmdParser',
        '2' => 'App\Lib\Amadeus\Flight\Parsers\VoidParser',
        '3' => 'App\Lib\Amadeus\Flight\Parsers\RefundParser',
        '20' => 'App\Lib\Amadeus\Flight\Parsers\InsuranceParser',
    ];

    /**
     * check amadeus file if it's ended with ENDX
     * @param filename
     * @return boolean
     */
    private function __checkFileInegrity($hfile)
    {
        $fh = fopen($hfile, 'r');
        $start = substr(fgets($fh), 0, 3);
        $pos = -1;
        $end = $c = "";
        do {
            fseek($fh, $pos, SEEK_END);
            $c = trim(fgetc($fh));
            $end .= $c;
            $pos--;
        } while ($c !== "E");
        fclose($fh);
        $end = trim(strrev($end));
        if ($start === 'AIR' && ($end === 'ENDX' || $end === 'END')) {
            return true;
        }
        $this->invalidFile = true;

        return false;
    }

    public function parse($file, $agent, $output = false)
    {

        if (!$this->__checkFileInegrity($file)) {
            return false;
        }

        crsDbConnection($agent);

        $identCollection = new IdentCollection();
        $ticketDataCollection = new TicketDataCollection();
        $segmentsCollection = new SegmentsCollection();
        $priceCollection = new PriceCollection();
        $participantsCollection = new ParticipantsCollection();
        $invoiceRemarksCollection = new InvoiceRemarksCollection();
        $customRemarksCollection = new CustomRemarksCollection();
        $emdDataCollection = new EmdDataCollection();
        $refundCollection = new RefundCollection();
        $ticketDataATCCOllection = new TicketDataATCCollection();
        $insuranceDataCollection = new InsuranceDataCollection();

        $validINS = false;
        $voidType = false;
        $ticketType = '';
        $hfile = fopen($file, "r");
        while (!@feof($hfile)) {
            $singleLine = @fgets($hfile);

            $threeCharsTag = substr($singleLine, 0, 3);
//            $twoCharsTag = substr($singleLine, 0, 2);
            $twoCharsTag = substr($singleLine, 0, 2);

            if ($threeCharsTag == 'AIR') {
                $ticketType = matchTicketType($singleLine);;

                if (empty($ticketType) ||
                    !isset($this->ticketTypesInDB[$ticketType]) ||
                    !isset($this->parserClasses[$this->ticketTypesInDB[$ticketType]])) {

//                    throw new \Exception(sprintf('Invalid ticket type %s', $singleLine));

                    fclose($hfile);
                    return;
                }
                $reissueTicketType = matchReissueTicketType(file_get_contents($file));

                if (!empty($reissueTicketType) && $ticketType == '7A'){
                    $ticketType = $reissueTicketType;
                }


//
//                        $reissueTicketType = matchReissueTicketType(file_get_contents($file));
//
//                        if (!empty($reissueTicketType)) {
//                            $ticketType = $reissueTicketType;
//                        }


                if ($ticketType == 'MA') //there are 2 different types of void we need to find which one is that
                {
                    $voidType = matchVoidRefundType(file_get_contents($file));
                    if ($voidType == '') {
                        $voidType = matchVoidEmdType(file_get_contents($file));
                    }
                }


            }

            if ($twoCharsTag == 'U-' && strpos($singleLine, 'INS;') !== false) {
                // var_dump($singleLine);
                $validINS = TRUE;
            }


        }

        fclose($hfile);


        if ($ticketType == 'IA' && $validINS == false) {
            DbHandler::setFileChecksum($file,$agent,'');

            renameFile($file,'ignore');
            return;
        } else {
            //var_dump($validINS,$ticketType);
        }

        $identCollection->put('version', $this->ticketTypesInDB[$ticketType]);

        switch ($ticketType) {
            case "7A": //issue
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection);

                break;
            case "EXCH": //reissue
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $ticketDataATCCOllection);

                break;

            case "MA":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $voidType);
                break;

            case "7D":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $emdDataCollection);
                break;

            case "RF":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $refundCollection);
                break;
            case "IA":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $insuranceDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection);

                break;

        }

        $responseA = [
            'ident' => $identCollection,
            'ticket' => $ticketDataCollection,
            'segments' => $segmentsCollection,
            'prices' => $priceCollection,
            'participants' => $participantsCollection,
            'invoiceRemarks' => $invoiceRemarksCollection,
            'customRemarks' => $customRemarksCollection,
            'emdData' => $emdDataCollection,
            'refund' => $refundCollection,
            'atc' => $ticketDataATCCOllection,
            'ins' => $insuranceDataCollection,
            'file' => $file,
            'agent' => $agent,
            'gds' => '1',
        ];

        $job = (new HandleData($responseA))->onQueue('HandleData');//->delay(\Carbon\Carbon::now()->addSeconds(2));
        //dispatch($job);
        $connection = \Illuminate\Support\Facades\Queue::connection('pgsql_crs_'.$agent);
        $connection->push($job,'','HandleData');


        if (!empty($ticketType) && $ticketType == '7A' || $ticketType == 'EXCH') {
            //Handling emd inside reissue or issue file
            $ticketType = matchEMDTicketType(file_get_contents($file));
//                    if (!empty($emd)) {
            //check EMD
//                        $ticketType = matchEMDTicketType(file_get_contents($file));
            if (!empty($ticketType)) {

                $identCollection = new IdentCollection();
                $ticketDataCollection = new TicketDataCollection();
                $segmentsCollection = new SegmentsCollection();
                $priceCollection = new PriceCollection();
                $participantsCollection = new ParticipantsCollection();
                $invoiceRemarksCollection = new InvoiceRemarksCollection();
                $customRemarksCollection = new CustomRemarksCollection();
                $emdDataCollection = new EmdDataCollection();

                $identCollection->put('version', $this->ticketTypesInDB[$ticketType]);
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $emdDataCollection);

                $responseB = [
                    'ident' => $identCollection,
                    'ticket' => $ticketDataCollection,
                    'segments' => $segmentsCollection,
                    'prices' => $priceCollection,
                    'participants' => $participantsCollection,
                    'invoiceRemarks' => $invoiceRemarksCollection,
                    'customRemarks' => $customRemarksCollection,
                    'emdData' => $emdDataCollection,
                    'refund' => $refundCollection,
                    'atc' => '',
                    'ins' => '',
                    'file' => $file,
                    'agent' => $agent,
                    'gds' => '1',
                ];

                $job = (new HandleData($responseB))->onQueue('HandleData');//->delay(\Carbon\Carbon::now()->addSeconds(2));
                //dispatch($job);
                $connection = \Illuminate\Support\Facades\Queue::connection('pgsql_crs_'.$agent);
                $connection->push($job,'','HandleData');
            }
        }
//                    }


        if ($output) {
            if (isset($responseB))
                return [$responseA, $responseB];
            else
                return $responseA;
        }
//        return [
//            'ident'=>$identCollection,
//            'ticket'=>$ticketDataCollection,
//            'segments'=>$segmentsCollection,
//            'prices'=>$priceCollection,
//            'participants'=>$participantsCollection,
//            'invoiceRemarks'=>$invoiceRemarksCollection,
//            'customRemarks'=>$customRemarksCollection,
//            'emdData'=>$emdDataCollection,
//            'file'=>$file
//        ];


    }


}