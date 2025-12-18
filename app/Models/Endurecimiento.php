<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Endurecimiento extends Model
{
    protected $table = 'endurecimientos';
    protected $primaryKey = 'ID_Endurecimiento';
    protected $fillable = ['Fecha_Ingreso', 'Fecha_Cierre', 'cantidad_inicial', 'cantidad_final', 'merma_total_etapa', 'Estado_General', 'Observaciones', 'Operador_Responsable'];


public function lotes()
{
    return $this->belongsToMany(LlegadaPlanta::class, 'endurecimiento_variedad', 'endurecimiento_id', 'ID_llegada')
                ->withPivot([
                    'variedad_id', 
                    'merma_inicial_plantacion',
                    'merma_aclimatacion_pasada',
                    'cantidad_inicial_lote',
                    'stock_entrada_etapa',
                    'cantidad_plantas',
                    'merma_acumulada_lote',
                    'Estado_Lote'
                ]);
}
    public function responsable()
    {
        return $this->belongsTo(Operador::class, 'Operador_Responsable', 'ID_Operador');
    }

    public function getDiasEnEtapaAttribute()
    {
        $inicio = Carbon::parse($this->Fecha_Ingreso);
        $fin = $this->Fecha_Cierre ? Carbon::parse($this->Fecha_Cierre) : Carbon::now();
        
        return floor($inicio->diffInDays($fin));
    }
}