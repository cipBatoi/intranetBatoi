<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramacionesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('programaciones', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('idModuloCiclo')->unsigned()->nullable()->index('programaciones_idmodulociclo_foreign');
			$table->string('idProfesor', 10)->index('programaciones_idprofesor_foreign');
			$table->string('fichero')->nullable();
			$table->boolean('anexos')->default(0);
			$table->boolean('estado')->default(0);
			$table->integer('checkList')->default(0);
			$table->string('ciclo', 80)->nullable();
			$table->boolean('criterios')->default(0);
			$table->boolean('metodologia')->default(0);
			$table->text('propuestas', 65535)->nullable();
			$table->string('curso', 10);
                        $table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('programaciones');
	}

}
