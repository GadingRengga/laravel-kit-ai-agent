<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot Position <-> Menu, polanya sama persis dengan role_has_menus yang
 * sudah ada (lihat App\Models\Superuser\Role::menus()) — bedanya di-attach
 * ke posisi jabatan, bukan role user. Empat kolom hak akses (can_view,
 * can_create, can_edit, can_delete) sengaja berupa boolean lepas per baris
 * pivot, BUKAN referensi ke "daftar role" tetap — supaya kombinasinya bebas
 * dipakai ulang di posisi manapun tanpa terikat satu posisi tertentu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_has_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['position_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_has_menus');
    }
};
