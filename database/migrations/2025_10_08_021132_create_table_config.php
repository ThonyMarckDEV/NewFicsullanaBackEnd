<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->unique()->comment('El tipo de configuración, ej: "storage".');
            $table->integer('estado')->comment('1 para local, 2 para MinIO/S3.');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Opcional: Insertar el registro inicial para el tipo de storage
        Config::create([
            'tipo' => 'storage',
            'estado' => 1, // Por defecto, empieza con almacenamiento local
            'descripcion' => 'Define el disco de almacenamiento a usar. 1: local (public), 2: minio (s3 compatible).'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }
};
