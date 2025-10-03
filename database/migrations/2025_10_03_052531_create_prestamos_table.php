<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prestamos table
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Cliente');
            $table->decimal('monto', 10, 2);
            $table->decimal('interes', 5, 2);
            $table->decimal('total', 10, 2);
            $table->integer('cuotas');
            $table->decimal('valor_cuota', 10, 2);
            $table->string('frecuencia')->comment('SEMANAL , CATORCENAL , MENSUAL');
            $table->string('modalidad')->comment('NUEVO , RCS , RSS');
            $table->date('fecha_generacion');
            $table->date('fecha_inicio');
            $table->unsignedBigInteger('id_Asesor');
            $table->unsignedBigInteger('id_Producto');
            $table->string('abonado_por')->comment('CUENTA CORRIENTE , CAJA CHICA');
            
            $table->tinyInteger('estado')->comment('1:vigente , 2:cancelado , 3:liquidado');
            $table->timestamps();

            $table->foreign('id_Producto')->references('id')->on('productos');
            $table->foreign('id_Cliente')->references('id')->on('usuarios');
            $table->foreign('id_Asesor')->references('id')->on('usuarios');
        });
        

        // Cuotas table
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Prestamo');
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->decimal('capital', 10, 2);
            $table->decimal('interes', 10, 2);
            $table->date('fecha_vencimiento');
            $table->tinyInteger('estado')->comment(' 1: pendiente , 2: pagado , 3: vence_hoy , 4: vencido , 5: prepagado');
            $table->integer('dias_mora')->default(0);
            $table->decimal('cargo_mora', 10, 2)->default(0.00);
            $table->boolean('mora_aplicada')->default(false);
            $table->decimal('mora_reducida', 5, 2)->default(0.00); // Percentage (0-100%)
            $table->boolean('reduccion_mora_aplicada')->default(false);
            $table->dateTime('fecha_mora_aplicada')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('id_Prestamo')->references('id')->on('prestamos')->onDelete('cascade');
        });

        // Pagos table
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_operacion')->nullable();
            $table->unsignedBigInteger('id_Cuota');
            $table->decimal('monto_pagado', 10, 2);
            $table->decimal('excedente', 10, 2)->default(0);
            $table->date('fecha_pago');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('id_Usuario');
            $table->string('modalidad')->comment('PRESENCIAL , VIRTUAL');
            $table->timestamps();
            
            $table->foreign('id_Cuota')->references('id')->on('cuotas')->onDelete('cascade');
            $table->foreign('id_Usuario')->references('id')->on('usuarios');
        });

    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('cuotas');
        Schema::dropIfExists('prestamos');
    }
};