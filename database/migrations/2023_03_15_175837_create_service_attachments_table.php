<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('service_attachments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->integer('service_segment_id')->nullable();
			$table->string('title', 80)->default('');
			$table->text('description')->default('');
			$table->string('link')->default('');
			$table->string('file_path')->default('');
			$table->string('file_type', 80)->default('');
			$table->boolean('is_print_attachment')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('service_attachments');
	}

}
