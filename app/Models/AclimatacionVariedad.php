<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AclimatacionVariedad extends Model
{
    use HasFactory;

    protected $table = 'aclimatacion_variedad';

    // La tabla pivote YA tiene su propio ID autoincrement
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'aclimatacion_id',
        'ID_llegada',
        'variedad_id',
        'cantidad_plantas',
    ];

    /**
     * Relación con la etapa de Aclimatación
     */
    public function aclimatacion()
    {
        return $this->belongsTo(\App\Models\Aclimatacion::class, 'aclimatacion_id', 'ID_Aclimatacion');
    }

    /**
     * Relación con el Lote de Llegada
     */
    public function llegada()
    {
        return $this->belongsTo(\App\Models\LlegadaPlanta::class, 'ID_llegada', 'ID_Llegada');
    }

    /**
     * Relación con la Variedad
     */
    public function variedad()
    {
        return $this->belongsTo(\App\Models\Variedad::class, 'variedad_id', 'ID_Variedad');
    }
}
