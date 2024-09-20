<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCrsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('crs_name', 40);
			$table->string('dirname')->default('');
			$table->integer('crsbroker_id')->nullable();
			$table->integer('sort_order')->nullable();
			$table->boolean('active')->nullable()->default(0);
			$table->boolean('autoimport')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crs');
	}

}
