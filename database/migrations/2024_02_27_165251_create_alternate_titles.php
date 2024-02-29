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
        Schema::create('alternate_titles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('title_id');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string('imdb_externid')->nullable()->unique();
            $table->unsignedBigInteger('tmdb_externid')->nullable()->unique();
            $table->string('title');
            $table->integer('ordering')->nullable();
            $table->string('region')->nullable();
            $table->string('types')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_original_title')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alternate_titles');
    }
};
