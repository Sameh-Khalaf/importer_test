<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSurchargesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('surcharges', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('surcharge_currency', 3)->nullable();
			$table->string('surcharge_type', 100)->nullable();
			$table->decimal('surcharge', 10)->default(0.00);
			$table->boolean('propax')->nullable()->default(0);
			$table->string('segment_table', 80)->nullable()->default('');
			$table->string('segment_ids', 120)->nullable()->default('');
			$table->char('payment', 1)->default('A');
			$table->string('remark')->nullable();
			$table->string('url')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('surcharges');
	}

}
