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
        Schema::create('recouvrements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(); // UUID unique
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_synced')->default(0); // Défaut à 0
            $table->string('reference_facture');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('movement_id');
            $table->unsignedBigInteger('mode_payements_id');
            $table->decimal('montant_facture', 10, 2);
            // $table->decimal('montant_paye', 10, 2);
            $table->decimal('pourcentage_assurance', 5, 2);
            $table->decimal('montant_saisi', 10, 2);
            $table->date('date_recouvrement');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('movement_id')->references('id')->on('movments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
