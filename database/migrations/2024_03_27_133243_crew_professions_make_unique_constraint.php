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
        Schema::table('crew_professions', function (Blueprint $table) {
            $table->unique(['crew_id', 'profession_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_professions', function (Blueprint $table) {
            $table->dropUnique('crew_professions_crew_id_profession_id_unique');
        });
    }
};
