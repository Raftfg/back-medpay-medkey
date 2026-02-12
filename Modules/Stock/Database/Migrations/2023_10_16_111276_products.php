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
        Schema::create('products',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name', 255);
            $table->string('dosage', 255)->nullable();
            $table->string('brand', 255)->nullable();

            // Secondary keys
            $table->unsignedBigInteger('conditioning_unit_id')->index();
            $table->unsignedBigInteger('administration_route_id')->index()->nullable();;
            $table->unsignedBigInteger('sale_unit_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('type_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable(); // Store the ID of the user that is executing an action on the resource.

            $table->foreign('conditioning_unit_id')->references('id')->on('conditioning_units')->onDelete('cascade');
            $table->foreign('administration_route_id')->references('id')->on('administration_routes')->onDelete('cascade');
            $table->foreign('sale_unit_id')->references('id')->on('sale_units')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('type_products')->onDelete('cascade');
            // Prevent the deletion of the products associated to a user when the user is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Additional attributes
            $table->integer('is_synced')->default(0); // To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); // Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); // To apply soft delete.
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
        Schema::dropIfExists('products');
    }
};
