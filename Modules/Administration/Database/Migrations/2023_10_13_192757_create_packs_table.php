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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('designation');
            $table->decimal('percentage', 8, 2);
            $table->unsignedBigInteger('insurances_id')->nullable();
            // $table->unsignedBigInteger('product_types_id');
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('users_id');
            $table->boolean('is_synced')->default(0);
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('users_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();

            // Clé étrangère insurance_id
            $table->foreign('insurances_id')
                ->references('id')
                ->on('insurances')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Clé étrangère ProductType_id
            // $table->foreign('product_types_id')
            //     ->references('id')
            //     ->on('product_types')
            //     ->onUpdate('cascade')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packs');
    }
};
