<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTraveltainmentAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('traveltainment_accounts', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('accountname', 30);
			$table->string('password', 20);
			$table->boolean('active')->default(1);
			$table->string('agent', 20)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('traveltainment_accounts');
	}

}
