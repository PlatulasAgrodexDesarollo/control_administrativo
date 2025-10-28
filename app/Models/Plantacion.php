<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantacion extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'plantacion';

    // Campos que se pueden llenar masivamente (FKs y datos)
    protected $fillable = [
        'fecha_plantacion', 
        'cantidad', 
        'ubicacion_invernadero', 
        'variedad_id', 
        'llegada_planta_id', 
        'operador_id', 
        'observaciones'
    ];

    // Relación 1: La Variedad de la planta
    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'variedad_id');
    }

    // Relación 2: El Lote de Inventario de donde proviene (Trazabilidad)
    public function loteLlegada()
    {
        // 'llegada_planta_id' es la FK en esta tabla que apunta a LlegadaPlanta
        return $this->belongsTo(LlegadaPlanta::class, 'llegada_planta_id');
    }

    // Relación 3: El Operador que realizó la tarea
    public function operador()
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }
}