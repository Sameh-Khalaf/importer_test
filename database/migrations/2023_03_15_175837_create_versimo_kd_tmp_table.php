<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVersimoKdTmpTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('versimo_kd_tmp', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('kunde', 80)->nullable();
			$table->string('kunde_id', 80)->nullable()->index('kunde_id_idx');
			$table->string('kundenart', 30)->nullable();
			$table->string('name', 80)->nullable();
			$table->string('vorname', 80)->nullable();
			$table->string('zusatz', 80)->nullable();
			$table->string('titel', 30)->nullable();
			$table->string('strasse', 80)->nullable();
			$table->string('szusatz', 80)->nullable();
			$table->string('land', 5)->nullable();
			$table->string('plz_1', 10)->nullable();
			$table->string('ort_1', 80)->nullable();
			$table->string('plz_2', 10)->nullable();
			$table->string('ort_2', 80)->nullable();
			$table->string('telefon_1', 80)->nullable();
			$table->string('telefon_2', 80)->nullable();
			$table->string('fax', 80)->nullable();
			$table->string('geburtsdatum', 10)->nullable();
			$table->string('email', 80)->nullable();
			$table->string('titel_2', 30)->nullable();
			$table->string('notiz')->nullable();
			$table->string('oft_sperrkz', 10)->nullable();
			$table->string('oft_kd_art', 10)->nullable();
			$table->string('oft_tel_update', 80)->nullable();
			$table->string('oft_handy_update', 80)->nullable();
			$table->string('oft_firma', 80)->nullable();
			$table->boolean('imported')->default(0)->index('imported_idx');
			$table->string('error')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('versimo_kd_tmp');
	}

}
