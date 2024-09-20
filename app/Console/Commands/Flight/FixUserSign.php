<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 11/28/19
 * Time: 6:41 PM
 */

namespace App\Console\Commands\Flight;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixUserSign extends Command
{
    protected $signature = "FixUserSign  {agent}";

    public function handle(){

        $agent = $this->argument('agent');
        crsDbConnection($agent);

        $pnrIds = DB::connection("pgsql_crs_".$agent)->select("select id,pnr_id,office_id,tktoffice_id,crs_id,ticketing_sine,owner_id from ident 
            where ticketing_sine != ''  and p_timestamp::date > date('2021-08-31')  and processed=false and ignored_by is null and owner_id=0");
        if(count($pnrIds)){
           
            foreach ($pnrIds as $single){
                $single = (array)$single;
               if(!empty($single['ticketing_sine'])){
// if($single['id'] !== 390414){
//     continue;
// }
echo $single['pnr_id'].PHP_EOL;

                    $accountsCrs = getOwnerId($single['ticketing_sine'],$agent,$single['tktoffice_id'],$single['office_id'],$single['crs_id']);
                    // print_r($accountsCrs);die;
                    if(!empty($accountsCrs)){
                        print_r($single['ticketing_sine']);
                        echo PHP_EOL;
                        print_r($single['owner_id']);
                        echo PHP_EOL;
                        print_r($accountsCrs);
                        // echo PHP_EOL;die;
                        $id = $single['id'];
                        $ownerId = $accountsCrs['ownerId'];
                        $affiliate = $accountsCrs['affiliate'];
                        DB::connection("pgsql_crs_".$agent)->select("update ident set owner_id = $ownerId, affiliate =$affiliate where id = $id");
                        $fh = fopen('FixUserSignLog.txt','a');
                        fwrite($fh,date('Y-m-d H:i:s').' Update ownerId Agent=> '.$agent.' PNR=> '.$single['pnr_id'].' sign=> '.$single['ticketing_sine'].'
                           CRS=> '.$single['crs_id'].' account=> '.$accountsCrs['ownerId'].PHP_EOL.PHP_EOL);
                        fclose($fh);
                    }


               }
            }
        }

/*die('');



        $tickets = DB::select("select id,pnr_id from ident where processed = true");
//        foreach ($tickets as $singleTicket)
//        {
//            $explode = explode('-',$singleTicket->id);
//            $tkt = $explode[2];

            $agent = $this->argument('agent');

            $customerAuth =  DB::connection('pgsql_auth')->table('corp_auth')->where('corp_id',"$agent")->first();

            $officeIds = 'CAIEG226E,CAIEG264T,CAIEG28AF,CAIEG28DQ,CAIEG28K9,CAIEG28EE,CAIEG28K8,CAIEG28R1,CAIEG282X,CAIEG27P2,CAIEG28KL,CAIEG2409,CAIEG26L6,CAIEG28FG,CAIEG268T,CAIEG28PR,CAIEG27N3 ,CAIEG25E7,CAIEG25R1,CAIEG28BO,CAIEG28AJ,CAIEG28BW,CAIEG2462,CAIEG236E,CAIEG25W6,CAIEG26A0,CAIEG24T9,CAIEG28FK,CAIEG276H,CAIEG3183,CAIEG3184,CAIEG26Z6,CAIEG25T1,CAIEG2792 ,CAIEG23B0,CAIDE28AA,CAIEG28DO,CAIEG25D6,CAIEG27B6,CAIEG27U8,CAIEG28Q6,CAIEG25A8,CAIEG250N,CAIEG25J0,CAIEG28FA,CAIEG25V1,CAIEG28EV,CAIEG28FI,CAIEG25H3,CAIEG28BH,CAIEG3147,AIGEG';

//            config(['database.connections.pgsql_traveloffice.host' => $customerAuth->db_host]);
            config(['database.connections.pgsql_traveloffice.host' =>'us.travware.info']);
            config(['database.connections.pgsql_traveloffice.port' => '5432']);
            config(['database.connections.pgsql_traveloffice.database' => 'travelofficeexceldbtest']);
            config(['database.connections.pgsql_traveloffice.username' => 'postgres']);
            config(['database.connections.pgsql_traveloffice.password' => 'Ererikagi871']);
            config(['database.connections.pgsql_traveloffice.driver' => 'pgsql']);
            config(['database.connections.pgsql_traveloffice.charset' => 'utf8']);

            $ticketData = DB::connection('pgsql_traveloffice')->table('accounts_crs')->where("sign_in_id", '!=',"")->get();
            foreach ($ticketData as $single){
                $officeExplode = explode(',',$officeIds);
                foreach ($officeExplode as $soffice) {
                    $single = (array)$single;
                    if('' != $soffice) {
                        DB::connection('pgsql_traveloffice')->table('accounts_crs')
                            ->insert(['accounts_id' => $single['accounts_id'], 'crs_id' => $single['crs_id'], 'sign_in_id' => $single['sign_in_id'],
                                'active' => 'true', 'tkt_officeid' => $soffice]);

                    }

                }
            }
            var_dump($ticketData);die;
            if(null == $ticketData){
                print_r($ticketData);die;
                DB::select("update ident set processed = false where id = ".$singleTicket->id);
                echo $singleTicket->pnr_id.PHP_EOL;
            }

//        }
*/
    }

}