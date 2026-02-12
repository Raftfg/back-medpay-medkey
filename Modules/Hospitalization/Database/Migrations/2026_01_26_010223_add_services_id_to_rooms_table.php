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
        Schema::connection('tenant')->table('rooms', function (Blueprint $table) {
            // Ajouter la colonne services_id si elle n'existe pas déjà
            if (!Schema::connection('tenant')->hasColumn('rooms', 'services_id')) {
                $table->unsignedBigInteger('services_id')->nullable();
                $table->foreign('services_id')
                    ->references('id')
                    ->on('services')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->table('rooms', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            if (Schema::connection('tenant')->hasColumn('rooms', 'services_id')) {
                $table->dropForeign(['services_id']);
                $table->dropColumn('services_id');
            }
        });
    }
};
