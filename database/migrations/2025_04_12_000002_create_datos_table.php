<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidoPaterno');
            $table->string('apellidoMaterno');
            $table->string('apellidoConyuge')->nullable();
            $table->string('estadoCivil');
            $table->string('sexo')->comment('M: Masculino, F: Femenino');
            $table->string('dni', 9)->unique();
            $table->date('fechaNacimiento');
            $table->date('fechaCaducidadDni');
            $table->string('nacionalidad');
            $table->boolean('residePeru');
            $table->string('nivelEducativo');
            $table->string('profesion');
            $table->boolean('enfermedadesPreexistentes');
            $table->string('ruc', 11)->nullable()->unique();
            $table->boolean('expuestaPoliticamente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos');
    }
};