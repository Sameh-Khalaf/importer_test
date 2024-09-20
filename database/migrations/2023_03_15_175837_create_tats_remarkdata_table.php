<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTatsRemarkdataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tats_remarkdata', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->index('index_tats_remark_pnr_id');
			$table->string('csv_reason_code', 3)->default('');
			$table->decimal('csv_tariff_booked', 10)->default(0.0);
			$table->string('csv_lowest_code', 3)->default('');
			$table->decimal('csv_tariff_lowest', 10)->default(0.0);
			$table->string('csv_highest_code', 3)->default('');
			$table->decimal('csv_tariff_highest', 10)->default(0.0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tats_remarkdata');
	}

}
