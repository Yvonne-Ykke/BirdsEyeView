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
        Schema::create('title_episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('title_id');
            $table->unsignedBigInteger('language_id');
            $table->string('imdb_externid')->nullable()->unique();
            $table->unsignedBigInteger('tmdb_externid')->nullable()->unique();
            $table->integer('season_number');
            $table->date('air_date');
            $table->integer('runtime_minutes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('title_episodes');
    }
};
