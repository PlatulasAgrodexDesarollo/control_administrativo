<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function nombreLoteSemana(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {

                if (empty($attributes['Fecha_Llegada'])) {
                    return 'Fecha N/D';
                }

                $fecha = Carbon::parse($attributes['Fecha_Llegada']);


                $semana_del_mes = $fecha->weekOfMonth;

                return "Lote " . $semana_del_mes .
                    " (" . $fecha->format('M Y') . ")";
            },
        );
    }

    public function aclimataciones()
    {
        return $this->belongsToMany(
            \App\Models\Aclimatacion::class,
            'aclimatacion_variedad',
            'ID_llegada',
            'aclimatacion_id'
        )
            ->withPivot('variedad_id', 'cantidad_plantas')
            ->with('variedad');
    }
}
