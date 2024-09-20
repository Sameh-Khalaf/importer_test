<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProcessedFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('processed_files', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('filename');
			$table->string('supplier', 40);
			$table->boolean('processed')->nullable()->default(0);
			$table->dateTime('processed_timestamp')->nullable()->default('now()');
			$table->string('agent', 20);
			$table->text('content')->nullable();
			$table->string('type', 5)->nullable();
			$table->integer('pnr_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('processed_files');
	}

}
