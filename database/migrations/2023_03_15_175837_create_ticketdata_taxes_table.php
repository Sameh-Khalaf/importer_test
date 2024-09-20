<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketdataTaxesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticketdata_taxes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('index_ticketdata_taxes_pnr_id');
			$table->string('currency', 3)->nullable();
			$table->string('code1', 10)->nullable();
			$table->string('code2', 10)->nullable();
			$table->decimal('amount', 10)->nullable();
			$table->integer('ticketdata_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticketdata_taxes');
	}

}
