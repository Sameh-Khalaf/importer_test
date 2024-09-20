<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisPriceSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_price_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id');
			$table->string('airfare_negotiated_fare', 20)->nullable();
			$table->string('airfare_negotiated_fare_code', 20)->nullable();
			$table->decimal('basefare_amount', 10)->nullable()->default(0.00);
			$table->string('basefare_currency', 3)->nullable();
			$table->string('currency', 3)->nullable();
			$table->decimal('equivfare_amount', 10)->nullable()->default(0.00);
			$table->string('equivfare_currency', 3)->nullable();
			$table->decimal('price', 10)->nullable()->default(0.00);
			$table->string('service_segment_id', 20)->nullable();
			$table->decimal('totalfare_amount', 10)->nullable()->default(0.00);
			$table->string('totalfare_currency', 3)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('iris_price_segments');
	}

}
