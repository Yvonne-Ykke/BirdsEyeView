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
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->string('imdb_externid')->nullable();
            $table->unsignedBigInteger('tmdb_externid')->nullable();
            $table->string('primary_title')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_adult')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->integer('runtime_minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
