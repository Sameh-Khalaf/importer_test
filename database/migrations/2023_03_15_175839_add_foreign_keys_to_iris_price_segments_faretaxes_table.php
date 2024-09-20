<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIrisPriceSegmentsFaretaxesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('iris_price_segments_faretaxes', function(Blueprint $table)
		{
			$table->foreign('price_seg_id', 'iris_price_seg_faretaxes_fkey')->references('id')->on('iris_price_segments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('iris_price_segments_faretaxes', function(Blueprint $table)
		{
			$table->dropForeign('iris_price_seg_faretaxes_fkey');
		});
	}

}
