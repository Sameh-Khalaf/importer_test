<?php


use Illuminate\Support\Facades\DB;

function calcFileChecksum($file, $agent)
{
    $fileChecksum = md5(file_get_contents($file));//md5_file($file);
    $agentChecksum = md5($agent);

    return md5($fileChecksum . $agentChecksum);
}

if (!function_exists('get_importer_ini_path')) {
    /**
     * Get the importer ini path.
     *
     * @param  string $path
     * @return string
     */
    function get_importer_ini_path()
    {
        return app()->basePath() . '/importer.ini';
    }
}

if (!function_exists('get_agents_folders')) {
    /**
     * Get the path to the agent folders.
     *
     * @param  string $ini
     * @return array
     */
    function get_agents_folders($ini)
    {
        $agents = [];
        if (is_array($ini)) {
            foreach ($ini as $single) {
                if (!is_array($single)) continue;

                if ($single['active'] != 1) continue;

                $agents[$single['agent']] = $single['folder'];
            }
        }
        return $agents;
    }
}


function crsDbConnection($agent = 'trav')
{
    $result = DB::connection('pgsql_auth')->select("select * from corp_auth where corp_id = '$agent'");

    if (isset($result[0]) && !empty($result[0])) {

        $connectionName = 'pgsql_crs_'.$agent;

        // Check if connection already exists
        if (!in_array($connectionName, config('database.connections'))) {
            // Create new connection
            $connectionConfig = [
                'driver' => 'pgsql',
                'host' => $result[0]->db_host,
                'database' => $result[0]->crs_db,
                'username' => $result[0]->db_user,
                'password' => $result[0]->db_password,
                'charset' => 'utf8',
                'TIMEZONE' => 'Africa/Cairo',
            ];
            config(['database.connections.'.$connectionName => $connectionConfig]);
            config(['database.default.'.$connectionName => $connectionConfig]);

            $connectionConfig = [
                'driver' => 'database',
                'host' => $result[0]->db_host,
                'database' => $result[0]->crs_db,
                'username' => $result[0]->db_user,
                'password' => $result[0]->db_password,
                'charset' => 'utf8',
                'TIMEZONE' => 'Africa/Cairo',
                'table' => 'jobs',
                'queue' =>  'ProcessFile'
            ];
            config(['queue.connections.'.$connectionName => $connectionConfig]);
            config(['queue.failed.database' => $connectionName]);
        }

        // Set current connection
        DB::setDefaultConnection($connectionName);

        // Test the connection
        try {
            $pdo = DB::connection($connectionName)->getPdo();
            if (!$pdo) {
                // Connection failed
                Log::error('CRS database connection failed for agent '.$agent);
                return false;
            }
        } catch (\Exception $e) {
            // Connection failed
            Log::error('CRS database connection failed for agent '.$agent);
            return false;
        }

        return true;
    }

    return false;
}

function load_custom_remarks_class($ini, $agent, $class='')
{
    // Make sure $ini is an array
    if (!is_array($ini)) {
        return false;
    }

    // Loop through each setting in $ini
    foreach ($ini as $single) {
        // Skip non-array settings or inactive settings or settings for other agents
        if (!is_array($single) || $single['active'] != 1 || $single['agent'] !== $agent) {
            continue;
        }

        if(!empty($class)){
            // load other gds remarks class, otherwise load amadeus if exist
            if($class == 'Sabre'){
                if (empty($single['customRemarksClassPathSabre']) || empty($single['customRemarksClassNameSabre'])) {
                    continue;
                }

                // Load the custom remarks class and return an instance of it
                require_once app_path() . '/Lib/' . $single['customRemarksClassPathSabre'] . '.php';
                return new $single['customRemarksClassNameSabre']();
            }
        }else {
            // Skip settings without a custom remarks class path or class name
            if (empty($single['customRemarksClassPath']) || empty($single['customRemarksClassName'])) {
                continue;
            }

            // Load the custom remarks class and return an instance of it
            require_once app_path() . '/Lib/' . $single['customRemarksClassPath'] . '.php';
            return new $single['customRemarksClassName']();
        }

    }

    // No suitable setting found, return false
    return false;
}

function checkCollectionExist($collectionName)
{

    $db = (new \MongoDB\Client())->importer;
    foreach ($db->listCollections() as $collectionInfo) {
        if ($collectionInfo->getName() == $collectionName) {
            return ;
        }
    }
    $db->createCollection($collectionName);
    $collection = $db->selectCollection($collectionName);

    $collection->createIndex(['createdAt'=>1],['expireAfterSeconds'=>86400]);
}

function addLog($params)
{
    $logger = new \Monolog\Logger("ImporterLog");
    if($params['crs_id'] == 7){
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/ImporterLog_Sabre' . date('Y-m-d') . '.log'));
        $logger->critical($params['message']);
    }
    if($params['crs_id'] == 1){
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/ImporterLog_Amadeus' . date('Y-m-d') . '.log'));
        $logger->critical($params['message']);
    }
    $collectionName = 'monitor';// .'_'. date('Y-m-d');
    try {

        if (isset($params['message'])) {
            $messageHash = md5($params['message']);
        }
        checkCollectionExist($collectionName);

        $collection = (new \MongoDB\Client())->importer->$collectionName;
        $allItems = $collection
            ->find(['ident_id'=>$params['ident_id']]);

        $count = $collection->countDocuments(['ident_id'=>$params['ident_id'],'message_hash'=>$messageHash,'agent'=>$params['agent']]);
        if($count >= 1){
            $logger = new \Monolog\Logger("MONITOR_LOG");
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/MONITOR_LOG' . date('Y-m-d') . '.log'));
            $logger->critical("ignore msg: " .$params['message']);
            return;
        }
        $time = time() * 1000;
        $mdate = new \MongoDB\BSON\UTCDateTime($time);
        $collectionArray = [
            'task_name' => isset($params['task_name']) ? $params['task_name'] : "",
            'message' => isset($params['message']) ? $params['message'] : "",
            'params' => isset($params['params']) ? $params['params'] : "",
            'match_code' => isset($params['match_code']) ? $params['match_code'] : "",
            'order_no' => isset($params['order_no']) ? $params['order_no'] : "",
            'crs_id' => isset($params['crs_id']) ? $params['crs_id'] : "",
            'pnr' => isset($params['pnr']) ? $params['pnr'] : "",
            'ident_id' => isset($params['ident_id']) ? $params['ident_id'] : "",
            'imported' => isset($params['imported']) ? $params['imported'] : "",
            'error_level' => isset($params['error_level']) ? $params['error_level'] : "0",
            'message_hash' => isset($messageHash) ? $messageHash : "",
            'agent' => isset($params['agent']) ? $params['agent'] : "",
            "createdAt"=> $mdate,
            //'seen'=>isset($params['imported']) ? $params['imported'] : "",
        ];
        //
        $res = $collection->insertOne($collectionArray);

        if ($res->getInsertedId()) {
            //$collection->createIndex(['createdAt'=>1],['expireAfterSeconds'=>5]);
        } else {
            $logger = new \Monolog\Logger("MONITOR_LOG");
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/MONITOR_LOG' . date('Y-m-d') . '.log'));
            $logger->critical("AUTOIMPORT: FAILED TO WRITE DATA! ".json_encode($collectionArray));

        }
    } catch (Exception $e) {

        $logger = new \Monolog\Logger("MONITOR_LOG");
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(storage_path() . '/logs/MONITOR_LOG' . date('Y-m-d') . '.log'));
        $logger->critical("AUTOIMPORT: [EX] FAILED TO WRITE DATA! " . $e->getMessage());
    }
}