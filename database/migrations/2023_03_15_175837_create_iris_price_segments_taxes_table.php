<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisPriceSegmentsTaxesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_price_segments_taxes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('price_seg_id');
			$table->string('tax_type', 30)->nullable();
			$table->string('tax_percent', 30)->nullable();
			$table->string('tax_currency', 3)->nullable();
			$table->string('tax_description', 100)->nullable();
			$table->decimal('tax_amount', 10)->nullable()->default(0.00);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('iris_price_segments_taxes');
	}

}
