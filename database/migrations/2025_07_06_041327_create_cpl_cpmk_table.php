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
        Schema::create('cpl_cpmk', function (Blueprint $table) {
            $table->primary(['cpl_id', 'cpmk_id']);
            $table->foreignUuid('cpl_id')->constrained('program_learning_outcomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('cpmk_id')->constrained('course_learning_outcomes')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpl_cpmk');
    }
};
