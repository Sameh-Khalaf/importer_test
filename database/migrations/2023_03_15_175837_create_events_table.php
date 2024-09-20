<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('events', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id');
			$table->string('import_event_type', 60)->default('booking');
			$table->string('title')->default('');
			$table->string('event_type', 20)->default('');
			$table->string('date', 20)->default('1900-01-01');
			$table->string('time', 20)->default('00:00');
			$table->string('login', 60)->nullable();
			$table->string('user_group', 60)->nullable();
			$table->boolean('open')->default(0);
			$table->boolean('internal')->default(0);
			$table->text('info')->nullable();
			$table->string('attachment')->nullable();
			$table->string('link')->nullable();
			$table->string('ibe', 60)->nullable();
			$table->string('ibe2', 60)->nullable();
			$table->string('ibe_number', 60)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('events');
	}

}
