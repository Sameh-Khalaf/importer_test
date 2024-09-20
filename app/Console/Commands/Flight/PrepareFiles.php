<?php
namespace App\Console\Commands\Flight;

use App\Events\ArtisanAmadeusCheck;
use App\Jobs\ProcessFile;
use App\Lib\Amadeus\Flight\DbHandler;
use Illuminate\Console\Command;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Matomo\Ini\IniReader;
use Mockery\Exception;

class PrepareFiles extends Command
{

    protected $signature = "PrepareFiles";

    private $ticketTypes = ['7A','7D','MA','RF','IA'];

    private $ignoreDate = null;

    private $extensionsToCheck='';

    private $activeGds = [];

    private $currentFileGds = '';

    /**

     *This function is responsible for handling the importer script.

     *It checks if the importer script is already running, reads the importer INI file, truncates the session table from the

     *auth database, clears the job and failed_jobs tables of all agents, and then processes their files.

     * @return void
     */
    public function handle()
    {
        // Check if the importer script is already running.
        $this->checkImporterRunning();

        // Read the importer INI file.
        $ini = $this->readImporterIni();

        // Truncate the session table from the auth database.
        $this->truncateSessionTable();


        // Loop through all the agents and process their files.
        foreach ($this->getAgents($ini) as $agentName => $singleAgent) {
            $this->clearAgentTables($agentName);
            $this->processAgentFiles($ini, $agentName, $singleAgent);
        }
    }

    /**
     * Checks if the importer script is already running by looking for an existing PID file.
     * If it is running, exits the script with a message indicating the previous PID.
     * If it is not running, creates a new PID file with the current PID.
     *
     * @return void
     */
    public function checkImporterRunning()
    {
        // Get the current process ID
        $pid = getmypid();

        // Check if a PID file already exists
        if (file_exists('/tmp/importer.pid')) {
            // If it does, read the previous PID from the file
            $oldPid = file_get_contents('/tmp/importer.pid');
        }
        // Check if the previous PID is set and if the process is still running
        if (!empty($oldPid) && isset($oldPid) && file_exists("/proc/$oldPid")) {
            // If it is, exit the script with a message indicating the previous PID
            exit('Already running pid:' . $oldPid);
        } else {
            // If it isn't, create a new PID file with the current PID
            file_put_contents('/tmp/importer.pid', $pid);
        }
    }

    /**
     * Reads the importer INI file and returns its contents.
     *
     * @return array The contents of the importer INI file.
     */
    public function readImporterIni()
    {
        $reader = new IniReader();
        return $reader->readFile(get_importer_ini_path());
    }

    /**
     * Truncates the `session` table in the `pgsql_auth` database by deleting all rows with a null `account_id`.
     *
     * @return void
     */
    public function truncateSessionTable()
    {
        DB::connection('pgsql_auth')->select("delete from session where account_id is null");
    }

    /**
     * Gets the list of agents from the given INI file.
     *
     * @param array $ini.Configuration settings
     * @return array The list of agents.
     */
    public function getAgents($ini)
    {
        return get_agents_folders($ini);
    }

    /**
     * Clears the specified agent's 'jobs' and 'failed_jobs' tables in the 'pgsql_crs_' database.
     *
     * @param string $agentName The name of the agent whose tables should be cleared.
     * @return void
     */
    public function clearAgentTables($agentName)
    {
        crsDbConnection($agentName);

        DB::connection("pgsql_crs_".$agentName)->table('jobs')->truncate();
        DB::connection("pgsql_crs_".$agentName)->table('failed_jobs')->truncate();
    }

    /**
     * Processes agent files based on the given INI file and agent name
     *
     * @param array $ini Configuration settings
     * @param string $agentName The name of the agent to process
     * @param array $singleAgent An array of agent directories to process recursively
     *
     * @return void
     */
    public function processAgentFiles(array $ini, $agentName, array $singleAgent)
    {
        // Reset ignore date to null
        $this->ignoreDate = null;

        // Iterate through the INI
        foreach ($ini as $single) {
            // If the current value is not an array, skip to the next iteration
            if (!is_array($single)) {
                continue;
            }

            // If the agent name does not match the current agent, skip to the next iteration
            if ($single['agent'] != $agentName) {
                continue;
            }

            // If the agent is not active, skip to the next iteration
            if ($single['active'] != 1) {
                continue;
            }

            // Set the active GDS to the array of active GDSes for this agent
            $this->activeGds = explode(',', $single['activeGds']);
        }

        // Iterate through the agent directories and process files recursively
        foreach ($singleAgent as $agentDir) {
            $this->processAgentFilesRecursive($ini, $agentName, $agentDir);
        }
    }


    /**
     * Process agent files recursively
     *
     * @param array $ini Configuration settings
     * @param string $agentName Name of the agent
     * @param string $agentDir Directory path of the agent
     *
     * @return void
     */
    public function processAgentFilesRecursive(array $ini, $agentName, $agentDir)
    {

        if (!is_dir($ini['ftpHome'])) {
            $this->error('FTP Home directory not found.');
            throw new Exception('FTP Home directory not found.' . PHP_EOL);
        }

        if (!is_dir($ini['ftpHome'] . '/' . $agentDir)) {
            $this->error('Agent directory not found for agent '.$agentName);
            throw new Exception('Agent directory not found for agent ' . $agentName . PHP_EOL);
        }

        if (!is_readable($ini['ftpHome'] . '/' . $agentDir)) {
            $this->error('Agent directory is not readable for agent '.$agentName);
            throw new Exception('Agent directory is not readable for agent ' . $agentName . PHP_EOL);
        }

        if (!is_writable($ini['ftpHome'] . '/' . $agentDir)) {
            $this->error('Agent directory is not writable for agent '.$agentName);
            throw new Exception('Agent directory is not writable for agent ' . $agentName . PHP_EOL);
        }

        // Check if the directory exists
        if (realpath($ini['ftpHome'] . '/' . $agentDir)) {
            // Set extensions to check from the configuration settings
            $this->extensionsToCheck = json_decode($ini['extensionsToCheck'], true);

            // Create a new recursive directory iterator
            $dirIterator = new \RecursiveDirectoryIterator(realpath($ini['ftpHome'] . '/' . $agentDir), \RecursiveDirectoryIterator::SKIP_DOTS);
            // Create a new recursive iterator
            $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);
            // Set the max depth of the iterator
            $iterator->setMaxDepth(0);

            // Count the number of files in the iterator
            $filesCount = iterator_count($iterator);
            // Create a new progress bar
            $pg = new Progress($filesCount);
            // Set the complete and incomplete symbols for the progress bar
            $pg->symbolComplete = "#";
            $pg->symbolIncomplete = "-";

            // Loop through each file in the iterator
            foreach ($iterator as $file) {
                // Check if the file is not a directory, is a file, and has a valid extension
                if (!$file->isDir() && $file->isFile() && $this->checkFileExtension($file)) {
                    // Set file permissions
                    chmod($file->getPath() . "/" . $file->getFileName(), 0777);

                    // Check if the current file is an Air file
                        // Get the first line of the file
                        $line = $this->getFirstLineOfFile($file);

                    if ($this->currentFileGds == '1') {
                        // If the file is not a valid Air file, rename it and skip it
                        if (!$this->checkIsValidAirFile($file, $line)) {
                            rename($file->getPath() . "/" . $file->getFilename(), $file->getPath() . "/" . $file->getFilename() . '.bad');
                            chmod($file->getPath() . "/" . $file->getFilename() . '.bad', 0777);
                            --$filesCount;
                            $pg->total = $filesCount;
                            $this->error('Ignore File ' . $file->getFilename());
                            continue;
                        }
                    }

                        // If the file extension is 'queue', handle the queue file
                        if($file->getExtension() == 'queue'){
                            $this->handleQueueFile($file,$agentName);
                        }

                        try {
                            // Handle the file
                            $this->handleFile($file,$agentName);

                            // Update the progress bar
                            $pg->total = $filesCount;
                            $pg->tick();
                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                            // If there is an error, decrement the file count and display the error message
                            $filesCount--;
                            $pg->total = $filesCount;
                            var_dump($e->getMessage());
                        }
                }else{
                    if($file->isFile()) {
                        $this->error('Ignore File ' . $file->getFilename());
                    }
                }
            }
        }else{
            throw new Exception('File path not found for agent '.$agentName.PHP_EOL);
        }

    }

    /**
     * Handles a file by calculating its checksum, checking if the checksum exists in the database,
     * and if not, adding the file to a job queue for processing.
     *
     * @param SplFileInfo $file The file to be handled.
     * @param string $agentName The name of the agent handling the file.
     *
     * @return void
     * @throws Exception if an error occurs during the handling of the file.
     */
    public function handleFile(\SplFileInfo $file, $agentName) {
        // Calculate the checksum of the file.
        $checkSum = calcFileChecksum($file->getPathName(), $agentName);

        // Check if the checksum already exists in the database for the file and agent.
        if(DbHandler::isFileChecksumExist($checkSum, $file, $agentName)) {
            // If the checksum already exists, return without doing anything.
            return;
        }

        try {

            // Rename the file to add the '.queue' suffix and make it writable.
            rename($file->getPath() . "/" . $file->getFilename(), $file->getPath() . "/" . $file->getFilename() . '.queue');
            chmod($file->getPath() . "/" . $file->getFilename().'.queue',0777);
//            crsDbConnection($agentName);
            // Create a new job to process the renamed file.
            $job = (new ProcessFile($file->getPath() . "/" . $file->getFilename() . '.queue', $agentName, $this->currentFileGds));

            // Push the job onto the job queue.
            // Register the custom connection with the queue manager
            \Illuminate\Support\Facades\Queue::extend('pgsql_crs_'.$agentName, function () use ($agentName) {
                return  \Illuminate\Support\Facades\Queue::connection('pgsql_crs_'.$agentName);
            });

            // Push the job onto the queue using the custom connection
            \Illuminate\Support\Facades\Queue::pushOn('ProcessFile', $job, '');

            $this->info("Processing File {$file->getFilename()}");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            // If an error occurs during the handling of the file, throw an exception.
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Reads the first line of a file and returns it as a string.
     *
     * @param string $file The file path to read from.
     *
     * @return string The first line of the file.
     */
    public function getFirstLineOfFile($file) {
        $f = fopen($file, 'r');
        $line = fgets($f);
        fclose($f);
        return $line;
    }

    /**
     * Checks whether the given file is a valid air file based on its first line.
     *
     * @param string $file The file path to check.
     * @param string $line The first line of the file.
     *
     * @return bool Returns true if the file is valid, and false otherwise.
     */
    public function checkIsValidAirFile($file, $line) {
        $explode = explode(';', $line);
        if (!isset($explode[1]) || !in_array($explode[1], $this->ticketTypes) && !$this->isInsurance($file)) {
            return false;
        }
        return true;
    }


    /**
     * Handles the queue file by calculating its checksum, deleting any existing
     * file checksum in the database, and renaming the file.
     *
     * @param \SplFileInfo $file - a reference to the queue file to handle
     * @param string $agentName - the name of the agent handling the queue
     *
     * @return void
     */
    public function handleQueueFile(\SplFileInfo &$file, $agentName)
    {
        // Calculate the checksum of the queue file
        $checkSum = calcFileChecksum($file->getPathName(), $agentName);

        // Delete any existing file checksum in the database
        DbHandler::delFileChecksumExist($checkSum, $agentName);

        // Rename the file by removing the '.queue' extension
        $newFile = str_replace('.queue', '', $file);
        rename($file->getPath() . "/" . $file->getFilename(), $newFile);

        // Update the file reference to point to the renamed file
        $file = new \SplFileInfo($newFile);
    }

    /**
     * Checks if the file extension is valid for any of the active GDSs and if the file
     * contents match the expected identifier for the GDS.
     *
     * @param string $file - The file to be checked.
     * @return bool - Returns true if the file extension and contents match any of the active GDSs,
     *                false otherwise.
     */
    public function checkFileExtension($file) {
        // Extract the file extension and convert to lowercase.
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if($this->delete0BytesFile($file)){
            return false;
        }
        // Check the file extension for each active GDS.
        foreach ($this->activeGds as $singleGds){
            if(in_array($extension,$this->extensionsToCheck[$singleGds]['ext'])){
                // Read the first line of the file.
                $line = $this->getFirstLineOfFile($file);

                // Determine the expected identifier based on the file extension.
                if(stripos($file->getFileName(),'.pnr') !== false){
                    $identifier = substr($line, 0, 2);
                }else {
                    $identifier = substr($line, 0, 4);
                }

                // Check if the file contents match the expected identifier for the GDS.
                if(in_array($identifier, $this->extensionsToCheck[$singleGds]['identifier'])){
                    $this->currentFileGds = $singleGds;
                    return true;
                }
            }
        }

        // If no matching GDS is found, return false.
        return false;
    }

    /**
     * Checks if the given file contains insurance information.
     *
     * @param string $file The path to the file to check.
     * @return bool Returns true if the file contains insurance information, false otherwise.
     */
    public function isInsurance($file) {
        // Open the file in read-only mode.
        $hfile = fopen($file, "r");

        // Read through the file line by line until the end of the file is reached.
        while (!feof($hfile)) {
            // Read a single line from the file.
            $singleLine = fgets($hfile);

            // Get the first two characters of the line.
            $twoCharsTag = substr($singleLine, 0, 2);

            // Check if the line starts with "U-".
            if ($twoCharsTag == 'U-') {
                // Split the line into segments using semicolons as delimiters.
                $explode = explode(';', $singleLine);

                // Check if the second segment contains the string "INS".
                if (isset($explode[1]) && strpos($explode[1], 'INS') !== false) {
                    // The file contains insurance information.
                    return true;
                }
            }
        }

        // The file does not contain insurance information.
        return false;
    }

    private function delete0BytesFile($file){
        if ($file->getSize() === 0) {
            unlink($file);
            $this->info("File deleted ". $file->getFilename());
            return true;
        }
    }

}



declare(ticks = 1);
pcntl_signal(SIGINT, function($signo) {
    fwrite(STDOUT, "\n\033[?25h");
    fwrite(STDERR, "\n\033[?25h");
    exit;
});

Class Progress {

    const MOVE_START = "\033[1G";
    const HIDE_CURSOR = "\033[?25l";
    const SHOW_CURSOR = "\033[?25h";

    // Available screen width
    private $width;
    // Ouput stream. Usually STDOUT or STDERR
    private $stream;
    // Output string format
    private $format;
    // Time the progress bar was initialised in seconds (with millisecond precision)
    private $startTime;
    // Time since the last draw
    private $timeSinceLastCall;
    // Pre-defined tokens in the format
    private $ouputFind = array(':current', ':total', ':elapsed', ':percent', ':eta', ':rate');
    // The symbol to denote completed parts of the bar
    public $symbolComplete = "=";
    // The symbol to denote incomplete parts of the bar
    public $symbolIncomplete = " ";
    // Current tick number
    public $current = 0;
    // Maximum number of ticks
    public $total = 1;
    // Seconds elapsed
    public $elapsed = 0;
    // Current percentage complete
    public $percent = 0;
    // Estimated time until completion
    public $eta = 0;
    // Current rate
    public $rate = 0;

    public $file = '';

    public function __construct(&$total = 1, $format = "Progress: [:bar] - :current/:total - :percent% - Elapsed::elapseds - ETA::etas - Rate::rate/s", $stream = STDERR) {
        // Get the terminal width
        $this->width = exec("tput cols");
        if (!is_numeric($this->width)) {
            // Default to 80 columns, mainly for windows users with no tput
            $this->width = 80;
        }

        $this->total = &$total;
        $this->format = $format;
        $this->stream = $stream;

        // Initialise the display
        fwrite($this->stream, self::HIDE_CURSOR);
        fwrite($this->stream, self::MOVE_START);

        // Set the start time
        $this->startTime = microtime(true);
        $this->timeSinceLastCall = microtime(true);

        $this->drawBar();
    }

    public function tick($amount = 1) {
        if($this->total == 0) return;
        $this->current = $this->current + $amount;
        $this->elapsed = microtime(true) - $this->startTime;
        $this->percent = $this->current / $this->total * 100;
        $this->rate = $this->current / $this->elapsed;
        $this->eta = ($this->current) ? ($this->elapsed / $this->current * $this->total - $this->elapsed) : false;
        $this->drawBar();


    }



    /**
     * Does the actual drawing
     */
    private function drawBar() {
        $this->timeSinceLastCall = microtime(true);
        fwrite($this->stream, self::MOVE_START);

        $replace = array(
            $this->current,
            $this->total,
            $this->roundAndPadd($this->elapsed),
            $this->roundAndPadd($this->percent),
            $this->roundAndPadd($this->eta),
            $this->roundAndPadd($this->rate),
        );

        $output = str_replace($this->ouputFind, $replace, $this->format);

        if (strpos($output, ':bar') !== false) {
            $availableSpace = $this->width - strlen($output) + 4;
            $done = $availableSpace * ($this->percent / 100);
            $left = $availableSpace - $done;
            if($done ==0 || $left <= 0) return;
            //var_dump($left);
            $output = str_replace(':bar', str_repeat($this->symbolComplete, $done) . str_repeat($this->symbolIncomplete, $left), $output);
        }

        fwrite($this->stream, $output);
    }

    /**
     * Adds 0 and space padding onto floats to ensure the format is fixed length nnn.nn
     */
    private function roundAndPadd($input) {
        $parts = explode(".", round($input, 2));
        $output = $parts[0];
        if (isset($parts[1])) {
            $output .= "." . str_pad($parts[1], 2, 0);
        } else {
            $output .= ".00";
        }

        return str_pad($output, 6, " ", STR_PAD_LEFT);
    }

    public function __destruct() {
        fwrite($this->stream, "\n" . self::SHOW_CURSOR);
    }

}