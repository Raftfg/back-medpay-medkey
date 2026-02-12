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
        Schema::create('stock_products',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('lot_number');
            $table->integer('units_per_box');
            $table->date('expire_date');
            $table->integer('quantity');
            $table->decimal('purchase_price', 13, 2);
            $table->decimal('selling_price', 13, 2);


            //Secondary keys
            $table->unsignedBigInteger('stock_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

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
        Schema::dropIfExists('stock_products');
    }
};
