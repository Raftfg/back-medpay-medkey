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
        Schema::create('remboursement_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(); // UUID unique
            $table->boolean('is_synced')->default(0); // Défaut à 0
            $table->unsignedBigInteger('facture_id');
            $table->unsignedBigInteger('remboursement_id');
            $table->decimal('montant_rembourse', 10, 2);
            // Ajoutez d'autres colonnes si nécessaire

            $table->foreign('facture_id')->references('id')->on('factures')->onDelete('cascade');
            $table->foreign('remboursement_id')->references('id')->on('remboursements')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('remboursement_details');
    }
};
