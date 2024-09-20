<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReportLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('report_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('agent', 40);
			$table->string('day', 6);
			$table->text('log')->nullable();
			$table->dateTime('created')->default('now()');
			$table->index(['agent','day'], 'index_agent');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('report_log');
	}

}
