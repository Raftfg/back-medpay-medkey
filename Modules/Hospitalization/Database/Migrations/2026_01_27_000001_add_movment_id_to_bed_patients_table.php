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
        Schema::table('bed_patients', function (Blueprint $table) {
            // Vérifier si la colonne n'existe pas déjà
            if (!Schema::hasColumn('bed_patients', 'movment_id')) {
                $table->unsignedBigInteger('movment_id')->nullable()->after('patient_id');
                $table->foreign('movment_id')->references('id')->on('movments')->onDelete('set null');
                $table->index('movment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bed_patients', function (Blueprint $table) {
            if (Schema::hasColumn('bed_patients', 'movment_id')) {
                $table->dropForeign(['movment_id']);
                $table->dropIndex(['movment_id']);
                $table->dropColumn('movment_id');
            }
        });
    }
};
