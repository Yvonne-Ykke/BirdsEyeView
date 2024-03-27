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
        Schema::table('people_professions', function (Blueprint $table) {
            $table->unique(['people_id', 'profession_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people_professions', function (Blueprint $table) {
            $table->dropUnique('people_profession_people_id_profession_id_unique');
        });
    }
};
