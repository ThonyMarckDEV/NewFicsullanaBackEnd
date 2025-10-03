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
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Datos');
            $table->string('ctaAhorros')->unique();
            $table->string('cci')->nullable()->unique();
            $table->string('entidadFinanciera');
            $table->timestamps();

            $table->foreign('id_Datos')->references('id')->on('datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_cuentas_bancarias');
    }
};
