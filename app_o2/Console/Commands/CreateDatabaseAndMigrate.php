<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class CreateDatabaseAndMigrate extends Command
{
    protected $signature = 'db:create-migrate {database} {--drop}';
    protected $description = 'Create a database and run migrations';

    public function handle()
    {

        // Get the database name from the command argument
        $database = $this->argument('database');
        $dropIfExist = $this->option('drop');

        // Create the database if it doesn't exist
        $this->info("Creating database {$database}");
        $this->createDatabase($database,$dropIfExist);

        // Set the database name in the configuration
        config(['database.connections.pgsql.database' => $database]);

        // Check if connection already exists
            // Create new connection
            $connectionConfig = [
                'driver' => 'pgsql',
                'host' => 'localhost',
                'database' => $database,//$result[0]->crs_db,
                'username' => 'postgres',
                'password' => 'postgres',
                'charset' => 'utf8',
                'TIMEZONE' => 'Africa/Cairo',
            ];
            config(['database.connections.pgsql' => $connectionConfig]);
            config(['queue.connections.pgsql' => $connectionConfig]);


        // Set current connection
        DB::setDefaultConnection('pgsql');
        // Run the migrations
        $this->info("Running migrations for database {$database}");
        $this->call('migrate');

        // Seed the database
        $this->info("Seeding database {$database}");
        $this->call('db:seed', ['--class' => 'CrsTableSeeder']);
    }

    protected function createDatabase($database,$dropIfExist=false)
    {
        // Get the root username and password from the configuration
        $username = config('database.connections.pgsql.username', 'postgres');
        $password = config('database.connections.pgsql.password', 'postgres');


        $pdo = new \PDO(
            "pgsql:host=localhost;port=5432",
            $username,
            $password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        if($dropIfExist) {
            $activeSession = $pdo->query("SELECT * FROM pg_stat_activity WHERE datname = '$database'" )->fetchAll();
            foreach ($activeSession as $single){
                $pid=$single['pid'];
                $pdo->exec("SELECT pg_terminate_backend($pid)");
            }
            $pdo->exec("DROP DATABASE IF EXISTS $database");
        }
	// Check if the database already exists
 	$stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    	$stmt->execute([$database]);
    	$exists = $stmt->fetch(\PDO::FETCH_NUM);

        if (empty($exists)) {
            // Create the database if it does not exist
            //$pdo->exec("DROP DATABASE IF EXISTS $database");
            $pdo->exec("CREATE DATABASE $database");
            $this->info("Database $database created successfully.");
        } else {
            $this->info("Database $database already exists. Skipping creation.");
        }
       
    }
}
