<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCacheAirlineStatisticsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cache_airline_statistics', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('sproduct_name', 50)->nullable();
			$table->string('agent', 20)->nullable();
			$table->string('airline', 3)->nullable();
			$table->integer('issues')->nullable()->default(0);
			$table->integer('refunds')->nullable()->default(0);
			$table->integer('voids')->nullable()->default(0);
			$table->integer('exchanges')->nullable()->default(0);
			$table->integer('emds')->nullable()->default(0);
			$table->integer('void_emds')->nullable()->default(0);
			$table->integer('void_refunds')->nullable()->default(0);
			$table->integer('pnr_id')->nullable();
			$table->string('journey_from_date', 10)->nullable();
			$table->string('journey_till_date', 10)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cache_airline_statistics');
	}

}
