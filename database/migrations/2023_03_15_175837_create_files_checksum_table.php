<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFilesChecksumTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('files_checksum', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->text('filename');
			$table->string('agent', 50);
			$table->text('checksum')->index('indx_checksum');
			$table->text('headertxt');
			$table->string('status');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('files_checksum');
	}

}
