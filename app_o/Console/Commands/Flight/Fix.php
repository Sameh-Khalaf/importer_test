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

class Fix extends Command
{
    protected $signature = "fix  {agent}";

    public function handle(){

        $agent = $this->argument('agent');
        crsDbConnection($agent);

        $pnrIds = DB::connection("pgsql_crs_".$agent)->select("select id,pnr_id from ident 
            where p_timestamp::date > date('2023-05-06')  and crs_id=7");
        if(count($pnrIds)){
           
            foreach ($pnrIds as $single){
                $single = (array)$single;

                $id = $single['id'];

                $filesObj = DB::connection("pgsql_crs_".$agent)->select("select * from processed_files where pnr_id=$id");
                $file = $filesObj[0];
                $content = $file->content;
                $matchCode = Sab_matchMatchCode($content);
                DB::connection("pgsql_crs_".$agent)->update("update ident set match_code = '$matchCode' where  id=$id");

            }
        }


    }

}