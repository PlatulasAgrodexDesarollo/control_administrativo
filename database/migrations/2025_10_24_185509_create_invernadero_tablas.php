<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tablas Maestras
        Schema::create('variedad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_variedad', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('clasificacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_clasificacion', 100)->unique();
            $table->string('tipo_uso', 50)->comment('Ej: Perdida, Calidad, Tamaño');
            $table->timestamps();
        });

        Schema::create('sustrato', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_sustrato', 100)->unique();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('inducimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_inducimiento', 100)->unique();
            $table->timestamps();
        });

        // 2. Tablas que dependen solo de Maestros (Llegada)
        Schema::create('llegada_planta', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_llegada');
            $table->integer('cantidad');
            $table->string('proveedor', 100)->nullable();
            $table->text('observaciones')->nullable();
            
            $table->foreignId('variedad_id')->constrained('variedad')->restrictOnDelete();
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            
            $table->timestamps();
        });

        // 3. Tablas de Procesos/Controles (que dependen de Operadores)
        Schema::create('control_plagas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_control');
            $table->string('tipo_plaga', 100);
            $table->string('producto_usado', 100);
            $table->text('acciones');
            $table->text('observaciones')->nullable();
            
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('chequeo_agronomico', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_chequeo');
            $table->string('estado_apical', 50);
            $table->string('estado_agresion', 50);
            $table->text('observaciones')->nullable();
            
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            $table->timestamps();
        });

        // 4. Tablas de Plantación y Producción (Dependencias Múltiples)
        Schema::create('plantacion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_plantacion');
            $table->integer('cantidad');
            $table->string('ubicacion_invernadero', 50);
            $table->text('observaciones')->nullable();
            
            $table->foreignId('variedad_id')->constrained('variedad')->restrictOnDelete();
            $table->foreignId('llegada_planta_id')->constrained('llegada_planta')->restrictOnDelete(); 
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            
            $table->timestamps();
        });
        
        // 5. Inventario, Pérdidas, y Procesos Intermedios
        Schema::create('inventario_invernadero', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_actual');
            $table->string('tipo_entrada', 50);
            $table->date('fecha_movimiento');
            $table->text('observaciones')->nullable();
            
            $table->foreignId('variedad_id')->constrained('variedad')->restrictOnDelete();
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
        });

        Schema::create('perdidas_invernadero', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_perdida');
            $table->string('motivo_perdida', 100);
            $table->text('observaciones')->nullable();
            
            $table->foreignId('variedad_id')->constrained('variedad')->restrictOnDelete();
            $table->foreignId('clasificacion_id')->constrained('clasificacion')->restrictOnDelete();
            
            $table->timestamps();
        });
        
        Schema::create('riego_fertilizacion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_fertilizacion');
            $table->string('tipo_fertilizacion', 50);
            $table->decimal('cantidad_aplicada', 10, 2);
            $table->text('observaciones')->nullable();
            
            $table->foreignId('sustrato_id')->constrained('sustrato')->restrictOnDelete();
            $table->foreignId('operador_id')->constrained('operadores')->restrictOnDelete();
            
            $table->timestamps();
        });
        
        // (Nota: Se omiten tablas muy específicas sin uso inmediato como LlenadoRP, LavadoRP, etc.)
    }

    /**
     * Reverse the migrations (Elimina las tablas en orden inverso).
     */
    public function down(): void
    {
        Schema::dropIfExists('riego_fertilizacion');
        Schema::dropIfExists('perdidas_invernadero');
        Schema::dropIfExists('inventario_invernadero');
        Schema::dropIfExists('plantacion');
        Schema::dropIfExists('chequeo_agronomico');
        Schema::dropIfExists('control_plagas');
        Schema::dropIfExists('llegada_planta');
        Schema::dropIfExists('inducimiento');
        Schema::dropIfExists('sustrato');
        Schema::dropIfExists('clasificacion');
        Schema::dropIfExists('variedad');
    }
};