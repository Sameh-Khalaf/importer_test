<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisPriceSegmentsFarefeesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_price_segments_farefees', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('price_seg_id');
			$table->string('fee_code', 30)->nullable();
			$table->string('fee_currency', 3)->nullable();
			$table->decimal('fee_amount', 10)->nullable()->default(0.00);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('iris_price_segments_farefees');
	}

}
