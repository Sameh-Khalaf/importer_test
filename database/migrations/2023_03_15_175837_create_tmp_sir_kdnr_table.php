<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTmpSirKdnrTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tmp_sir_kdnr', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('orig_kdnr', 30)->unique('kdnr_unique_key');
			$table->string('aurora_kdnr', 30);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tmp_sir_kdnr');
	}

}
