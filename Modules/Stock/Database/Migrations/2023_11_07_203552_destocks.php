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
        Schema::create('destocks', function (Blueprint $table) {
            $table->id();
            $table->string('reference_facture');
            $table->integer('quantity_retrieved');
            $table->integer('quantity_ordered');

            $table->unsignedBigInteger('stock_product_id');
            $table->unsignedBigInteger('user_id')->index()->nullable(); // Store the ID of the user that is executing an action on the resource.

            // Clé étrangère liée à la table 'stock_id'
            $table->foreign('stock_product_id')->references('id')->on('stock_products')->onDelete('cascade'); 

            //Additional attributes
            $table->integer('is_synced')->default(0); //To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); //Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); //To apply soft delete.
            $table->timestamps();
        });

        if (Schema::hasTable('destocks') && Schema::hasTable('users')) {
            Schema::table('destocks', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('destock');
    }
};
