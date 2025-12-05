<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ChequeoHyT;

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
        'Observaciones',
        'Operador_Responsable'
    ];

   
    public function operadorResponsable(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operador::class, 'Operador_Responsable', 'ID_Operador');
    }

   
    public function lotesAclimatados(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\LlegadaPlanta::class,
            'aclimatacion_variedad', 
            'aclimatacion_id', 
            'ID_llegada' 
        )
        ->withPivot('variedad_id', 'cantidad_plantas')
        ->with('variedad');
    }

   
    public function variedades(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Variedad::class,
            'aclimatacion_variedad',
            'aclimatacion_id',
            'variedad_id'
        )
        ->withPivot('cantidad_plantas', 'ID_llegada');
    }
    public function chequeos(): HasMany
    {
       
    return $this->hasMany(ChequeoHyT::class, 'ID_Aclimatacion', 'ID_Aclimatacion');
}
}