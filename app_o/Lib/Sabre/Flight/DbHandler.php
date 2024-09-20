<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/18/19
 * Time: 11:24 AM
 */

namespace App\Lib\Sabre\Flight;

use App\CustomeRemarks;
use App\EmdData;
use App\FilesChecksum;
use App\InvoiceRemarks;
use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Amadeus\Flight\Collections\EmdDataCollection;
use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Amadeus\Flight\Collections\PriceCollection;
use App\Lib\Amadeus\Flight\Collections\RefundCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataATCCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use App\Ident;
use App\Participants;
use App\ProcessedFiles;
use App\Segments;
use App\Ticketdata;
use App\TicketdataATC;
use App\TicketdataRefunds;
use App\TicketdataTaxes;
use Illuminate\Support\Facades\DB;

/**
 * Class DbHandler
 * @package App\Lib\Amadeus\Flight
 */
class DbHandler
{

    /**
     * @var string
     */
    public static $file = '';
    public static $output=false;
    public static $agent = '';
    /**
     * @param IdentCollection $identCollection
     * @throws \Throwable
     */
    public function saveIdent(IdentCollection &$identCollection)
    {
        $identModel = new Ident($identCollection->toArray());
        $identModel->setConnection('pgsql_crs_'.self::$agent);

        if(static::$output)
        {
            print_r($identCollection);
        }else{
            $identModel->saveOrFail();
        }

        
        $identCollection->putNotExistKeyWithVal('id', $identModel->id);


    }

    /**
     * @param SegmentsCollection $segmentsCollection
     * @param IdentCollection $identCollection
     * @throws \Throwable
     */
    public static function saveSegments(SegmentsCollection &$segmentsCollection, IdentCollection &$identCollection)
    {
        $segmentsCounter = $segmentsCollection->count();
        for ($i = 0; $i < $segmentsCounter; $i++) {
            $segmentsCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);
            $segmentsModel = new Segments($segmentsCollection->offsetGet($i));
            $segmentsModel->setConnection('pgsql_crs_'.self::$agent);
            try {

                if(static::$output)
                {
                    print_r($segmentsCollection);
                }else{
                    $segmentsModel->saveOrFail();
                }

            } catch (\Exception $e) {
                throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__);
                //var_dump($segmentsCollection->toArray(), $segmentsModel, $e->getMessage(), static::$file);
                die;
            }
        }
    }







    /**
     * @param IdentCollection $identCollection
     * @param $file
     * @param $agent
     * @throws \Throwable
     */
    public function saveProcessedFile(IdentCollection &$identCollection, $file, $agent)
    {
        $fileInfo = new \SplFileInfo($file);
        $processedFileModel = new ProcessedFiles();
        $processedFileModel->filename = $fileInfo->getFilename();
        $processedFileModel->setConnection('pgsql_crs_'.self::$agent);
        $processedFileModel->agent = $agent;
        $processedFileModel->pnr_id = $identCollection->offsetGet('id');
        if(file_exists($file)) {
            $processedFileModel->content = file_get_contents($file);
        }elseif (file_exists(str_replace('.queue','.done',$file))){
            $file = str_replace('.queue','.done',$file);
            $processedFileModel->content = file_get_contents($file);
        }
        $processedFileModel->supplier = 0;
        $processedFileModel->type = $identCollection->offsetGet('version');
//        $processedFileModel->saveOrFail();
        if(static::$output)
        {
            print_r($processedFileModel->toArray());
        }else{
            $processedFileModel->saveOrFail();
        }

    }

    /**
     * @param IdentCollection $identCollection
     * @param InvoiceRemarksCollection $invoiceRemarksCollection
     * @throws \Throwable
     */
    public function saveInvoiceRemarks(IdentCollection &$identCollection, InvoiceRemarksCollection &$invoiceRemarksCollection)
    {
        if (!empty($invoiceRemarksCollection->toArray()[0]['remark_type'])) {

            $invoiceRemarksCounter = $invoiceRemarksCollection->count();
            for ($i = 0; $i < $invoiceRemarksCounter; $i++) {

                $invoiceRemarksCollection->putByIndex('pnr_id', $identCollection->offsetGet('id'), $i);

                $invoiceRemarksModel = new InvoiceRemarks($invoiceRemarksCollection->offsetGet($i));
//                $invoiceRemarksModel->saveOrFail();
                if(static::$output)
                {
                    print_r($invoiceRemarksCollection);
                }else{
                    $invoiceRemarksModel->saveOrFail();
                }

            }
        }
    }


    /**
     * @param $file
     * @param $agent
     * @param $status
     * @throws \Throwable
     */
    public static function setFileChecksum($file, $agent, $status)
    {

        if(file_exists($file)) {
            $fileChecksumVal = md5_file($file);
        }elseif (file_exists(str_replace('.queue','.done',$file))){
            $file = str_replace('.queue','.done',$file);
            $fileChecksumVal = md5_file($file);
        }else{
            return;
        }

        $agentChecksumVal = md5($agent);
        $checksum = md5($fileChecksumVal . $agentChecksumVal);
        //$fileChecksum = FilesChecksum::where('checksum', $checksum)->get();
        $fileChecksum = DB::connection("pgsql_crs_".$agent)->table('files_checksum')->where('checksum',$checksum)->get();
        $f = fopen($file, 'r');
        $line = fgets($f);
        fclose($f);

        if ($fileChecksum->count()) {

            if($status == ''){
                DB::connection("pgsql_crs_".$agent)->table('files_checksum')->where('checksum',$checksum)->delete();
            }else{
                DB::connection("pgsql_crs_".$agent)->table('files_checksum')->where('checksum',$checksum)->update(['status' => $status]);
            }


        } else {
            if(file_exists($file)) {
                $fileInfo = new \SplFileInfo($file);
            }elseif (file_exists(str_replace('.queue','.done',$file))){
                $file = str_replace('.queue','.done',$file);
                $fileInfo = new \SplFileInfo($file);
            }

            $filesChecksumModel = new FilesChecksum();
            $filesChecksumModel->setConnection('pgsql_crs_'.self::$agent);
            $filesChecksumModel->filename = $fileInfo->getFilename();
            $filesChecksumModel->agent = $agent;
            $filesChecksumModel->status = $status;
            $filesChecksumModel->headertxt = $line;

            $filesChecksumModel->checksum = md5($fileChecksumVal . $agentChecksumVal);
//            $filesChecksumModel->saveOrFail();
            if(static::$output)
            {
                print_r($filesChecksumModel->toArray());
            }else{
                $filesChecksumModel->saveOrFail();
            }
        }
    }


    /**
     * @param $checksum
     * @return bool
     * @throws \Exception
     */
    public static function isFileChecksumExist($checksum,$file, $agent)
    {
        $fileChecksum = DB::connection("pgsql_crs_".$agent)->table('files_checksum')->where('checksum',$checksum)->get();

        //$fileChecksum = FilesChecksum::where('checksum', $checksum)->get();

        if(!$fileChecksum->count())
        {
            //the file has not been added
            $f = fopen($file->getPathName(), 'r');
            $line = fgets($f);
            fclose($f);
            $filesChecksumModel = new FilesChecksum();
            $filesChecksumModel->setConnection("pgsql_crs_".$agent);
            $filesChecksumModel->filename = $file->getFilename();
            $filesChecksumModel->agent = $agent;
            $filesChecksumModel->status = 'OnQueue';
            $filesChecksumModel->headertxt = $line;

            $filesChecksumModel->checksum = $checksum;
//            $filesChecksumModel->saveOrFail();
            if(static::$output)
            {
                print_r($filesChecksumModel->toArray());
            }else{
                $filesChecksumModel->saveOrFail();
            }
            return false;
        }else{
//            if($fileChecksum->toArray()[0]['status'] == 'OnQueue'){
//                return false;
//            }
            return true;


        }
//
//        $onQueueStatus = FilesChecksum::where('status', 'OnQueue')->where('checksum', $checksum)->delete();
//
//        if($onQueueStatus == 0) {
//
//            $fileChecksum = FilesChecksum::where('checksum', $checksum)->get();
//            if (!empty($fileChecksum->toArray())) {
//                return true;
//            }
//        }
//
//        return false;
    }


    /**
     * @param IdentCollection $identCollection
     * @param ParticipantsCollection $participantsCollection
     * @param CustomRemarksCollection $customRemarksCollection
     * @throws \Throwable
     */
    public function saveCustomRemarks(IdentCollection &$identCollection, ParticipantsCollection &$participantsCollection,
                                             CustomRemarksCollection &$customRemarksCollection)
    {
        if (isset($customRemarksCollection) && $customRemarksCollection->count()) {

            $remarksSettings = getCustomerInvoiceSettingByMatchCode($identCollection->offsetGet('match_code'),$identCollection->offsetGet('agent'));

            foreach ($customRemarksCollection->toArray() as $single) {

                if (empty(array_filter($single))) continue;

                if($identCollection->offsetGet('match_code') !== '') {

                    $single['participants_id'] = null;
                    $single['agent'] = $identCollection->offsetGet('agent');
                    $single['pnr_id'] = $identCollection->offsetGet('id');

                    $customeRemarksModel = new CustomeRemarks($single);
                    try {
//                        $customeRemarksModel->saveOrFail();
                        if (static::$output) {
                            print_r($customRemarksCollection);
                        } else {
                            $customeRemarksModel->saveOrFail();

                        }
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__);
//                            var_dump($customeRemarksModel, $customRemarksCollection, $e->getMessage(), $participantsCollection,  static::$file);
                        die;
                    }



                }else {
                        $single['participants_id'] = null;
                        $single['agent'] = $identCollection->offsetGet('agent');
                        $single['pnr_id'] = $identCollection->offsetGet('id');

                        $customeRemarksModel = new CustomeRemarks($single);
                        try {
//                        $customeRemarksModel->saveOrFail();
                            if (static::$output) {
                                print_r($customRemarksCollection);
                            } else {
                                $customeRemarksModel->saveOrFail();

                            }
                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage().' '.__FILE__.' '.__LINE__);
//                            var_dump($customeRemarksModel, $customRemarksCollection, $e->getMessage(), $participantsCollection, static::$file);
                            die;
                        }

                }
            }
        }
    }


    public function success($file,$agent,$status = 'Imported')
    {
        static::setFileChecksum($file,$agent,$status);
        renameFile($file,'done');

    }

    public static function delFileChecksumExist($checksum,$agentName)
    {
        DB::connection("pgsql_crs_".$agentName)->table('files_checksum')->where('checksum',$checksum)->delete();
//        $fileChecksum = FilesChecksum::where('checksum', $checksum)->get();
//
//        if (!$fileChecksum->count()) {
//            $fileChecksum->each(function ($fileChecksumModel) {
//                $fileChecksumModel->delete();
//            });
//
//        }
    }
}