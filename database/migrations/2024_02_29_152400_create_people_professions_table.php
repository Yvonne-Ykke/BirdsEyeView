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
        Schema::create('people_professions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('profession_id');
            $table->timestamps();

            $table->foreign('people_id')->references('id')->on('people');
            $table->foreign('profession_id')->references('id')->on('professions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people_professions');
    }
};
