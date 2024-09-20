<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMagicresAgenciesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('magicres_agencies', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('agency_id')->index('index_agency_id');
			$table->string('agent', 60)->index('magicres_agencies_agent');
			$table->boolean('active')->default(0);
			$table->integer('customer_db_affiliate_id')->nullable();
			$table->unique(['agency_id','agent','active','customer_db_affiliate_id'], 'unique_magicres_id_per_agent_affiliate');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('magicres_agencies');
	}

}
