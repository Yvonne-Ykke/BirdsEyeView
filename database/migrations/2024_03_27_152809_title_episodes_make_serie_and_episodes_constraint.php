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
            $table->unique(['parent_title_id', 'title_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('title_episodes', function (Blueprint $table) {
            $table->dropUnique(['parent_title_id', 'title_id']);
        });
    }
};
