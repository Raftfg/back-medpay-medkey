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
        Schema::create('stock_transfers',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('comment')->nullable();
            $table->string('model_name');
            $table->unsignedBigInteger('model_id');

            //Secondary keys
            $table->unsignedBigInteger('from_stock_id');
            $table->unsignedBigInteger('user_id')->index()->nullable();; //Store the ID of the user that is executing an action on the resource.

            $table->foreign('from_stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

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
        Schema::dropIfExists('stock_transfers');
    }
};
