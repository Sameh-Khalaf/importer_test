<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIrisFilestatusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('iris_filestatus', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('pnr_id');
			$table->boolean('mas_done')->default(0);
			$table->dateTime('mas_timestamp')->nullable();
			$table->boolean('res_done')->default(0);
			$table->dateTime('res_timestamp')->nullable();
			$table->boolean('pri_done')->default(0);
			$table->dateTime('pri_timestamp')->nullable();
			$table->string('agent', 100);
			$table->unique(['pnr_id','agent'], 'iris_filestatus_pnr_id_key');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('iris_filestatus');
	}

}
