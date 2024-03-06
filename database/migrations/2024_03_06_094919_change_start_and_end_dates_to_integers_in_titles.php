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
        Schema::table('titles', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("
        alter table public.titles
        alter column start_date type integer
            using (DATE_PART('year', start_date))::integer;");

            \Illuminate\Support\Facades\DB::statement("
    alter table public.titles
    alter column end_date type integer
        using (DATE_PART('year', end_date))::integer;");

            $table->renameColumn('start_date', 'start_year');
            $table->renameColumn('end_date', 'end_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->date('start_year')->nullable()->change();
            $table->date('end_year')->nullable()->change();

            $table->renameColumn('start_year', 'start_date');
            $table->renameColumn('end_year', 'end_date');
        });
    }
};
