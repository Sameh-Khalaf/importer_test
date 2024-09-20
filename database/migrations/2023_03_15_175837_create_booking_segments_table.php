<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookingSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('booking_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('lkz', 30)->nullable();
			$table->string('mode_of_transport', 100)->nullable();
			$table->string('destination', 100)->nullable();
			$table->string('journey_motive', 100)->nullable();
			$table->string('journey_id', 60)->nullable();
			$table->string('text_module')->nullable();
			$table->string('notice')->nullable();
			$table->smallInteger('source')->nullable()->comment('crs(id)');
			$table->smallInteger('state')->default(0)->comment('booking state');
			$table->decimal('total_price', 10)->nullable()->default(0.00);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('booking_segments');
	}

}
