<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LlegadaPlantaSeeder extends Seeder
{
    public function run(): void
    {
        // Se asume que la Variedad 'Tomate Cherry' tiene ID 1 y el Operador de prueba tiene ID 1
        
        DB::table('llegada_planta')->insert([
            [
                'fecha_llegada' => now()->subDays(7),
                'cantidad' => 5000,
                'proveedor' => 'Vivero del Sol',
                'observaciones' => 'Lote #A102. Plantín de 4 semanas.',
                'variedad_id' => 1, 
                'operador_id' => 1, 
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha_llegada' => now()->subDays(3),
                'cantidad' => 2000,
                'proveedor' => 'GreenHouse Seeds',
                'observaciones' => 'Lote #B300. Pimiento listo para trasplante.',
                'variedad_id' => 3, 
                'operador_id' => 1, 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}