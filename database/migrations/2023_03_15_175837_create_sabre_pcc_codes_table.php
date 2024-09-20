<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSabrePccCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sabre_pcc_codes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('pcc_code', 4)->index('sabre_pcc_codes_id');
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
		Schema::drop('sabre_pcc_codes');
	}

}
