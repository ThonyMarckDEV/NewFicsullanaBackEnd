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
        Schema::create('cliente_empleos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Datos');
            $table->string('centroLaboral');
            $table->integer('ingresoMensual');
            $table->date('inicioLaboral');
            $table->string('situacionLaboral');
            $table->timestamps();

            $table->foreign('id_Datos')->references('id')->on('datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_empleos');
    }
};
