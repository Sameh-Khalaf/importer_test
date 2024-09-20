<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateParticipantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('participants', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('index_p_pnr_id');
			$table->string('title')->nullable();
			$table->string('first_name', 60)->nullable();
			$table->string('last_name', 60)->nullable();
			$table->string('dob', 14)->nullable();
			$table->string('type', 10)->nullable();
			$table->string('gender', 1)->nullable()->default('M');
			$table->string('street', 80)->nullable();
			$table->string('zip', 10)->nullable();
			$table->string('phone', 20)->nullable();
			$table->string('city', 40)->nullable();
			$table->decimal('tax', 10)->nullable();
			$table->string('pax_number', 60)->default('');
			$table->string('addon1', 60)->default('');
			$table->string('addon2', 60)->default('');
			$table->string('addon3', 60)->default('');
			$table->integer('service_segment_id')->nullable();
			$table->string('price_map', 60)->nullable();
			$table->string('tariff_code', 30)->nullable();
			$table->string('degree', 30)->nullable();
			$table->string('price', 40)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('participants');
	}

}
