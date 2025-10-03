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
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Datos');
            $table->string('direccionFiscal');
            $table->string('direccionCorrespondencia');
            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');
            $table->string('tipoVivienda')->comment('Ejemplo: Propia, Alquilada, Familiar, etc.');
            $table->string('tiempoResidencia')->comment('Ejemplo: 1 año, 2 años, etc.');
            $table->string('referenciaDomicilio');
            $table->timestamps();
        
            $table->foreign('id_Datos')->references('id')->on('datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
