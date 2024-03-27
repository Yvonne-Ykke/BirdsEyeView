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
        Schema::create('crew_professions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('crew_id');
            $table->unsignedInteger('profession_id');

            $table->foreign('crew_id')->references('id')->on('model_has_crew');
            $table->foreign('profession_id')->references('id')->on('professions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_professions');
    }
};
