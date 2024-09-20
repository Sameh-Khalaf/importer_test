<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('car_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('takeover_date', 30)->nullable();
			$table->string('takeover_time', 30)->nullable();
			$table->string('takeover_from', 40)->nullable();
			$table->string('takeover_location', 40)->nullable();
			$table->string('restitution_date', 30)->nullable();
			$table->string('restitution_time', 30)->nullable();
			$table->string('restitution_from', 40)->nullable();
			$table->string('restitution_location', 40)->nullable();
			$table->string('carclass', 40)->nullable();
			$table->string('car')->nullable();
			$table->string('remark')->nullable();
			$table->decimal('fare', 10)->nullable();
			$table->string('segtype', 10)->nullable();
			$table->string('tour_operator', 10)->nullable();
			$table->string('filekey', 50)->nullable();
			$table->string('payment', 1)->nullable();
			$table->integer('dynares_seq_no')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('car_segments');
	}

}
