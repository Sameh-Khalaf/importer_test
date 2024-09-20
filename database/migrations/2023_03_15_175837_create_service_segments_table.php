<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('service_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('service_segments_pnr_id');
			$table->string('service_name', 60)->nullable();
			$table->string('pnr', 60)->default('');
			$table->string('tourop', 30)->default('');
			$table->string('product', 30)->default('');
			$table->char('payment', 1)->default('A');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('service_segments');
	}

}
