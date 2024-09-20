<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisTerminalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_terminals', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('terminal_id', 20)->nullable();
			$table->string('agent', 20)->nullable();
			$table->boolean('active')->nullable()->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('iris_terminals');
	}

}
