<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('custom_remarks', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('field_name', 50);
			$table->text('remark');
			$table->text('remark_text');
			$table->string('agent');
			$table->integer('pnr_id');
			$table->timestamps();
			$table->bigInteger('participants_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('custom_remarks');
	}

}
