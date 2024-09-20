<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOptionToBookingDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('option_to_booking_dates', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('ident_id');
			$table->string('agent', 50)->nullable();
			$table->integer('crs_id');
			$table->date('option_to_bookings_date');
			$table->string('pnr', 50);
			$table->string('touroperator', 10);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('option_to_booking_dates');
	}

}
