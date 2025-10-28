<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
            
            $table->dropUnique('operadores_identificacion_unique'); 
        });

        Schema::table('operadores', function (Blueprint $table) {
         
            $table->renameColumn('identificacion', 'puesto'); 
        });
        
       
    }

    public function down(): void
    {
        Schema::table('operadores', function (Blueprint $table) {
           
            $table->renameColumn('puesto', 'identificacion');
           
            $table->unique('identificacion'); 
        });
    }
};