<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIdentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ident', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('pnr_id', 50)->nullable()->index('indx_ident_pnr_id');
			$table->string('agent', 20)->nullable()->index('indx_ident_agent');
			$table->string('office_id', 25)->nullable();
			$table->boolean('processed')->nullable()->default(0);
			$table->integer('crs_id');
			$table->integer('user_id')->nullable()->default(0);
			$table->dateTime('p_timestamp')->nullable()->default('now()');
			$table->integer('import_count')->nullable()->default(0);
			$table->boolean('show_in_list')->default(1);
			$table->string('booking_date', 10)->nullable();
			$table->string('conso_id', 10)->nullable();
			$table->string('majority_carrier', 10)->nullable();
			$table->string('point_of_return', 3)->nullable();
			$table->string('error_code', 60)->nullable();
			$table->string('destination_area', 10)->nullable();
			$table->string('journey_from_date', 10)->nullable();
			$table->string('journey_till_date', 10)->nullable();
			$table->string('delivery_type', 30)->nullable()->default('');
			$table->boolean('additional_import')->default(0);
			$table->integer('magicres_agency')->nullable();
			$table->integer('magicres_terminal')->nullable();
			$table->string('magicres_doctype', 10)->nullable()->default('--');
			$table->integer('magicres_superpnr')->nullable();
			$table->string('magicres_catalog', 60)->nullable();
			$table->integer('ignored_by')->nullable();
			$table->boolean('storno')->nullable()->default(0);
			$table->smallInteger('owner_id')->nullable();
			$table->smallInteger('affiliate')->nullable();
			$table->string('group_id', 60)->default('');
			$table->text('rule')->nullable();
			$table->string('file_type', 1)->default('B');
			$table->text('notice')->nullable();
			$table->smallInteger('priority')->default(1);
			$table->smallInteger('classifications')->default(0);
			$table->boolean('has_refunds')->nullable()->default(0);
			$table->boolean('update_flag')->default(0);
			$table->integer('ypsilon_pnr_id')->nullable();
			$table->boolean('autobooker_checked')->default(0);
			$table->string('filename', 120)->nullable();
			$table->integer('parent_id')->nullable();
			$table->boolean('sir_flag')->default(0);
			$table->string('spnr_id', 50)->nullable();
			$table->bigInteger('sr_dat_mitarbeiter_lfdnr')->default(0);
			$table->integer('version')->nullable()->default(1);
			$table->string('pnr_original', 10)->nullable();
			$table->string('booking_sine', 20)->nullable();
			$table->string('ticketing_sine', 20)->nullable();
			$table->string('match_code', 10)->nullable();
			$table->string('auto_import_order')->nullable()->default('50');
			$table->string('booking_iata', 25)->nullable();
			$table->boolean('has_online_invoice')->nullable()->default(0);
			$table->boolean('has_online_voucher')->nullable()->default(0);
			$table->boolean('isdomestic')->default(0);
			$table->boolean('online_invoice_printed')->nullable()->default(0);
			$table->string('ticketing_iata', 25)->nullable();
			$table->string('tktoffice_id', 25)->nullable();
			$table->integer('total_pnr_passengers')->nullable();
			$table->string('valid_carrier', 3)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ident');
	}

}
