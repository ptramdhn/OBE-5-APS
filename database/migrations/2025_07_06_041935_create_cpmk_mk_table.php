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
        Schema::create('cpmk_mk', function (Blueprint $table) {
            $table->foreignUuid('cpmk_id')->constrained('course_learning_outcomes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('mk_id')->constrained('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['cpmk_id', 'mk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpmk_mk');
    }
};
