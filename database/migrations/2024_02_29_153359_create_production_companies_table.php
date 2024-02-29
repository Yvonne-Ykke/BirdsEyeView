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
        Schema::create('production_companies', function (Blueprint $table) {
            $table->id();
            $table->string('imdb_externid')->nullable()->unique();
            $table->unsignedBigInteger('tmdb_externid')->nullable()->unique();
            $table->string('name');
            $table->string('origin_country');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_companies');
    }
};
