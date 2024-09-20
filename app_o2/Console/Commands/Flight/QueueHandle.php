<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 9/2/20
 * Time: 5:39 PM
 */

namespace App\Console\Commands\Flight;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Matomo\Ini\IniReader;

class QueueHandle extends Command
{
    protected $signature = 'queue:handle
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';

    public function handle()
    {
        $agentConnection = $this->argument('connection');
        crsDbConnection($agentConnection);
        Artisan::call('queue:work', [ 'connection'=>"pgsql_crs_$agentConnection", '--tries' => '1','--once'=>false, '--queue' => 'HandleData']);

    }
}