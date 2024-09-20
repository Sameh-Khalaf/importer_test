<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIdentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ident', function(Blueprint $table)
		{
			$table->foreign('crs_id', 'ident_crs_id')->references('id')->on('crs')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ident', function(Blueprint $table)
		{
			$table->dropForeign('ident_crs_id');
		});
	}

}
