<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/5/19
 * Time: 5:18 PM
 */

namespace App\Jobs;


use App\Lib\Amadeus\Flight\Helpers\EmdHelper as AmaEmdHelper;
use App\Lib\Amadeus\Flight\Helpers\InsuranceHelper as AmaInsuranceHelper;
use App\Lib\Amadeus\Flight\Helpers\IssueHelper as AmaIssueHelper;
use App\Lib\Amadeus\Flight\DbHandler;
use App\Lib\Amadeus\Flight\Helpers\RefundHelper as AmaRefundHelper;
use App\Lib\Amadeus\Flight\Helpers\ReIssueHelper as AmaReIssueHelper;
use App\Lib\Amadeus\Flight\Helpers\VoidHelper as AmaVoidHelper;

use App\Lib\Galileo\Flight\Helpers\EmdHelper as GalEmdHelper;
use App\Lib\Galileo\Flight\Helpers\InsuranceHelper as GalInsuranceHelper;
use App\Lib\Galileo\Flight\Helpers\IssueHelper as GalIssueHelper;

use App\Lib\Galileo\Flight\Helpers\RefundHelper as GalRefundHelper;
use App\Lib\Galileo\Flight\Helpers\ReIssueHelper as GalReIssueHelper;
use App\Lib\Galileo\Flight\Helpers\VoidHelper as GalVoidHelper;


use Illuminate\Support\Facades\DB;

class HandleData extends Job
{
    private $response = null;

    public function __construct($response)
    {

        //
        $this->response = $response;
    }

    public function handle()
    {

        $agent = $this->response['agent'];
        crsDbConnection($agent);
        $identCollection = $this->response['ident'];
        $ticketDataCollection = $this->response['ticket'];
        $segmentsCollection = $this->response['segments'];
        $priceCollection = $this->response['prices'];
        $participantsCollection = $this->response['participants'];
        $invoiceRemarksCollection = $this->response['invoiceRemarks'];
        $customRemarksCollection = $this->response['customRemarks'];
        $emdDataCollection = $this->response['emdData'];
        $refundCollection = $this->response['refund'];

        $ticketDataATCCOllection = $this->response['atc'];

        $insuranceDataCollection = $this->response['ins'];

        DbHandler::$file = $this->response['file'];

        if(isset( $this->response['gds']) && $this->response['gds'] == '14') {
            $gdsClass = 'App\\Lib\\Galileo\\Flight\\Helpers\\';
        }elseif(isset( $this->response['gds']) && $this->response['gds'] == '7'){
            $gdsClass = 'App\\Lib\\Sabre\\Flight\\Helpers\\';
        }else{
            $gdsClass = 'App\\Lib\\Amadeus\\Flight\\Helpers\\';
        }
         switch ($identCollection->offsetGet('version')){
            case '1':
                try {

                    $gdsClass = $gdsClass.'IssueHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $segmentsCollection, $priceCollection,
                        $participantsCollection, $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);

                }catch (\Exception $e){

                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }

                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;

            case '7':
                try {
                    $gdsClass = $gdsClass.'ReIssueHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $segmentsCollection, $priceCollection,
                        $participantsCollection, $ticketDataATCCOllection, $invoiceRemarksCollection, $customRemarksCollection, $agent,$this->response['file']);

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;

            case '10':
                try {
                    $gdsClass = $gdsClass.'EmdHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $segmentsCollection, $priceCollection,
                        $participantsCollection, $emdDataCollection, $invoiceRemarksCollection, $customRemarksCollection,
                        $agent, $this->response['file']);

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }

                break;

            case '2': //void issue, reissue
                try {
                    $gdsClass = $gdsClass.'VoidHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $priceCollection, $participantsCollection,
                        $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }

                break;
            case '12': //void Emd
                try {
                    $gdsClass = $gdsClass.'VoidHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $priceCollection, $participantsCollection,
                        $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;
            case '13': //void Refund
                try {

                    $gdsClass = $gdsClass.'VoidHelper';

                    new $gdsClass($identCollection, $ticketDataCollection, $priceCollection, $participantsCollection,
                        $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;

            case "3":
                try {
                    $gdsClass = $gdsClass.'RefundHelper';
                    if($this->response['gds'] == '1') {
                        new $gdsClass($identCollection, $participantsCollection, $refundCollection,
                            $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);
                    }elseif($this->response['gds'] == '14'){
                        new $gdsClass($identCollection, $participantsCollection, $refundCollection,
                            $invoiceRemarksCollection, $customRemarksCollection,$segmentsCollection, $agent, $this->response['file']);
                    }else{
                        new $gdsClass($identCollection, $participantsCollection, $ticketDataCollection,
                            $invoiceRemarksCollection, $customRemarksCollection, $agent, $this->response['file']);
                    }

                }catch (\Exception $e)
                {
                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }

                break;
            case '14': //refund emd
                try {
                    $gdsClass = $gdsClass.'RefundHelper';

                    new $gdsClass($identCollection, $participantsCollection, $refundCollection,
                        $invoiceRemarksCollection,$customRemarksCollection, $agent, $this->response['file']);


                }catch (\Exception $e)
                {

                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;
            case '20': //insurance
                try {
                    $gdsClass = $gdsClass.'InsuranceHelper';

                    new $gdsClass($identCollection, $participantsCollection, $insuranceDataCollection,
                        $invoiceRemarksCollection,$customRemarksCollection, $agent, $this->response['file']);


                }catch (\Exception $e)
                {

                    if(file_exists($this->response['file'])) {

                        $file = new \SplFileInfo($this->response['file']);
                        $checkSum = calcFileChecksum($file->getPathName(), $agent);
                        DbHandler::delFileChecksumExist($checkSum,$agent);
                        renameFile($this->response['file'], '');
                    }
                    throw new \Exception($e->getMessage().PHP_EOL.$e->getTraceAsString());
                }
                break;
        }
    }

    public function ignoreStatusForDuplication($file){

    }


//    public function success()
//    {
//        DbHandler::setFileChecksum($this->response['file'],$this->response['agent'],'Imported');
//
//
//    }
}
