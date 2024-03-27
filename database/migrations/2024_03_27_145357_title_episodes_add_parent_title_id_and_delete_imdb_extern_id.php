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
            // Voeg een nieuwe kolom parent_title_id toe aan de tabel
            $table->unsignedBigInteger('parent_title_id')->nullable()->after('id');

            // Verwijder de kolom imdb_externid uit de tabel
            $table->dropColumn('imdb_externid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('title_episodes', function (Blueprint $table) {
            // Voeg de kolom imdb_externid opnieuw toe aan de tabel
            $table->string('imdb_externid')->nullable()->after('id');

            // Verwijder de nieuwe kolom parent_title_id uit de tabel
            $table->dropColumn('parent_title_id');
        });
    }
};
