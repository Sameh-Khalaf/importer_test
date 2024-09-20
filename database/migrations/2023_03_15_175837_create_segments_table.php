<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('index_pnr_id');
			$table->string('dep_city', 30)->nullable();
			$table->string('dep_date', 14)->nullable();
			$table->string('dep_time', 14)->nullable();
			$table->string('arr_city', 30)->nullable();
			$table->string('arr_date', 14)->nullable();
			$table->string('arr_time', 14)->nullable();
			$table->decimal('fare', 10)->nullable();
			$table->decimal('total_tax', 10)->nullable();
			$table->string('flight_no', 20)->nullable();
			$table->string('class', 20)->nullable();
			$table->string('status', 4)->nullable();
			$table->text('ibonummer')->nullable();
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
			$table->string('ticket_fop', 1000)->nullable();
			$table->string('ticket_date', 1000)->nullable();
			$table->text('ticket_ticket_number')->nullable();
			$table->integer('dynares_seq_no')->nullable();
			$table->decimal('ass_total_price', 10)->nullable();
			$table->string('ass_currency', 3)->nullable();
			$table->string('ass_prod_code', 30)->nullable();
			$table->string('ass_prod_group', 100)->nullable();
			$table->decimal('ass_total_journey_price', 10)->nullable();
			$table->string('ass_booking_date', 10)->nullable();
			$table->boolean('ticket_isdomestic')->nullable()->default(0);
			$table->string('ticket_iatanr', 20)->nullable();
			$table->text('ticket_endorsement')->nullable();
			$table->text('ticket_name')->nullable();
			$table->string('ticket_orig_pnr', 100)->nullable();
			$table->string('ticket_farebase', 1000)->nullable();
			$table->string('hotel_name', 300)->nullable();
			$table->string('hotel_city', 70)->nullable();
			$table->string('hotel_room', 70)->nullable()->comment('Room Code for matching in Travelius "conf_booking_service_room_type.code"');
			$table->string('hotel_environment', 70)->nullable();
			$table->string('hotel_service', 70)->nullable();
			$table->string('hotel_remark', 70)->nullable();
			$table->string('hotel_street', 70)->nullable();
			$table->string('hotel_from_date', 10)->nullable();
			$table->string('hotel_to_date', 10)->nullable();
			$table->string('hotel_pricedescription', 70)->nullable();
			$table->string('hotel_category', 20)->nullable();
			$table->string('hotel_phone', 50)->nullable()->default('');
			$table->string('rent_company', 40)->nullable()->default('');
			$table->string('car_type', 40)->nullable()->default('');
			$table->string('fare_text', 60)->nullable()->default('');
			$table->boolean('storno_flag')->nullable()->default(0);
			$table->string('ass_policy_nr', 60)->nullable();
			$table->string('ass_pax', 3)->nullable();
			$table->string('ass_comment', 100)->nullable();
			$table->string('ass_range', 40)->nullable();
			$table->string('ass_validity', 10)->nullable();
			$table->string('car_takeover_date', 10)->nullable();
			$table->string('car_takeover_time', 8)->nullable();
			$table->string('car_takeover_from', 40)->nullable();
			$table->string('car_takeover_location', 40)->nullable();
			$table->string('car_restitution_date', 10)->nullable();
			$table->string('car_restitution_time', 8)->nullable();
			$table->string('car_restitution_from', 40)->nullable();
			$table->string('car_restitution_location', 40)->nullable();
			$table->string('car_remark', 100)->nullable();
			$table->string('car', 100)->nullable();
			$table->text('additional_info')->nullable();
			$table->text('text_modul_codes')->nullable();
			$table->string('product_type', 20)->nullable();
			$table->string('seats', 2)->nullable();
			$table->string('flight_remark', 30)->nullable();
			$table->string('seq_number', 30)->nullable();
			$table->integer('service_segment_id')->nullable()->index('segments_service_segemt_id_index');
			$table->string('insurance_flag', 30)->nullable();
			$table->decimal('fare_pp', 10)->nullable()->comment('Purchases Price in Booking');
			$table->string('hotel_board', 10)->nullable()->comment('Board Code for matching in Travelius "conf_booking_service_catering.code"');
			$table->string('hotel_country', 2)->nullable();
			$table->string('hotel_zip', 10)->nullable();
			$table->string('terminal')->nullable();
			$table->string('arr_city_name', 30)->nullable();
			$table->string('class_of_booking', 20)->nullable();
			$table->string('class_of_service', 20)->nullable();
			$table->string('dep_city_name', 30)->nullable();
			$table->string('equipment')->nullable();
			$table->string('stop_over', 20)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('segments');
	}

}
