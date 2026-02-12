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
        Schema::create('sale_products',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('price');
            $table->integer('quantity');

            //Secondary keys
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('stock_products_id');
            
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade')
                ->name('fk_sale_products_stock_products_id');

            $table->foreign('stock_products_id')
                ->references('id')
                ->on('stock_products')
                ->onDelete('cascade')
                ->name('fk_sale_products_sale_id');

                //Additional attributes
                $table->unsignedBigInteger('user_id'); //Store the ID of the user that is executing an action on the resource.
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
        Schema::dropIfExists('sale_products');
    }
};
