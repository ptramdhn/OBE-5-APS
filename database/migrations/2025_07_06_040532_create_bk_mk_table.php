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
        Schema::create('bk_mk', function (Blueprint $table) {
            $table->foreignUuid('mk_id')->constrained('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('bk_id')->constrained('study_materials')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['mk_id', 'bk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_mk');
    }
};
