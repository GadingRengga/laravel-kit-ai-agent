<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index();
            $table->unsignedBigInteger('permission_id')->index();
            $table->timestamps();

            // sengaja tidak pakai ->constrained()/foreign() supaya migration
            // bisa diimport satu-satu tanpa error urutan tabel
            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
    }
};
