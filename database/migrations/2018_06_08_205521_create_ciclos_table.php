<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCiclosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ciclos', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->string('ciclo', 50);
			$table->boolean('departamento')->index('ciclos_departamento_foreign');
			$table->string('responsable', 80);
			$table->boolean('tipo');
			$table->string('normativa', 10)->default('LOE');
			$table->string('titol', 100)->nullable();
			$table->string('rd', 100)->nullable();
			$table->string('rd2', 100)->nullable();
			$table->string('vliteral', 100)->nullable();
			$table->string('cliteral', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ciclos');
	}

}
