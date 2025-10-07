<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCargosMoraTable extends Migration
{
    public function up()
    {
        Schema::create('cargos_mora', function (Blueprint $table) {
            $table->id(); // Opcional: Agrega un ID primario
            $table->string('dias', 50);
            $table->decimal('monto_300_1000', 10, 2);
            $table->decimal('monto_1001_2000', 10, 2);
            $table->decimal('monto_2001_3000', 10, 2);
            $table->decimal('monto_3001_4000', 10, 2);
            $table->decimal('monto_4001_5000', 10, 2);
            $table->decimal('monto_5001_6000', 10, 2);
            $table->decimal('monto_mas_6000', 10, 2);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cargos_mora');
    }
}