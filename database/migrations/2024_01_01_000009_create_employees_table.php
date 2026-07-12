<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique(); // NIP / kode pegawai internal

            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('position_id')->nullable();

            $table->string('name');
            $table->string('nik', 20)->nullable();     // NIK KTP
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->date('join_date')->nullable();
            $table->date('resign_date')->nullable();

            $table->enum('employment_status', ['active', 'probation', 'resigned', 'terminated'])
                ->default('active');
            $table->enum('employment_type', ['permanent', 'contract', 'intern', 'freelance'])
                ->default('permanent');

            $table->string('photo')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
