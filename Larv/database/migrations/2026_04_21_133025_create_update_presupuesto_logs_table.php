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
        Schema::create('update_presupuesto_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_presupuesto');
            $table->unsignedBigInteger('id_pieza')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->string('accion', 50); // 'agregar_pieza', 'modificar_pieza'
            $table->string('campo_modificado', 100)->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_presupuesto_logs');
    }
};
