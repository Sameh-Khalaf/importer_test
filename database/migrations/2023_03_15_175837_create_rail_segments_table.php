<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRailSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rail_segments', function(Blueprint $table)
		{
			$table->integer('pnr_id')->nullable();
			$table->integer('segments_id')->nullable();
			$table->string('dep_city', 20)->nullable();
			$table->date('dep_date')->nullable();
			$table->string('dep_time', 14)->nullable();
			$table->string('arr_city', 20)->nullable();
			$table->date('arr_date')->nullable();
			$table->string('arr_time', 14)->nullable();
			$table->decimal('fare', 10)->nullable();
			$table->decimal('total_tax', 10)->nullable();
			$table->string('train_no', 20)->nullable();
			$table->string('class', 20)->nullable();
			$table->string('seats', 5)->nullable();
			$table->string('tariff', 8)->nullable();
			$table->string('wagon', 4)->nullable();
			$table->string('cabin', 20)->nullable();
			$table->string('document_nr', 50)->nullable();
			$table->string('via', 50)->nullable();
			$table->string('via1', 50)->nullable();
			$table->string('via2', 50)->nullable();
			$table->string('reservation_nr', 12)->nullable();
			$table->string('status', 4)->nullable();
			$table->string('segtype', 10)->nullable();
			$table->boolean('ticketed')->nullable()->default(0);
			$table->string('filekey', 50)->nullable();
			$table->string('carrier', 10)->nullable();
			$table->string('payment', 1)->nullable();
			$table->string('service_string', 200)->nullable();
			$table->string('housing', 200)->nullable();
			$table->string('tour_operator', 10)->nullable();
			$table->string('fare_currency', 3)->nullable();
			$table->string('tax_currency', 3)->nullable();
			$table->decimal('surcharges', 10, 3)->nullable();
			$table->string('surcharges_currency', 3)->nullable();
			$table->string('surcharges_type', 40)->nullable();
			$table->boolean('is_point_of_return')->nullable()->default(0);
			$table->string('ticket_fop', 100)->nullable();
			$table->string('ticket_date', 1000)->nullable();
			$table->string('ticket_ticket_number', 1000)->nullable();
			$table->integer('id', true);
			$table->boolean('smokeFlag')->nullable()->default(0);
			$table->string('tarifftext', 40)->nullable();
			$table->string('reductiontext', 30)->nullable();
			$table->string('tax_code', 20)->nullable();
			$table->smallInteger('pax')->nullable();
			$table->string('seg_subtype', 5)->nullable();
			$table->string('nvs_ordernumber', 9)->nullable()->default('');
			$table->string('ticket_command', 1)->nullable();
			$table->string('service_nr', 5)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rail_segments');
	}

}
