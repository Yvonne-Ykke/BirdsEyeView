<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alternate_titles', function (Blueprint $table) {
            $table->dropColumn('imdb_externid');
            $table->dropColumn('tmdb_externid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alternate_titles', function (Blueprint $table) {
            $table->string('imdb_externid')->nullable()->unique()->after('language_id');
            $table->unsignedBigInteger('tmdb_externid')->nullable()->unique()->after('imdb_externid');
        });
    }
};
