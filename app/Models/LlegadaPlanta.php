<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LlegadaPlanta extends Model
{
    use HasFactory;

    protected $table = 'llegada_planta';
    protected $primaryKey = 'ID_Llegada';
    public $incrementing = true;

     
    protected $fillable = [
        'Fecha_Llegada',
        'Cantidad_Plantas',
        'Pre_Aclimatacion',
        'Observaciones',
        'ID_Variedad',
        'Operador_Llegada'
    ];
    public function operadorLlegada()
    {
        return $this->belongsTo(Operador::class, 'Operador_Llegada', 'ID_Operador');
    }

    // Relación 2: La Variedad
    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'ID_Variedad', 'ID_Variedad');
    }
}
