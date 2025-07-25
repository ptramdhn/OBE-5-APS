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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('prodi_id')->constrained('program_studies')->onUpdate('cascade')->onDelete('restrict');
            $table->string('id_mk')->unique();
            $table->string('kode_mk')->unique();
            $table->string('name');
            $table->integer('semester');
            $table->integer('sks');
            $table->string('jenis_mk');
            $table->string('kelompok_mk');
            $table->string('lingkup_kelas');
            $table->string('mode_kuliah');
            $table->string('metode_pembelajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
