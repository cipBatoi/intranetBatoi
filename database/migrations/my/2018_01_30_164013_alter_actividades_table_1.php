<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActividadesTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actividades',function (Blueprint $table){
            //$table->dropColumn('idEmpresa');
            $table->boolean('fueraCentro')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actividades',function (Blueprint $table){
            $table->dropColumn('fueraCentro');
        });
    }
}