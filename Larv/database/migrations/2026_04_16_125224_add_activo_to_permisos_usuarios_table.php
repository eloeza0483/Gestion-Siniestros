<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_usuarios', function (Blueprint $table) {
            $table->tinyInteger('activo')->default(1)->after('permiso_id');
        });
    }

    public function down(): void
    {
        Schema::table('permisos_usuarios', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
