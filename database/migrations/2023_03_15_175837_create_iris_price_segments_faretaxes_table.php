<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisPriceSegmentsFaretaxesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_price_segments_faretaxes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('price_seg_id');
			$table->string('tax_code', 30)->nullable();
			$table->string('tax_currency', 3)->nullable();
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
		Schema::drop('iris_price_segments_faretaxes');
	}

}
