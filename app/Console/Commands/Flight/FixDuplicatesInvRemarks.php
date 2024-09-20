<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 11/28/19
 * Time: 6:41 PM
 */

namespace App\Console\Commands\Flight;


use App\InvoiceRemarks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDuplicatesInvRemarks extends Command
{
    protected $signature = "FixDINVR  {agent}";

    public function handle(){

        $agent = $this->argument('agent');
        crsDbConnection($agent);

        $pnrIds = DB::connection("pgsql_crs_".$agent)->select("select id,pnr_id from ident 
            where p_timestamp::date > date('2023-04-10')  and processed=true and ignored_by is null");

        $first = [];
        if(count($pnrIds)){
           
            foreach ($pnrIds as $single){
                $single = (array)$single;

                $id = $single['id'];

                $remarks = DB::connection("pgsql_crs_".$agent)->select("select * from invoice_remarks where pnr_id=$id");

                $sanitizedRemarks = [];
                $invoiceRemarks = [
                    'MANAGEMENT FEES',
                    'DISCOUNT',
                    'MARKUP',
                    'DIP MARKUP',
                    'RIFFILE'
                ];
                foreach ($invoiceRemarks as $singleRemark){
                    foreach ($remarks as $remark) {

                        if($remark->remark_type == $singleRemark){
                            $sanitizedRemarks[] = (array)$remark;
                        }
                    }
                }
              

                if(!empty($sanitizedRemarks)) {
                    echo $id.PHP_EOL;
                    DB::connection("pgsql_crs_".$agent)->delete("delete from invoice_remarks where pnr_id=$id");
                    foreach ($sanitizedRemarks as $single) {
                        $invoiceRemarksModel = new InvoiceRemarks($single);
                        $invoiceRemarksModel->setConnection('pgsql_crs_' . $agent);
                        $invoiceRemarksModel->saveOrFail();
                    }
                    $first[] = $id;
                }

            }
        }
        PRINT_r($first);die;

    }

}