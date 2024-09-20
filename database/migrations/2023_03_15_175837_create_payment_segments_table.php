<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id');
			$table->string('crs_code', 10)->default('');
			$table->decimal('amount', 10)->default(0.00);
			$table->string('currency')->default('EUR');
			$table->date('payment_date')->default('now()');
			$table->boolean('online')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment_segments');
	}

}
