<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateYpsilonXmlApiLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ypsilon_xml_api_log', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('ypsilon_pnr_id')->index('ypsilon_pnr_id_index2');
			$table->string('gds_pnr_id', 30);
			$table->string('agent_etacs', 60);
			$table->boolean('error')->default(0)->comment('error hapened while writing from xml_api to office4crs');
			$table->boolean('saved_in_db')->default(0)->comment('data from xml_api saved in office4crs');
			$table->dateTime('created')->default('now()');
			$table->dateTime('saved')->nullable()->comment('time when data from xml_api where saved in office4crs');
			$table->text('error_text')->nullable();
			$table->dateTime('booking_date');
			$table->integer('group_record_id')->nullable();
			$table->integer('transaction_id')->nullable();
			$table->string('conso_yps', 60);
			$table->string('agent_yps', 60);
			$table->index(['ypsilon_pnr_id','agent_etacs'], 'ypsilon_pnr_id_index');
			$table->unique(['ypsilon_pnr_id','agent_etacs'], 'unique_agent_and_ypsilon_pnr_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ypsilon_xml_api_log');
	}

}
