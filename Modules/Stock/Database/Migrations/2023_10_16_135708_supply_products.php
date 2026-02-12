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
        Schema::create('supply_products',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('units_per_box');
            $table->date('expire_date');
            $table->string('lot_number');
            $table->integer('quantity');
            $table->double('purchase_price', 13, 2);
            $table->unsignedTinyInteger('profit_margin');

            // Constraint
            $table->unique(['lot_number', 'supply_id']);

            //Secondary keys
            $table->unsignedBigInteger('supply_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id');

            $table->foreign('supply_id')->references('id')->on('supplies')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
                
            //Additional attributes
            $table->integer('is_synced')->default(0); //To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); //Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); //To apply soft delete.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supply_products');
    }
};
