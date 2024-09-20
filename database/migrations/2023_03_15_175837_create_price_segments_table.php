<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePriceSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('price_segments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->integer('service_segment_id')->nullable();
			$table->string('price_text', 60)->default('');
			$table->decimal('price_amount', 10)->default(0.00);
			$table->smallInteger('price_number')->default(1);
			$table->decimal('tax_amount', 10)->default(0.00);
			$table->decimal('commision_amount', 10)->default(0.00);
			$table->decimal('commision_tax_amount', 10)->default(0.00);
			$table->string('tax_key', 20)->default('');
			$table->string('tax_type', 1)->default('I');
			$table->smallInteger('sort_order')->default(0);
			$table->string('ticket_number', 100)->default('');
			$table->string('verk_code', 50)->default('');
			$table->decimal('tax_rate', 10)->default(0.00);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('price_segments');
	}

}
