<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperadorSeeder extends Seeder
{
    
    public function run(): void
    {
      
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); 
        DB::table('operadores')->truncate(); 
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 

        // 2. INSERCIÓN DEL OPERADOR BASE (CRUCIAL para la FK)
        DB::table('operadores')->insert([
            'id' => 1, // FORZAMOS EL ID 1 para que LlegadaPlantaSeeder no falle
            'nombre' => 'Jefe',
            'apellido' => 'Produccion',
            'puesto' => 'Supervisor', // Usamos el campo 'puesto'
            'observaciones' => 'Operador de prueba para inicializar el sistema.',
            'estado' => 1, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 3. Puedes agregar otros operadores de prueba aquí si lo deseas
        DB::table('operadores')->insert([
            'nombre' => 'Diego',
            'apellido' => 'Villegas',
            'puesto' => 'Técnico de Plagas',
            'observaciones' => 'Personal encargado de sanidad.',
            'estado' => 1, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}