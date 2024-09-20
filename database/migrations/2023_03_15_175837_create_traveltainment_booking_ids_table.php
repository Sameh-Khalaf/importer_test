<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTraveltainmentBookingIdsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('traveltainment_booking_ids', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('account_id')->nullable();
			$table->string('ttbuchid', 50)->nullable()->unique('traveltainment_booking_ids_idx');
			$table->string('pnr_id', 30)->nullable();
			$table->date('date')->nullable();
			$table->time('time')->nullable();
			$table->string('agent', 50)->nullable();
			$table->string('status', 1)->nullable();
			$table->string('engine', 50)->nullable();
			$table->boolean('file_exported')->nullable();
			$table->dateTime('import_datetime')->nullable()->default('now()');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('traveltainment_booking_ids');
	}

}
