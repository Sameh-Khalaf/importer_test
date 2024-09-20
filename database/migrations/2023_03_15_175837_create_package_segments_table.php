<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePackageSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('package_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('package_segments_pnr_id');
			$table->string('dep_city', 30)->nullable();
			$table->string('dep_date', 14)->nullable();
			$table->string('dep_time', 14)->nullable();
			$table->string('arr_city', 30)->nullable();
			$table->string('arr_date', 14)->nullable();
			$table->string('arr_time', 14)->nullable();
			$table->string('via_city', 30)->nullable();
			$table->string('via_date', 14)->nullable();
			$table->string('via_time', 14)->nullable();
			$table->string('dep_city2', 30)->nullable();
			$table->string('dep_date2', 14)->nullable();
			$table->string('dep_time2', 14)->nullable();
			$table->string('arr_city2', 30)->nullable();
			$table->string('arr_date2', 14)->nullable();
			$table->string('arr_time2', 14)->nullable();
			$table->string('via_city2', 30)->nullable();
			$table->string('via_date2', 14)->nullable();
			$table->string('via_time2', 14)->nullable();
			$table->string('transfer', 60)->nullable();
			$table->string('housing', 60)->nullable();
			$table->string('service_1', 60)->nullable();
			$table->string('service_2', 60)->nullable();
			$table->string('misc', 60)->nullable();
			$table->string('hotelname', 60)->nullable();
			$table->string('service_category', 60)->nullable();
			$table->string('city', 60)->nullable();
			$table->string('tour_operator', 10)->nullable();
			$table->decimal('fare', 10, 0)->nullable()->default(0.00);
			$table->string('filekey', 30)->nullable();
			$table->smallInteger('crs_seq_no')->nullable();
			$table->char('payment', 1)->default('A');
			$table->string('transport', 60)->nullable();
			$table->string('fare_text', 60)->nullable()->default('');
			$table->text('additional_info')->nullable();
			$table->text('text_modul_codes')->nullable();
			$table->string('product_type', 20)->nullable();
			$table->string('seq_number', 30)->nullable();
			$table->integer('service_segment_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('package_segments');
	}

}
