<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvoiceRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoice_remarks', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->text('remark');
			$table->string('remark_type', 50);
			$table->string('currency', 3);
			$table->string('amount', 50);
			$table->integer('pnr_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoice_remarks');
	}

}
