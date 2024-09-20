<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::Table("crs")->insert(
            [
                'id'=>'1',
                'crs_name'=>'Amadeus',
            ]
        );
        DB::Table("crs")->insert(
            [
                'id'=>'7',
                'crs_name'=>'Sabre',
            ]

        );

        DB::Table("crs")->insert(
            [
                'id'=>'14',
                'crs_name'=>'Galileo',
            ]
        );
    }
}
