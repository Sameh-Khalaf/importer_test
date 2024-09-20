<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketdataRefundsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticketdata_refunds', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('ticket_number', 20);
			$table->string('domestic_flag', 1)->nullable();
			$table->string('currency', 3)->nullable()->default('EUR');
			$table->decimal('fare_paid', 10)->default(0.00);
			$table->decimal('fare_used', 10)->default(0.00);
			$table->decimal('fare_refund', 10)->default(0.00);
			$table->decimal('net_refund', 10)->default(0.00);
			$table->decimal('cancel_fee', 10)->default(0.00);
			$table->decimal('cancel_fee_commission', 10)->default(0.00);
			$table->decimal('misc_fee', 10)->default(0.00);
			$table->string('tax_code', 2)->nullable();
			$table->decimal('tax_refund', 10)->default(0.00);
			$table->decimal('refund_total', 10)->default(0.00);
			$table->string('refund_date', 10)->nullable();
			$table->string('dep_date_first_seg', 10)->nullable();
			$table->boolean('processed')->default(0);
			$table->boolean('show_in_list')->default(0);
			$table->string('agent', 50)->nullable()->default('');
			$table->decimal('commission_rate', 10)->default(0.00);
			$table->string('ticket_date', 30)->default('');
			$table->string('orig_pnr', 8)->nullable();
			$table->string('source', 30)->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticketdata_refunds');
	}

}
