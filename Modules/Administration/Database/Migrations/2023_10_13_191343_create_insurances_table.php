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
        Schema::create('insurances', function (Blueprint $table){
            $table->id();
            $table->string('name');
            // $table->string('number_insurance')->nullable();
            // $table->string('insuranceComp');
            // $table->boolean('is_convention');
            // $table->string('phone')->nullable();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('users_id');
            $table->boolean('is_synced')->default(0);
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();
        });

        if (Schema::hasTable('insurances') && Schema::hasTable('users')) {
            Schema::table('insurances', function (Blueprint $table) {
                $table->foreign('users_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('insurances');
    }
};
