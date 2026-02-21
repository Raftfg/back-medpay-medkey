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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('users_id');
            $table->boolean('is_synced')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('product_types') && Schema::hasTable('users')) {
            Schema::table('product_types', function (Blueprint $table) {
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
        Schema::dropIfExists('product_types');
    }
};
