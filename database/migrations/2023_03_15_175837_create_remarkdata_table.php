<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRemarkdataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('remarkdata', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('remark_data_pnr_id_index');
			$table->string('price1_text', 60)->nullable();
			$table->string('price1_no', 5)->nullable();
			$table->string('price1_amount', 20)->nullable();
			$table->string('price2_text', 60)->nullable();
			$table->string('price2_no', 5)->nullable();
			$table->string('price2_amount', 20)->nullable();
			$table->string('price3_text', 60)->nullable();
			$table->string('price3_no', 5)->nullable();
			$table->string('price3_amount', 20)->nullable();
			$table->string('customer_nr', 60)->nullable();
			$table->string('customer_last_name', 60)->nullable();
			$table->string('customer_first_name', 60)->nullable();
			$table->string('customer_street', 60)->nullable();
			$table->string('customer_zip', 15)->nullable();
			$table->string('customer_city', 60)->nullable();
			$table->string('customer_telephone_privat', 60)->nullable();
			$table->string('customer_telephone_business', 60)->nullable();
			$table->string('customer_telephone_mobil', 60)->nullable();
			$table->string('customer_email', 60)->nullable();
			$table->string('customer_costcenter', 20)->nullable();
			$table->string('journey_id', 60)->nullable();
			$table->string('affiliate', 60)->nullable();
			$table->string('agent', 60)->nullable();
			$table->string('art_of_journey', 60)->nullable();
			$table->string('destination', 60)->nullable();
			$table->string('transport', 60)->nullable();
			$table->string('customer_nr_start', 60)->nullable();
			$table->string('description')->nullable();
			$table->string('fee1_code', 7)->nullable();
			$table->string('fee1_amount', 20)->nullable();
			$table->string('fee2_code', 7)->nullable();
			$table->string('fee2_amount', 20)->nullable();
			$table->string('fee3_code', 7)->nullable();
			$table->string('fee3_amount', 20)->nullable();
			$table->string('customer_consulter', 60)->nullable();
			$table->string('original_pnr')->nullable()->default('');
			$table->string('price4_text', 60)->nullable()->default('');
			$table->string('price5_text', 60)->nullable()->default('');
			$table->string('price4_no', 5)->nullable()->default('');
			$table->string('price5_no', 5)->nullable()->default('');
			$table->string('price4_amount', 20)->nullable()->default('');
			$table->string('price5_amount', 20)->nullable()->default('');
			$table->string('addon_1', 10)->nullable()->default('');
			$table->string('addon_2', 10)->nullable()->default('');
			$table->string('addon_3', 10)->nullable()->default('');
			$table->string('addon_4', 10)->nullable()->default('');
			$table->string('free_2', 30)->nullable()->default('');
			$table->string('free_3', 30)->nullable()->default('');
			$table->string('free_4', 30)->nullable()->default('');
			$table->string('free_1', 30)->nullable()->default('');
			$table->boolean('use_ticket_taxes')->default(0);
			$table->string('booking_nr', 20)->nullable()->default('');
			$table->string('fee1_quantity', 5)->nullable()->default('');
			$table->string('fee2_quantity', 5)->nullable()->default('');
			$table->string('fee3_quantity', 5)->nullable()->default('');
			$table->char('fee1_payment', 1)->nullable()->default('');
			$table->char('fee2_payment', 1)->nullable()->default('');
			$table->char('fee3_payment', 1)->nullable()->default('');
			$table->string('price6_text', 60)->nullable()->default('');
			$table->string('price6_no', 5)->nullable()->default('');
			$table->string('price6_amount', 20)->nullable()->default('');
			$table->string('price7_text', 60)->nullable()->default('');
			$table->string('price7_no', 5)->nullable()->default('');
			$table->string('price7_amount', 20)->nullable()->default('');
			$table->string('price8_text', 60)->nullable()->default('');
			$table->string('price8_no', 5)->nullable()->default('');
			$table->string('price8_amount', 20)->nullable()->default('');
			$table->string('price9_text', 60)->nullable()->default('');
			$table->string('price9_no', 5)->nullable()->default('');
			$table->string('price9_amount', 20)->nullable()->default('');
			$table->string('fee4_code', 7)->nullable()->default('');
			$table->string('fee4_amount', 20)->nullable()->default('');
			$table->string('fee4_quantity', 5)->nullable()->default('');
			$table->char('fee4_payment', 1)->nullable()->default('');
			$table->string('fee5_code', 7)->nullable()->default('');
			$table->string('fee5_amount', 20)->nullable()->default('');
			$table->string('fee5_quantity', 5)->nullable()->default('');
			$table->char('fee5_payment', 1)->nullable()->default('');
			$table->boolean('create_new_service')->default(0);
			$table->string('free_5', 30)->nullable()->default('');
			$table->string('free_6', 30)->nullable()->default('');
			$table->string('free_7', 30)->nullable()->default('');
			$table->string('free_8', 30)->nullable()->default('');
			$table->string('free_9', 30)->nullable()->default('');
			$table->string('free_10', 30)->nullable()->default('');
			$table->string('price1_tax', 10)->default('');
			$table->string('price2_tax', 10)->default('');
			$table->string('price3_tax', 10)->default('');
			$table->string('price4_tax', 10)->default('');
			$table->string('price5_tax', 10)->default('');
			$table->string('price6_tax', 10)->default('');
			$table->string('price7_tax', 10)->default('');
			$table->string('price8_tax', 10)->default('');
			$table->string('price9_tax', 10)->default('');
			$table->string('pax_profile_1')->nullable()->default('');
			$table->string('pax_profile_2')->nullable()->default('');
			$table->string('pax_profile_3')->nullable()->default('');
			$table->string('pax_profile_4')->nullable()->default('');
			$table->string('pax_profile_5')->nullable()->default('');
			$table->string('pax_profile_6')->nullable()->default('');
			$table->string('pax_profile_7')->nullable()->default('');
			$table->string('pax_profile_8')->nullable()->default('');
			$table->string('pax_profile_9')->nullable()->default('');
			$table->string('pax_profile_10')->nullable()->default('');
			$table->boolean('auto_print')->nullable();
			$table->string('booking_status_fields', 150)->nullable();
			$table->string('price1_commission', 10)->nullable();
			$table->string('price2_commission', 10)->nullable();
			$table->string('price3_commission', 10)->nullable();
			$table->string('price4_commission', 10)->nullable();
			$table->string('price5_commission', 10)->nullable();
			$table->string('price6_commission', 10)->nullable();
			$table->string('price7_commission', 10)->nullable();
			$table->string('price8_commission', 10)->nullable();
			$table->string('price9_commission', 10)->nullable();
			$table->string('payment', 20)->nullable()->default('');
			$table->string('matchcode')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('remarkdata');
	}

}
