<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInsuranceDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('insurance_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->text('name')->nullable();
			$table->text('beneficiary')->nullable();
			$table->integer('no_ppl')->nullable();
			$table->text('address')->nullable();
			$table->text('emergency')->nullable();
			$table->string('depdate', 10)->nullable();
			$table->string('arrdate', 10)->nullable();
			$table->text('trip')->nullable();
			$table->text('tripvalue')->nullable();
			$table->text('geozone')->nullable();
			$table->text('tocode')->nullable();
			$table->string('insurance_provider_code')->nullable();
			$table->string('insurance_provider_name')->nullable();
			$table->string('insurance_product_code')->nullable();
			$table->string('insurance_product_name')->nullable();
			$table->text('product_details')->nullable();
			$table->text('extension')->nullable();
			$table->string('subscription_date')->nullable();
			$table->string('subscribtion_time')->nullable();
			$table->string('deposit_date')->nullable();
			$table->string('departure_time')->nullable();
			$table->text('reduction_code')->nullable();
			$table->string('substitute')->nullable();
			$table->string('babysit')->nullable();
			$table->string('siid')->nullable();
			$table->string('policy_number')->nullable();
			$table->string('appraisal_number')->nullable();
			$table->string('net_premium_currency')->nullable();
			$table->string('net_premium_amount')->nullable();
			$table->string('commission_percentage')->nullable();
			$table->string('commission_amount')->nullable();
			$table->string('tax_codes')->nullable();
			$table->string('tax_amounts')->nullable();
			$table->string('total_currency')->nullable();
			$table->string('total_amount')->nullable();
			$table->bigInteger('pnr_id')->nullable();
			$table->string('participants_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('insurance_data');
	}

}
