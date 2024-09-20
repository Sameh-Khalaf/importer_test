<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketdataAtcTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticketdata_atc', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('pnr_id');
			$table->integer('ticketdata_id');
			$table->string('old_base_fare_currency', 3)->nullable();
			$table->decimal('old_base_fare', 16)->nullable();
			$table->string('new_base_fare_currency', 3)->nullable();
			$table->decimal('new_base_fare', 16)->nullable();
			$table->string('base_fare_balance_currency', 3)->nullable();
			$table->decimal('base_fare_balance', 16)->nullable();
			$table->string('old_tax_currency', 3)->nullable();
			$table->decimal('old_tax', 16)->nullable();
			$table->string('new_tax_currency', 3)->nullable();
			$table->decimal('new_tax', 16)->nullable();
			$table->string('tax_balance_currency', 3)->nullable();
			$table->decimal('tax_balance', 16)->nullable();
			$table->string('ticket_difference_currency', 3)->nullable();
			$table->decimal('ticket_difference', 16)->nullable();
			$table->string('tst_collection_currency', 3)->nullable();
			$table->decimal('tst_collection', 16)->nullable();
			$table->string('penalty_currency', 3)->nullable();
			$table->decimal('penalty', 16)->nullable();
			$table->string('total_additional_collection_currency', 3)->nullable();
			$table->decimal('total_additional_collection', 16)->nullable();
			$table->string('residual_value_currency', 3)->nullable();
			$table->decimal('residual_value', 16)->nullable();
			$table->string('grand_total_currency', 3)->nullable();
			$table->decimal('grand_total', 16)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticketdata_atc');
	}

}
