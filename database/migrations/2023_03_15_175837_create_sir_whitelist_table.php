<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSirWhitelistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sir_whitelist', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('agent', 100);
			$table->integer('verk_code');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sir_whitelist');
	}

}
