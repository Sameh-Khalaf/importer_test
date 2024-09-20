<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 10/14/19
 * Time: 4:01 PM
 */

namespace App\Console\Commands\Flight;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Matomo\Ini\IniReader;
use App\Lib\SQLite;

class AutoImport extends Command
{
    protected $signature = "AutoImport";

    private $specialOfficeIds = [];
    private $autoImportOfficeIds = [];
    private $specialOfficeIdsPass = [];
    private $ftpIpAddress = '';
    private $logpath ='';
    private $autoImport = 0;

    private $sqliteObj = null;

    private function init()
    {


        try {
            if (isset($_SERVER['HOME']) && $_SERVER['HOME'] == "/home/aymen-ahmed") {

                $this->logpath = $logdbPath = '/tmp/importer' . date('dmY') . '.db';
                if (!file_exists($logdbPath)) {

                    touch($logdbPath);
                    chmod($logdbPath,0777);
                }
;
                $this->sqliteObj = new SQLite($logdbPath);
            } else {
                $logdbPath = "/tmp/importer" . date('dmY') . '.log';
                if (!file_exists($logdbPath)) {

                    touch($logdbPath);
                    chmod($logdbPath,0777);
                }

                $this->sqliteObj = new SQLite($logdbPath);
            }

            $commands = ['CREATE TABLE IF NOT EXISTS logs (
                        id   INTEGER PRIMARY KEY,
                        task_name VARCHAR (255) NOT NULL,
                        process_id INTEGER NOT NULL,
                        message TEXT NOT NULL,
                        date   VARCHAR (255) NOT NULL,
                        data TEXT,
                        match_code TEXT,
                        order_no TEXT,
                        crs_id INTEGER,
                        pnr_id TEXT,
                        row_color TEXT,
                        ident_id INTEGER,
                        imported INTEGER NOT NULL
                      )',
            ];
            foreach ($commands as $command) {
//                $this->pdo->exec($command);
                $this->sqliteObj->query($command);
            }


        } catch (\PDOException $e) {
            if(file_exists($logdbPath)) {
                system("rm -rf " . $logdbPath);
            }
            // handle the exception here
            var_dump($e->getMessage());
            die;
        }

//
    }

    public function handle()
    {


        $pid = getmypid();
        $oldPid = @file_get_contents('/tmp/auto_import.pid');
        if ($oldPid && file_exists("/proc/$oldPid")) {
            exit('Already running pid:' . $oldPid);
        } else {
            file_put_contents('/tmp/auto_import.pid', $pid);
        }


        $reader = new IniReader();
        $ini = $reader->readFile(get_importer_ini_path());
        $agents = get_agents_folders($ini);

        foreach ($agents as $agentName => $singleAgent) {

            if(is_array($ini))
            {
                foreach ($ini as $single)
                {

                    if(!is_array($single)) continue;

                    if($single['active'] != 1) continue;

                    if($single['agent'] == $agentName){
                        if(isset($single['autoImportOfficeIds']) && !empty($single['autoImportOfficeIds'])){
                            $this->autoImportOfficeIds = explode(',',$single['autoImportOfficeIds']);
                        }
                        if(isset($single['specialOfficeIds']) && !empty($single['specialOfficeIds'])){
                            $this->specialOfficeIds = explode(',',$single['specialOfficeIds']);
                        }
                        if(isset($single['specialOfficeIdsPass']) && !empty($single['specialOfficeIdsPass'])){
                            $this->specialOfficeIdsPass = explode('-;-',$single['specialOfficeIdsPass']);
                        }
                        if(isset($single['pdfFtpIP']) && !empty($single['pdfFtpIP'])){
                            $this->ftpIpAddress = $single['pdfFtpIP'];
                        }
                        if($single['autoImport'] == '0'){
                            $this->autoImport = 0;
                        }else{
                            $this->autoImport = 1;
                        }

                        if(file_exists(realpath('importer-settings.json'))){
                            $contents = file_get_contents(realpath('importer-settings.json'));
                            $contentsArray = json_decode($contents,true);
                            if(isset($contentsArray[$agentName])){
                                if($contentsArray[$agentName][0]['autoImport'] == 'false'){
                                    $this->autoImport = 0;
                                }
                            }
                        }

                    }
                }
            }
            if( $this->autoImport == 0){
                break;
            }



            crsDbConnection($agentName);

            $db = (new \MongoDB\Client())->importer;
            $db->dropCollection('monitor');

            /*****************************************************/
            /* ### Auto Import by travel agency for Sabre #######*/

            $sql = "select * from ident where processed = false and ignored_by is null   
                      and agent = '{$agentName}' and version = 1  and crs_id = 7" ;

            $crsData = DB::connection('pgsql_crs_'.$agentName)->select($sql);
            if(isset($crsData) && count($crsData)){
                foreach ($crsData as $single){
                    if($single->owner_id == 0) {

                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Ticket Owner id is 0 in this PNR: $single->pnr_id ({$single->id}) , sign: [{$single->ticketing_sine}]",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,


                        ]);

                        continue;
                    }
                    if($single->affiliate == 0) {
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Affiliate id is 0 in this PNR: $single->pnr_id ({$single->id})",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,

                        ]);
                        continue;
                    }
                    $params = [
                        'pnr_id' => $single->id,
                        'pnr' => $single->pnr_id,
                        'crs_id' => $single->crs_id,
                        'owner_id' => $single->owner_id,
                        'affiliate' => $single->affiliate,
                        'agent' => $single->agent,
                        'tkt_officeId'=>$single->tktoffice_id,
                        'autoimport_action'=>'travelAgencyOfficeId'
                    ];

                    // $client = new \GuzzleHttp\Client(['verify'=>false]);
                    // $response = $client->request('post',
                    //     env('traveloffice').'/api/crs-inbox/auto-import',
                    //     ['form_params' => $params]);

                    // print_r( $response->getBody()->getContents());
                }
            }

            /*****************************************************/
            /*###################################################*/


//            $sql = "select * from public.ident i where exists
//            (select * from ident i2 where (i2.pnr_id =i.pnr_id or i2.pnr_id=i.pnr_original)  and i2.processed = true and i2.version = 1 and i2.agent= '$agentName' )
//            and i.processed is not true and i.agent= '$agentName' and ignored_by is null order by i.version asc";
//            print_r($sql);die;
//            $sql = "select * from public.ident i where exists
//            (select * from ident i2 where (i2.pnr_id =i.pnr_id or i2.pnr_id=i.pnr_original)  and i2.processed = true and i2.version = 1 and i2.agent= '$agentName' )
//            and i.processed is not true and i.agent= '$agentName' and ignored_by is null order by i.version asc";
            $sql = "select * from ident where processed = false and ignored_by is null  AND (COALESCE(match_code,'') != ''  or COALESCE(auto_import_order,'') != '') 
                      and agent = '{$agentName}' and (version = 1 or version = 20 ) " ;

            $crsData = DB::connection('pgsql_crs_'.$agentName)->select($sql);

            if (isset($crsData) && !empty($crsData)) {


                foreach ($crsData as $single) {
                    //if($single->pnr_id != "TVADIZ") continue;
                   // if($single->pnr_id != "tklg9x") continue;
                   // if($single->pnr_id != "R3Q4BR") continue;

                   // if($single->crs_id == 7) continue;
                    
                    if($single->owner_id == 0) {

                        addLog([
                           'task_name'=>'AutoImport',
                           'message'=>"Error: Ticket Owner id is 0 in this PNR: $single->pnr_id ({$single->id}) , sign: [{$single->ticketing_sine}]",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,


                        ]);

                        //$this->logIt("#8b0000",__FUNCTION__,"",$single->pnr_id,[],$single->id);
                        continue;
                    }
                    if($single->affiliate == 0) {
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Affiliate id is 0 in this PNR: $single->pnr_id ({$single->id})",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,

                        ]);
                       // $this->logIt("#8b0000",__FUNCTION__,"Unable to Import affiliate id is 0 in this PNR: $single->pnr_id ({$single->id})",$single->pnr_id,[],$single->id);
                        continue;
                    }
//                    if($i == 100)break;

                    if(false === ($key = array_search($single->tktoffice_id,$this->autoImportOfficeIds))){
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Ticket OfficeId #$single->tktoffice_id is not appear in in AutoImport officeids for this PNR: $single->pnr_id ({$single->id})",
                            'params'=> json_encode((array)$this->autoImportOfficeIds),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,

                        ]);
                       /* $this->logIt("#8b0000",__FUNCTION__,"Unable to Import Ticket OfficeId #$single->tktoffice_id is not valid in this PNR: $single->pnr_id ({$single->id})",
                            $single->pnr_id,[],$single->id);*/
                        continue;
                    }
                    if(false !== ($key = array_search($single->tktoffice_id,$this->specialOfficeIds))){

                        $generatePDF = true;
                        $targetOfficeId = $single->tktoffice_id;
                        $targetOfficeIdPass = '';
                        if(isset($this->specialOfficeIdsPass[$key])) {
                            $targetOfficeIdPass = $this->specialOfficeIdsPass[$key];
                        }
                    }elseif (false !== ($key = array_search($single->office_id,$this->specialOfficeIds))){

                        $generatePDF = true;
                        $targetOfficeId = $single->office_id;
                        $targetOfficeIdPass = '';
                        if(isset($this->specialOfficeIdsPass[$key])) {
                            $targetOfficeIdPass = $this->specialOfficeIdsPass[$key];
                        }
                    }else{
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"NOTICE: No Invoice PDF will be generated for this PNR: $single->pnr_id ({$single->id}) 
                                         because TicketingOfficeId or BookingOfficeId not appear in the allowed OfficeIds list",
                            'params'=> json_encode((array)$this->specialOfficeIds),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "1",
                            'agent'=> $single->agent,

                        ]);
                        $generatePDF = false;
                        $targetOfficeId = '';
                        $targetOfficeIdPass='';
                    }
                    if($agentName == 'Exceltravelde'){
                        $single->has_online_invoice = false;
                    }

                    $params = [
                        'pnr_id' => $single->id,
                        'matchode' => $single->match_code,
                        'orderno' => $single->auto_import_order,
                        'owner_id' => $single->owner_id,
                        'affiliate' => $single->affiliate,
                        'agent' => $single->agent,
                        'auto_invoice'=>$single->has_online_invoice,
                        'auto_voucher'=>$single->has_online_voucher,
                        'generate_pdf'=>$generatePDF,
                        'target_officeid'=>$targetOfficeId,
                        'target_officeidpass'=>$targetOfficeIdPass,
                        'ftp_ip'=>$this->ftpIpAddress,
                        'original_number'=>''
                    ];

                    $client = new \GuzzleHttp\Client(['verify'=>false]);
                    $response = $client->request('post',
                        env('traveloffice').'/api/crs-inbox/auto-import',
                        ['form_params' => $params]);

                    print_r( $response->getBody()->getContents());
                   // die('xxx');
                }
            }

            // $sql = "select * from public.ident i where exists
            // (select * from ident i2 where (i2.pnr_id =i.pnr_id or i2.pnr_id=i.pnr_original)  and i2.processed = true and (i2.version = 1 or i2.version= 7) and i2.agent= '$agentName' and i2.crs_id='1' )
            // and i.processed is not true and i.agent= '$agentName' and crs_id='1' and ignored_by is null and version !=1 and version != 20 order by i.version asc  ";
            $sql = "select * from public.ident i where exists
            (select * from ident i2 where (i2.pnr_id =i.pnr_id or i2.pnr_id=i.pnr_original)  and i2.processed = true and (i2.version = 1 or i2.version= 7) and i2.agent= '$agentName'  )
            and i.processed is not true and i.agent= '$agentName' and ignored_by is null and version !=1 and version != 20 order by i.version asc  ";

            $crsData = DB::connection('pgsql_crs_'.$agentName)->select($sql);

            if (isset($crsData) && !empty($crsData)) {


                foreach ($crsData as $single) {

                //    if($single->crs_id == 7) {
                //     var_dump($single->pnr_id);
                //    }

                    
                    //if($single->pnr_id != "TVADIZ") continue;
                    //if($single->pnr_id != "MQW2J5X") continue;
                    //var_dump($single->pnr_id);
                    //if($single->pnr_id != "UGLNJ6") continue;
//                    if($i == 100)break;
                    if($single->owner_id == 0) {

                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Ticket Owner id is 0 in this PNR: $single->pnr_id ({$single->id}) , sign: [{$single->ticketing_sine}]",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,


                        ]);

                        //$this->logIt("#8b0000",__FUNCTION__,"",$single->pnr_id,[],$single->id);
                        continue;
                    }
                    if($single->affiliate == 0) {
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Affiliate id is 0 in this PNR: $single->pnr_id ({$single->id})",
                            'params'=> json_encode((array)$single),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,

                        ]);
                        // $this->logIt("#8b0000",__FUNCTION__,"Unable to Import affiliate id is 0 in this PNR: $single->pnr_id ({$single->id})",$single->pnr_id,[],$single->id);
                        continue;
                    }
//                    if($i == 100)break;

                    if(false === ($key = array_search($single->tktoffice_id,$this->autoImportOfficeIds))){
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"Error: Ticket OfficeId #$single->tktoffice_id is not appear in AutoImport officeids for this PNR: $single->pnr_id ({$single->id})",
                            'params'=> json_encode((array)$this->autoImportOfficeIds),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "3",
                            'agent'=> $single->agent,

                        ]);
                        /* $this->logIt("#8b0000",__FUNCTION__,"Unable to Import Ticket OfficeId #$single->tktoffice_id is not valid in this PNR: $single->pnr_id ({$single->id})",
                             $single->pnr_id,[],$single->id);*/
                        continue;
                    }
                    if(false !== ($key = array_search($single->tktoffice_id,$this->specialOfficeIds))){

                        $generatePDF = true;
                        $targetOfficeId = $single->tktoffice_id;
                        $targetOfficeIdPass = '';
                        if(isset($this->specialOfficeIdsPass[$key])) {
                            $targetOfficeIdPass = $this->specialOfficeIdsPass[$key];
                        }
                    }elseif (false !== ($key = array_search($single->office_id,$this->specialOfficeIds))){

                        $generatePDF = true;
                        $targetOfficeId = $single->office_id;
                        $targetOfficeIdPass = '';
                        if(isset($this->specialOfficeIdsPass[$key])) {
                            $targetOfficeIdPass = $this->specialOfficeIdsPass[$key];
                        }
                    }else{
                        addLog([
                            'task_name'=>'AutoImport',
                            'message'=>"NOTICE: No Invoice PDF will be generated for this PNR: $single->pnr_id ({$single->id}) 
                                         because TicketingOfficeId or BookingOfficeId not appear in the allowed OfficeIds list",
                            'params'=> json_encode((array)$this->specialOfficeIds),
                            'match_code'=> $single->match_code,
                            'order_no'=> $single->auto_import_order,
                            'crs_id'=> $single->crs_id,
                            'pnr'=> $single->pnr_id,
                            'ident_id'=> $single->id,
                            'imported'=> 0,
                            'error_level'=> "1",
                            'agent'=> $single->agent,

                        ]);
                        $generatePDF = false;
                        $targetOfficeId = '';
                        $targetOfficeIdPass='';
                    }
                    if($agentName == 'Exceltravelde'){
                        $single->has_online_invoice = false;
                    }
                    $originalNumber = '';
                    if($single->version == 10){
                        //check if we have icw

                        $sql = "select original_number from ticketdata where pnr_id=$single->id";
                        $ticketData = DB::connection('pgsql_crs_'.$agentName)->select($sql);
                        if(isset($ticketData) && !empty($ticketData)){
                            $originalNumber = $ticketData[0]->original_number;
                        }

                    }

                    //auto invoice after emd
                    if($single->version == 7)
                    {
                        $sql = "select number from ticketdata where pnr_id=$single->id";
                        $ticketData = DB::connection('pgsql_crs_'.$agentName)->select($sql);
                        if(isset($ticketData) && !empty($ticketData)){
                            $tkNumber = $ticketData[0]->number;

                            //check if we have emd for this reissue
                            $sql = "select number from ticketdata where original_number like '%$tkNumber%' and ticket_type = 10";
                            $emdData = DB::connection('pgsql_crs_'.$agentName)->select($sql);
                            if(isset($emdData) && isset($emdData[0]) && !empty($emdData[0])){
                                $single->has_online_invoice = 0;
                                $generatePDF = 0;
                            }
                        }
                    }
                    $params = [
                        'pnr_id' => $single->id,
                        'matchode' => $single->match_code,
                        'orderno' => $single->auto_import_order,
                        'owner_id' => $single->owner_id,
                        'affiliate' => $single->affiliate,
                        'agent' => $single->agent,
                        'auto_invoice'=>$single->has_online_invoice,
                        'generate_pdf'=>$generatePDF,
                        'target_officeid'=>$targetOfficeId,
                        'target_officeidpass'=>$targetOfficeIdPass,
                        'ftp_ip'=>$this->ftpIpAddress,
                        'original_number'=>$originalNumber
                    ];

                    $client = new \GuzzleHttp\Client(['verify'=>false]);
                    $response = $client->request('post',
                        env('traveloffice').'/api/crs-inbox/auto-import',
                        ['form_params' => $params]);

                    print_r( $response->getBody()->getContents());//die;

                }
            }
        }
    }

    private function logIt($color='', $task_name, $message, $pnr = '',$data = [], $ident_id='',$imported = 0)
    {
        return;
        //check if pnr id with same status exist
        if(null == $this->sqliteObj){
            $this->init();
        }
        $res = null;
        $message = base64_encode($message);
        //if(!empty($ident_id)){
        $res = $this->sqliteObj->get_row("SELECT * FROM logs where  ident_id =  '$ident_id' and imported = '$imported' and message = '$message'");
        //}

        if(null === $res || false === $res){
            $insertData = ['task_name' => $task_name,
                'process_id' => getmypid(),
                'message' => $message,
                'date' => date('d.m.Y H:i:s'),
                'data' => json_encode($data),
                'match_code' => isset($data['matchode'])? $data['matchode'] : '',
                'crs_id' => 1,
                'pnr_id'=>$pnr,
                'ident_id'=>$ident_id,
                'row_color'=>$color,
                'imported' => $imported
            ];


            try {
                $res = $this->sqliteObj->insert('logs', $insertData);
                $this->sqliteObj = null;
            } catch (\PDOException $e) {
                if(file_exists($this->logpath)) {
                    system("rm -rf " . $this->logpath);
                    print_r(PHP_EOL."Restart ... ".PHP_EOL);
                    exit;
                }
            }
        }else {
            return false;

        }

    }

}
