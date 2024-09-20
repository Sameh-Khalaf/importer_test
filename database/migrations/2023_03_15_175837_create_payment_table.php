<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('index_payment_pnr_id');
			$table->decimal('amount', 10)->nullable();
			$table->string('cardno', 20)->nullable();
			$table->string('cccvc', 5)->nullable();
			$table->string('ccexpiry', 10)->nullable();
			$table->string('ccissuerno', 30)->nullable();
			$table->string('ccowner', 60)->nullable();
			$table->string('ccstartdate', 15)->nullable();
			$table->string('currency', 3)->nullable();
			$table->string('debitaccount', 30)->nullable();
			$table->string('debitaccountowner', 60)->nullable();
			$table->string('debitinstitute', 60)->nullable();
			$table->string('debitinstituteid', 20)->nullable();
			$table->string('issuer', 20)->nullable();
			$table->string('paymentid', 20)->nullable();
			$table->string('provider', 30)->nullable();
			$table->string('stamp', 20)->nullable();
			$table->string('status', 20)->nullable();
			$table->string('type', 20)->nullable();
			$table->smallInteger('is_wddx_payment')->default(0);
			$table->string('remark', 60)->default('');
			$table->string('payment_reference', 60)->default('');
			$table->string('remote_transaction', 60)->default('');
			$table->string('approval_code', 60)->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment');
	}

}
