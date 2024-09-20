<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTrafficsTerminalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('traffics_terminals', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('terminal_id', 10);
			$table->string('agent', 60);
			$table->boolean('active')->default(1);
			$table->string('info', 100)->nullable()->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('traffics_terminals');
	}

}
