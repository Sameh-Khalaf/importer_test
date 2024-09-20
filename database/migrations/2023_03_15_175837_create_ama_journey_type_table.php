<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAmaJourneyTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ama_journey_type', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('ama_kuerzel', 5)->nullable();
			$table->string('sro24_text', 30)->nullable();
			$table->integer('sro24_type')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ama_journey_type');
	}

}
