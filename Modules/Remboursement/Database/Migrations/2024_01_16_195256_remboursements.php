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
        //
        Schema::create('remboursements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(); // UUID unique
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_synced')->default(0); // Défaut à 0
            $table->unsignedBigInteger('patient_id');
            $table->timestamp('date_remboursement');
            // Ajoutez d'autres colonnes si nécessaire
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('remboursements')) {
            Schema::table('remboursements', function (Blueprint $table) {
                if (Schema::hasTable('patients')) {
                    $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                }

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('remboursements');
    }
};
