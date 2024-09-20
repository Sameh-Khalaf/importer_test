<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRailsegTaxesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('railseg_taxes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('currency', 3)->nullable()->default('EUR');
			$table->string('tax_code', 10)->nullable();
			$table->decimal('amount', 10)->nullable();
			$table->string('service_nr', 5)->nullable();
			$table->decimal('tax', 10)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('railseg_taxes');
	}

}
