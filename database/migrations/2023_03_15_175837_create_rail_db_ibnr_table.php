<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRailDbIbnrTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rail_db_ibnr', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('ibnr')->nullable();
			$table->string('railstation', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rail_db_ibnr');
	}

}
