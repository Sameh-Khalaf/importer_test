<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('remarks', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->index('index_remarks_pnr_id');
			$table->smallInteger('sort_order')->nullable();
			$table->text('remark_text')->nullable();
			$table->dateTime('created')->default('now()');
			$table->boolean('note')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('remarks');
	}

}
