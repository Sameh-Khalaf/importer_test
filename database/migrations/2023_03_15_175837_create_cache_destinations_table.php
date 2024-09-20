<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCacheDestinationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cache_destinations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('destination', 50)->nullable();
			$table->integer('total_visits')->nullable();
			$table->string('airline', 3)->nullable();
			$table->integer('pnr_id')->nullable();
			$table->string('agent', 20)->nullable();
			$table->string('sproduct_name', 50)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cache_destinations');
	}

}
