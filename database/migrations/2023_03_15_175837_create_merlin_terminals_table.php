<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMerlinTerminalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('merlin_terminals', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('terminal_id')->index('merlin_terminals_terminal_id');
			$table->string('agent', 60);
			$table->boolean('active')->default(1);
			$table->string('info', 100)->nullable()->default('');
			$table->unique(['terminal_id','agent'], 'merlin_terminals_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('merlin_terminals');
	}

}
