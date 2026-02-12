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
        Schema::create('stock_transfer_products',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('quantity_transfered');

            //Secondary keys
            $table->unsignedBigInteger('stock_product_id');
            $table->unsignedBigInteger('stock_transfer_id');

            // Unique constraint for the combination of stock_transfer_id and stock_product_id
            $table->unique(['stock_transfer_id', 'stock_product_id'], 'unique_stock_transfer_product');
            
            $table->foreign('stock_product_id')->references('id')->on('stock_products')->onDelete('cascade')->name('fk_stock_transfer_products_stock_products_id');
            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');

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
        Schema::dropIfExists('stock_transfer_products');
    }
};
