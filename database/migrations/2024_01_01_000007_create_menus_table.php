<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // menu induk (self relation, no FK)
            $table->string('name');            // "Manajemen Karyawan"
            $table->string('slug')->unique();   // employee-management
            $table->string('icon')->nullable(); // class font-awesome, mis. "fa-solid fa-users"
            $table->string('route')->nullable(); // nama route, mis. "employees.index"
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
