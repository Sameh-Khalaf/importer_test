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

namespace App\Lib\Sabre\Flight;

use App\Jobs\HandleData;
use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\EmdDataCollection;
use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\InsuranceDataCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Sabre\Flight\Collections\RefundCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataATCCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;

class SabreFlightParser
{


    private $ticketTypesInDB = array(
        '1' => '1',
        'A' => '10',
        '5' => '2',
        '7' => '7',
        '2' => '3',
        '3' => '3',


    );

    private $parserClasses = [
        '1' => 'App\Lib\Sabre\Flight\Parsers\IssueParser',
        '7' => 'App\Lib\Sabre\Flight\Parsers\ReIssueParser',
        '10' => 'App\Lib\Sabre\Flight\Parsers\EmdParser',
        '2' => 'App\Lib\Sabre\Flight\Parsers\VoidParser',
        '3' => 'App\Lib\Sabre\Flight\Parsers\RefundParser',
        //'20' => 'App\Lib\Amadeus\Flight\Parsers\InsuranceParser',
    ];

    private $fileDataArray = [];

    /**
     * convert Sabre file to array
     * @param filename
     * @return mixed
     */

    private $file;
    private $agent;
    private $identCollection;
    private $ticketDataCollection;
    private $segmentsCollection;
    private $priceCollection;
    private $participantsCollection;
    private $invoiceRemarksCollection;
    private $customRemarksCollection;
    private $data;
    public function getFileDataAsArray($file)
    {
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
        //$data = preg_split("/\\r\\n|\\r|\\n|\\r|\\n/", $data);
        $data = preg_split("/\\r\\n|\\r|\\n|\\r|\\n/", $data);

        @$data[0] = @$data[0] . ' ' . @$data[1] . ' ' . @$data[2] . ' ' . @$data[3] . ' ' . @$data[4];
        //$data = array_filter($data);
        $data = array_values($data);

        return $data;
    }

    private function _prepData($data, $identifier = 'M2', $count = 1)
    {
        foreach ($data as $key => $single) {
            //$index = -1;
            $tempKey = $key;
            if (substr($single, 0, 2) == $identifier) {
                //for($i=1; $i <= $count;$i++){
                if (!empty($data[$tempKey])) {

                    if (isset($this->fileDataArray[$identifier])) {
                        $index = count($this->fileDataArray[$identifier]);
                    } else {
                        if(!isset($index)) {
                            $index = 0;
                        }
                    }
                    $this->fileDataArray[$identifier][$index] = $data[$key];


                } else {
                    continue;
                }
                // }
            }
        }
    }


    private function _prepMXRecords($data){
        $tempKey = 0;
        foreach ($data as $key=>$single){
            if(substr($single,0,4) == 'MX00'){
                $tempKey = $key;
                break;
            }
        }
        end($data);
        $lastKey = key($data);
        if($tempKey != 0){
            for($i=$tempKey;$i <= $lastKey;$i++){

                if(!empty($data[$i])){
                    if (isset($this->fileDataArray['MX']) && substr($data[$i],0,4) == 'MX00') {

                        $index = count($this->fileDataArray['MX']);
                    } else {
                        if(!isset($index)) {
                            $index = 0;
                        }
                    }

                    $this->fileDataArray['MX'][$index][] = $data[$i];
                    if($index != 0){
                       // var_dump($data[$i],count($this->fileDataArray['MX']));
                        //print_r($this->fileDataArray);die;
                    }

                }
            }
        }
    }

    private function _checkReIssue(){
        foreach ($this->fileDataArray['M5'] as $single){
            preg_match('/\/E-@[0-9]{13}\//',$single,$matches);
            if(isset($matches[0]) && !empty($matches[0])){
                return true;
            }
        }
        return false;
    }

    private function _checkRefund(){
        foreach ($this->fileDataArray['M5'] as $single){
            $refund = trim(substr($single,7,1));
            if($refund == 'R'){
                return true;
            }
        }
        return false;
    }

    public function parse($file, $agent, $output = false)
    {

        if (!($this->data = $this->getFileDataAsArray($file))) {
            return false;
        }


        crsDbConnection($agent);

        $this->identCollection = new IdentCollection();
        $this->ticketDataCollection = new TicketDataCollection();
        $this->segmentsCollection = new SegmentsCollection();
        $this->priceCollection = new PriceCollection();
        $this->participantsCollection = new ParticipantsCollection();
        $this->invoiceRemarksCollection = new InvoiceRemarksCollection();
        $this->customRemarksCollection = new CustomRemarksCollection();
        $this->emdDataCollection = new EmdDataCollection();
        $this->refundCollection = new RefundCollection();
        $this->ticketDataATCCOllection = new TicketDataATCCollection();
        $this->insuranceDataCollection = new InsuranceDataCollection();

        $validINS = false;
        $voidType = false;
        $ticketType = '';

        $ticketType = Sab_matchTicketType($this->data[0]);

        if (empty($ticketType) || !isset($this->ticketTypesInDB[$ticketType])
            || !isset($this->parserClasses[$this->ticketTypesInDB[$ticketType]])) {
            return;
        }

        $this->file = $file;

        //multi tickets handle example:
        //M50101  WY#9314240235/    .00/22367.00/22114.80/ONE/CA 1.1MOHAMED YEHIA ALI MR/1/F/E
        //M50201 RWY#9314240235/     P0/19267.00/21980.00/ONE/CA 1.1MOHAMED YEHIA ALI MR/1/1234/F/F/E
        //M50301  WY#9314240245/    .00/22367.00/22192.80/ONE/CA 1.1MOHAMED YEHIA ALI MR/1/F/E
        //M50401 AWY#9314250914/    .00/    0.00/  10.80/ONE/CA 1.1MOHAMED YEHIA ALI MR/1/F/E-@9109314240245/34
        $this->_prepData($this->data, 'M5', Sab_matchM5Count($this->data[0]));

        $ticketsAvailable = [];
        if(isset($this->fileDataArray['M5'])) {
            foreach ($this->fileDataArray['M5'] as $single) {
                if (preg_match('/F|D\/E$/', $single)) {
                    $ticketsAvailable[] = 1;
                }

                if (trim(substr($single, 7, 1)) == 'R') {
                    $ticketsAvailable[] = 3;
                }

                if (preg_match('/\/E-@[0-9]{13}\//', $single)) {
                    $ticketsAvailable[] = 7;
                }
            }
        }
        $this->fileDataArray = [];
        $ticketsAvailable = array_unique($ticketsAvailable);

        //void $this->data == 3
        if(count($this->data) == 3){
            $ticketsAvailable=['2'];
        }

        if(count($ticketsAvailable)>1){
            $this->_prepData($this->data, 'M1', Sab_matchM1Count($this->data[0]));
            $this->_prepData($this->data, 'M2', Sab_matchM2Count($this->data[0]));
            $this->_prepData($this->data, 'M3', Sab_matchM3Count($this->data[0]));
            $this->_prepData($this->data, 'M4', Sab_matchM4Count($this->data[0]));
            $this->_prepData($this->data, 'M5', Sab_matchM5Count($this->data[0]));
            $this->_prepData($this->data, 'M8');

            $this->_prepMXRecords($this->data);

            foreach ($this->fileDataArray['M5'] as $single) {

                if (preg_match('/F|D\/E$/', $single) && trim(substr($single,7,1)) == '') {
                    $this->addToQueue(1,$agent);
                }

                if(trim(substr($single,7,1)) == 'R'){

                    $this->addToQueue(3,$agent);
                }
                if(preg_match('/\/E-@[0-9]{13}\//',$single)){
                    $this->addToQueue(7,$agent);
                }
            }
        }elseif (count($ticketsAvailable) == 1) {
            if ($ticketType == '1') {

                $this->_prepData($this->data, 'M1', Sab_matchM1Count($this->data[0]));
                $this->_prepData($this->data, 'M2', Sab_matchM2Count($this->data[0]));
                $this->_prepData($this->data, 'M3', Sab_matchM3Count($this->data[0]));
                $this->_prepData($this->data, 'M4', Sab_matchM4Count($this->data[0]));
                $this->_prepData($this->data, 'M5', Sab_matchM5Count($this->data[0]));
                $this->_prepData($this->data, 'M8');

                $this->_prepMXRecords($this->data);

                if($this->_checkReIssue()){
                    $ticketType = '7';
                }

                $this->identCollection->put('version', $this->ticketTypesInDB[$ticketType]);
            }

            if ($ticketType == '2') {
                $this->_prepData($this->data, 'M1', Sab_matchM1Count($this->data[0]));
                $this->_prepData($this->data, 'M3', Sab_matchM3Count($this->data[0]));
                $this->_prepData($this->data, 'M5', Sab_matchM5Count($this->data[0]));

                //check refund
                if ($this->_checkRefund()) {
                    $ticketType = '3';
                    $this->identCollection->put('version', $this->ticketTypesInDB[$ticketType]);
                }
            }

            //emd
            if ($ticketType == 'A') {
                $this->_prepData($this->data, 'M1', Sab_matchM1Count($this->data[0]));
                $this->_prepData($this->data, 'M2', Sab_matchM2Count($this->data[0]));
                $this->_prepData($this->data, 'M3', Sab_matchM3Count($this->data[0]));
                $this->_prepData($this->data, 'M4', Sab_matchM4Count($this->data[0]));
                $this->_prepData($this->data, 'M5', Sab_matchM5Count($this->data[0]));
                $this->_prepData($this->data, 'MG', Sab_matchM5Count($this->data[0]));
                if (empty($this->fileDataArray['MG'])) {
                    throw new \Exception('INVALID EMD ' . __FILE__ . ' ' . __LINE__);
                }

                $this->identCollection->put('version', $this->ticketTypesInDB[$ticketType]);

            }

            if ($ticketType == '5') {
                $this->identCollection->put('version', $this->ticketTypesInDB[$ticketType]);
            }


            switch ($ticketType) {
                case "1": //issue
                    $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                    $parser->parse($file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
                        $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection, $this->customRemarksCollection, $this->data, $this->fileDataArray);

                    break;
                case "7": //reissue
                    $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                    $parser->parse($file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
                        $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection, $this->customRemarksCollection, $this->data, $this->fileDataArray);

                    break;
                case "A": //emd
                    $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                    $parser->parse($file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
                        $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection, $this->customRemarksCollection, $this->data, $this->fileDataArray);

                    break;
                case "5": //void
                    $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                    $parser->parse($file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
                        $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection, $this->customRemarksCollection, $this->data, $this->fileDataArray);

                    break;
                case "3": //refund
                    $parser = new $this->parserClasses[$this->ticketTypesInDB[$ticketType]];
                    $parser->parse($file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
                        $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection, $this->customRemarksCollection, $this->data, $this->fileDataArray);

                    break;
            }

            $responseA = [
                'ident' => $this->identCollection,
                'ticket' => $this->ticketDataCollection,
                'segments' => $this->segmentsCollection,
                'prices' => $this->priceCollection,
                'participants' => $this->participantsCollection,
                'invoiceRemarks' => $this->invoiceRemarksCollection,
                'customRemarks' => $this->customRemarksCollection,
                'emdData' => $this->emdDataCollection,
                'refund' => $this->refundCollection,
                'atc' => $this->ticketDataATCCOllection,
                'ins' => $this->insuranceDataCollection,
                'file' => $file,
                'agent' => $agent,
                'gds' => '7'
            ];

            $job = (new HandleData($responseA))->onQueue('HandleData');//->delay(\Carbon\Carbon::now()->addSeconds(2));
            //dispatch($job);
            $connection = \Illuminate\Support\Facades\Queue::connection('pgsql_crs_' . $agent);
            $connection->push($job, '', 'HandleData');
        }

    }

    private function addToQueue($tktType,$agent){

        $parser = new $this->parserClasses[$this->ticketTypesInDB[$tktType]];
        $parser->parse($this->file, $agent, $this->identCollection, $this->ticketDataCollection, $this->segmentsCollection,
            $this->priceCollection, $this->participantsCollection, $this->invoiceRemarksCollection,
            $this->customRemarksCollection, $this->data,$this->fileDataArray);

        $response = [
            'ident' => $this->identCollection,
            'ticket' => $this->ticketDataCollection,
            'segments' => $this->segmentsCollection,
            'prices' => $this->priceCollection,
            'participants' => $this->participantsCollection,
            'invoiceRemarks' => $this->invoiceRemarksCollection,
            'customRemarks' => $this->customRemarksCollection,
            'emdData' => $this->emdDataCollection,
            'refund' => $this->refundCollection,
            'atc' => $this->ticketDataATCCOllection,
            'ins' => $this->insuranceDataCollection,
            'file' => $this->file,
            'agent' => $agent,
            'gds' => '7'
        ];

        $job = (new HandleData($response))->onQueue('HandleData');//->delay(\Carbon\Carbon::now()->addSeconds(2));
        //dispatch($job);
        $connection = \Illuminate\Support\Facades\Queue::connection('pgsql_crs_'.$agent);
        $connection->push($job,'','HandleData');
        $this->resetCollections();
    }


    private function resetCollections(){
        $this->identCollection = new IdentCollection();
        $this->ticketDataCollection = new TicketDataCollection();
        $this->segmentsCollection = new SegmentsCollection();
        $this->priceCollection = new PriceCollection();
        $this->participantsCollection = new ParticipantsCollection();
        $this->invoiceRemarksCollection = new InvoiceRemarksCollection();
        $this->customRemarksCollection = new CustomRemarksCollection();
        $this->emdDataCollection = new EmdDataCollection();
        $this->refundCollection = new RefundCollection();
        $this->ticketDataATCCOllection = new TicketDataATCCollection();
        $this->insuranceDataCollection = new InsuranceDataCollection();
    }
}