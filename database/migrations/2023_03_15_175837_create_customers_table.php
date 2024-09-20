<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('pnr_id')->nullable()->index('index_customers_pnr_id');
			$table->string('firstname', 60)->nullable();
			$table->string('surname', 60)->nullable();
			$table->string('dob', 14)->nullable();
			$table->string('gender', 1)->nullable()->default('M');
			$table->string('street', 80)->nullable();
			$table->string('zip', 20)->nullable();
			$table->string('city', 40)->nullable();
			$table->string('address1', 60)->nullable();
			$table->string('address2', 60)->nullable();
			$table->string('address3', 60)->nullable();
			$table->integer('billingid')->nullable();
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
			$table->string('titletype', 15)->nullable();
			$table->string('customer_nr')->nullable();
			$table->string('type_of_customer', 10)->nullable();
			$table->string('categorie', 100)->nullable();
			$table->string('belong_to', 60)->nullable();
			$table->string('matchcode')->nullable();
			$table->string('pobox_city', 40)->nullable();
			$table->string('notice')->nullable();
			$table->string('optional_mobile_phone', 60)->nullable();
			$table->string('optional_phone', 60)->nullable();
			$table->string('type_of_customer2', 10)->nullable();
			$table->integer('customer_blocked')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customers');
	}

}
