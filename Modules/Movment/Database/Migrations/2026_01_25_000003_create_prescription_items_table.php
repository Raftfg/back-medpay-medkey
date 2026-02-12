<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            $table->unsignedBigInteger('prescription_id');
            $table->foreign('prescription_id')
                ->references('id')
                ->on('prescriptions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->unsignedBigInteger('product_id')->nullable()->comment('Produit du stock');
            $table->string('medication_name')->comment('Nom du médicament');
            $table->string('dosage')->nullable()->comment('Dosage (ex: 500mg)');
            $table->string('form')->nullable()->comment('Forme (comprimé, gélule, sirop, etc.)');
            $table->string('administration_route')->nullable()->comment('Voie d\'administration');
            $table->integer('quantity')->default(1)->comment('Quantité');
            $table->string('frequency')->nullable()->comment('Fréquence (ex: 2x/jour)');
            $table->text('instructions')->nullable()->comment('Instructions pour le patient');
            $table->integer('duration_days')->nullable()->comment('Durée en jours');
            $table->enum('status', ['pending', 'dispensed', 'completed', 'cancelled'])->default('pending');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescription_items');
    }
};
