<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGalileoPccCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('galileo_pcc_codes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('pcc_code', 10);
			$table->string('agent', 60);
			$table->boolean('active')->default(1);
			$table->string('info', 100)->nullable()->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('galileo_pcc_codes');
	}

}
