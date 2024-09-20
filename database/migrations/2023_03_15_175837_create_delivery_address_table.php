<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeliveryAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('delivery_address', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable();
			$table->string('firstname', 60)->nullable();
			$table->string('surname', 60)->nullable();
			$table->string('dob', 14)->nullable();
			$table->string('gender', 1)->nullable()->default('M');
			$table->string('street', 80)->nullable();
			$table->string('zip', 10)->nullable();
			$table->string('city', 40)->nullable();
			$table->string('address1', 60)->nullable();
			$table->string('address2', 60)->nullable();
			$table->string('address3', 60)->nullable();
			$table->string('busiphone', 50)->nullable();
			$table->string('cellphone', 50)->nullable();
			$table->string('company', 60)->nullable();
			$table->string('country', 60)->nullable();
			$table->string('county', 60)->nullable();
			$table->string('email', 60)->nullable();
			$table->string('fax', 50)->nullable();
			$table->string('houseno', 50)->nullable();
			$table->string('pobox', 30)->nullable();
			$table->string('privphone', 50)->nullable();
			$table->string('title', 50)->nullable();
			$table->string('titleid', 20)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('delivery_address');
	}

}
