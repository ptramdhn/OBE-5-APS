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
        Schema::create('cpl_bk', function (Blueprint $table) {
            $table->foreignUuid('cpl_id')->constrained('program_learning_outcomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('bk_id')->constrained('study_materials')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['cpl_id', 'bk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpl_bk');
    }
};
