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
        Schema::table('title_episodes', function (Blueprint $table) {
            $table->integer('season_number')->nullable()->change();
            $table->date('air_date')->nullable()->change();
            $table->integer('runtime_minutes')->nullable()->change();
            $table->unsignedBigInteger('language_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('title_episodes', function (Blueprint $table) {
            $table->integer('season_number')->change();
            $table->date('air_date')->change();
            $table->integer('runtime_minutes')->change();
            $table->unsignedBigInteger('language_id')->change();
        });
    }
};
