<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmdDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('emd_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('pnr_id');
			$table->integer('ticketdata_id');
			$table->string('airline_code', 3)->nullable();
			$table->integer('airline_number')->nullable();
			$table->string('airline_name', 24)->nullable();
			$table->string('creation_date', 5)->nullable();
			$table->string('marketing_airline_code', 2)->nullable();
			$table->string('operating_airline_code', 2)->nullable();
			$table->string('carrier_fee_owner', 2)->nullable();
			$table->string('origin_city', 3)->nullable();
			$table->string('destination_city', 3)->nullable();
			$table->string('to_carrier', 62)->nullable();
			$table->string('at_location', 62)->nullable();
			$table->string('emd_type', 1)->nullable();
			$table->string('reason_issuance_code', 1)->nullable();
			$table->string('reason_issuance_code_desc', 87)->nullable();
			$table->string('reason_issuance_sub_code', 3)->nullable();
			$table->string('reason_issuance_sub_code_desc', 87)->nullable();
			$table->string('remarks', 221)->nullable();
			$table->string('service_remarks', 221)->nullable();
			$table->string('not_valid_before_date', 7)->nullable();
			$table->string('not_valid_after_date', 7)->nullable();
			$table->string('coupon_value', 27)->nullable();
			$table->string('issue_identifier', 1)->nullable();
			$table->string('fare_currency', 3)->nullable();
			$table->string('fare_amount', 11)->nullable();
			$table->string('inclusive_tax_included', 1)->nullable();
			$table->string('equiv_currency', 3)->nullable();
			$table->string('equiv_amount', 11)->nullable();
			$table->string('refund_currency', 3)->nullable();
			$table->string('refund_amount', 11)->nullable();
			$table->string('total_currency', 3)->nullable();
			$table->string('total_amount', 11)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('emd_data');
	}

}
