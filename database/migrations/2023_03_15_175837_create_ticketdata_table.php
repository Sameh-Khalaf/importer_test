<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketdataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticketdata', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('indx_pnr_id');
			$table->string('number', 50)->nullable()->index('indx_ticket_number');
			$table->string('date', 150)->nullable();
			$table->string('fop', 200)->nullable();
			$table->boolean('isdomestic')->nullable();
			$table->string('iatanr', 20)->nullable();
			$table->string('endorsement', 200)->nullable();
			$table->string('name', 250)->nullable();
			$table->string('orig_pnr', 100)->nullable()->index('indx_orig_pnr');
			$table->string('farebase')->nullable();
			$table->decimal('fare_amount', 10)->nullable();
			$table->string('fare_currency', 3)->nullable();
			$table->decimal('tax_amount', 10)->nullable();
			$table->string('tax_currency', 3)->nullable();
			$table->decimal('equiv_amount', 10)->nullable();
			$table->string('equiv_currency', 3)->nullable();
			$table->decimal('commission_rate', 10)->nullable();
			$table->decimal('commission_amount', 10)->nullable();
			$table->decimal('commission_vat', 10)->nullable();
			$table->string('original_number', 50)->nullable()->index('indx_ticket_original_number');
			$table->string('date_orig', 50)->nullable();
			$table->smallInteger('ticket_type')->nullable()->default(1)->comment('1 = "TKT";
2 = "CANX";
3 = "REF";
4 = "MCO";
5 = "ADM";
6 = "ACM";
10= "EMD";');
			$table->bigInteger('participants_id')->nullable();
			$table->string('valid_carrier', 3)->nullable();
			$table->string('iatanr_booking_agent', 20)->nullable();
			$table->string('iatanr_ticketing_agent', 20)->nullable();
			$table->string('fare_commission', 127)->nullable();
			$table->string('tour_code', 150)->nullable();
			$table->boolean('conjunctive_flag')->nullable()->default(0);
			$table->string('tour_operator', 150)->nullable();
			$table->boolean('partially_paid')->nullable()->default(0);
			$table->string('remaining_amount', 20)->nullable();
			$table->string('remaining_amount_currency', 4)->nullable();
			$table->boolean('company_own_cc')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticketdata');
	}

}
