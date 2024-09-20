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

namespace App\Lib\Galileo\Flight;

use App\Jobs\HandleData;
use App\Lib\Galileo\Flight\Collections\CustomRemarksCollection;
use App\Lib\Galileo\Flight\Collections\EmdDataCollection;
use App\Lib\Galileo\Flight\Collections\IdentCollection;
use App\Lib\Galileo\Flight\Collections\InsuranceDataCollection;
use App\Lib\Galileo\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Galileo\Flight\Collections\ParticipantsCollection;
use App\Lib\Galileo\Flight\Collections\PriceCollection;
use App\Lib\Galileo\Flight\Collections\RefundCollection;
use App\Lib\Galileo\Flight\Collections\SegmentsCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataATCCollection;
use App\Lib\Galileo\Flight\Collections\TicketDataCollection;

class GalileoFlightParser
{


    private $ticketTypesInDB = array(
        'H' => '1',
        'F' => '10',
        'CA' => '2',
        'V' => '2',
        'R' => '3',
        'EXCH' => '7',
        'Z' => '12',
        'C' => '13',

    );

    private $parserClasses = [
        '1' => 'App\Lib\Galileo\Flight\Parsers\IssueParser',
        '7' => 'App\Lib\Galileo\Flight\Parsers\ReIssueParser',
        '10' => 'App\Lib\Galileo\Flight\Parsers\EmdParser',
        '2' => 'App\Lib\Galileo\Flight\Parsers\VoidParser',
        '3' => 'App\Lib\Galileo\Flight\Parsers\RefundParser',
        //'20' => 'App\Lib\Amadeus\Flight\Parsers\InsuranceParser',
    ];

    /**
     * convert galileo file to array
     * @param filename
     * @return mixed
     */

    public function getFileDataAsArray($file) {
        $data = '';

        $fh = fopen($file, "r");
        if ($fh) {
            while (($line = fgets($fh)) !== false) {
                $data .= $line;
            }

            fclose($fh);
        } else {
            return false;
        }
        $data = preg_split("/\\r\\n|\\r|\\n/", $data);
        @$data[0] = @$data[0] . ' ' . @$data[1] . ' ' . @$data[2] . ' ' . @$data[3] . ' ' . @$data[4];
        unset($data[1]);
        unset($data[2]);
        unset($data[3]);
        unset($data[4]);
        $data = array_values($data);
        return $data;
    }


    private function checkReissue($data){
        foreach ($data as $single){
            $checkA10 = substr($single,0,3);
            if($checkA10 == 'A10'){
                return true;
            }
        }
        return false;

    }

    private function checkEmd($data){
        foreach ($data as $single){
            $checkA10 = substr($single,0,3);
            if($checkA10 == 'A29'){
                return true;
            }
        }
        return false;

    }

    public function parse($file, $agent, $output = false)
    {

        if (!($data = $this->getFileDataAsArray($file))) {
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
        $refundCollection = new RefundCollection();


        $ticketType = Gal_matchTicketType($data[0]);
        if (empty($ticketType) || !isset($this->ticketTypesInDB[$ticketType])
            || !isset($this->parserClasses[$this->ticketTypesInDB[$ticketType]])) {

            return;
        }


        if ($ticketType == 'H') {

            //check reissue
            if ($this->checkReissue($data)) {
                $ticketType = 'EXCH';
            }
            $identCollection->put('version', $this->ticketTypesInDB[$ticketType]);
        }

        elseif ($ticketType == 'F'){
            if($this->checkEmd($data)){
                $identCollection->put('version', '10');
            }else{
                throw new \Exception('unhandled Emd '.__FILE__.' '.__LINE__);
            }
        }elseif ($ticketType == 'R'){
            $identCollection->put('version','3');
        }elseif ($ticketType == 'V') {
            $identCollection->put('version','2');

        }

        switch ($ticketType) {
            case "H": //issue
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $data);

                break;
             case "EXCH": //reissue
                 $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                 $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                     $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $data);

                 break;
            case "F":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];

                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $data);

                break;

            case "R":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];

                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $refundCollection, $data);

                break;
            case "V":
                $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];

                $parser->parse($file, $agent, $identCollection, $ticketDataCollection, $segmentsCollection,
                    $priceCollection, $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $data);

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
            'gds' => '14'
        ];

        $job = (new HandleData($responseA))->onQueue('HandleData');//->delay(\Carbon\Carbon::now()->addSeconds(2));
        //dispatch($job);
        $connection = \Illuminate\Support\Facades\Queue::connection('pgsql_crs_'.$agent);
        $connection->push($job,'','HandleData');
    }


}