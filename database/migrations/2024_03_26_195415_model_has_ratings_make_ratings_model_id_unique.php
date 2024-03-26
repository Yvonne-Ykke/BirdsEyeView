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
        Schema::table('model_has_ratings', function (Blueprint $table) {
            $table->unique('model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_has_ratings', function (Blueprint $table) {
            $table->dropUnique('model_has_ratings_model_id_unique');
        });
    }
};
