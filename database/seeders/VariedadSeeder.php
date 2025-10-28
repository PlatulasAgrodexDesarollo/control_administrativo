<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class VariedadSeeder extends Seeder
{
  
    public function run(): void
    {
        DB::table('variedad')->insert([
            ['nombre_variedad' => 'Tomate Cherry', 'descripcion' => 'Variedad de alto rendimiento, ciclo corto.'],
            ['nombre_variedad' => 'Lechuga Romana', 'descripcion' => 'Apta para hidroponía, sensible a plagas.'],
            ['nombre_variedad' => 'Pimiento Rojo Blocky', 'descripcion' => 'Requiere soporte y fertilización constante.'],
        ]);
    }
    
}
