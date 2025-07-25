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
        Schema::create('cpl_pl', function (Blueprint $table) {
            $table->foreignUuid('cpl_id')->constrained('program_learning_outcomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('pl_id')->constrained('graduate_profiles')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['cpl_id', 'pl_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpl_pl');
    }
};
