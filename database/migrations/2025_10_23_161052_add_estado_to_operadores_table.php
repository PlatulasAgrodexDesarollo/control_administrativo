<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('operadores', function (Blueprint $table) {
            // Añade el campo 'estado': 1 es activo (por defecto), 0 es inactivo.
            $table->boolean('estado')->default(1)->after('observaciones'); 
        });
    }

    public function down()
    {
        Schema::table('operadores', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
