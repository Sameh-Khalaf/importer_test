<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/4/19
 * Time: 3:05 PM
 */
//usage
// php artisan queue:process {agent}
namespace App\Jobs;


use App\Lib\Amadeus\Flight\AmadeusFlightParser;
use App\Lib\Galileo\Flight\GalileoFlightParser;
use App\Lib\Sabre\Flight\SabreFlightParser;

class ProcessFile extends Job
{

    private $file = null;

    private $agent = null;

    private $gds = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file, $agent, $gds)
    {
        //
        $this->file = $file;
        $this->agent = $agent;
        $this->gds = $gds;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        //crsDbConnection($this->agent);
        if(file_exists($this->file))
        {
            if($this->gds == '1') {
                $parser = new AmadeusFlightParser();
                $response = $parser->parse($this->file, $this->agent);
                if ($response) {
                    $response['agent'] = $this->agent;

                }
            }

            if($this->gds == '14') {
                $parser = new GalileoFlightParser();
                $response = $parser->parse($this->file, $this->agent);
                if ($response) {
                    $response['agent'] = $this->agent;

                }
            }

            if($this->gds == '7') {

                $parser = new SabreFlightParser();
                $response = $parser->parse($this->file, $this->agent);
                if ($response) {
                    $response['agent'] = $this->agent;

                }
            }
        }
    }

    public function shutdown(){
        return true;
    }
}
