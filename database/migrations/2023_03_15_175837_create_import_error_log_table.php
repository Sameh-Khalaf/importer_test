<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImportErrorLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('import_error_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('crs_id')->nullable();
			$table->string('status_code', 10)->nullable()->comment('200 = Database, 300 Zend, 400 = Unknown');
			$table->text('exception')->nullable();
			$table->string('pnr_id', 50)->nullable()->comment('pnr_id from table ident');
			$table->integer('ident_id')->nullable()->comment('id from table ident');
			$table->integer('corp_id')->nullable()->comment('column id from corp_auth');
			$table->string('filename')->nullable();
			$table->dateTime('time')->nullable()->default('now()');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('import_error_log');
	}

}
