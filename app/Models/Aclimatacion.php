<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aclimatacion extends Model
{
    use HasFactory;

    protected $table = 'aclimatacion';
    protected $primaryKey = 'ID_Aclimatacion';
    public $incrementing = true;

    protected $fillable = [
        'Fecha_Ingreso',
        'Estado_Inicial',
        'Duracion_Aclimatacion',
        'fecha_cierre',         
        'cantidad_final',       
        'merma_etapa',
        'ID_Llegada',
        'Observaciones',
        'ID_Variedad',
        'Operador_Responsable'
    ];


   public function loteLlegada()
    {
        
        return $this->belongsTo(LlegadaPlanta::class, 'ID_Llegada', 'ID_Llegada');
    }

    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'ID_Variedad', 'ID_Variedad');
    }


    public function operadorResponsable()
    {
        return $this->belongsTo(Operador::class, 'Operador_Responsable', 'ID_Operador');
    }
}
