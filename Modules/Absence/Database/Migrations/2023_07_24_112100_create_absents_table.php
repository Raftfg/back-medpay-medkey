<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('vacations_id')->nullable();
            $table->unsignedBigInteger('missions_id')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->timestamps();

            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible
        });

        // En provisioning multi-tenant, l'ordre de migrations peut varier.
        // On pose les contraintes uniquement si les tables cibles existent.
        Schema::table('absents', function (Blueprint $table) {
            if (Schema::hasTable('vacations')) {
                $table->foreign('vacations_id')->references('id')->on('vacations')->restrictOnDelete();
            }

            if (Schema::hasTable('missions')) {
                $table->foreign('missions_id')->references('id')->on('missions')->restrictOnDelete();
            }

            if (Schema::hasTable('users')) {
                $table->foreign('users_id')->references('id')->on('users')->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absents');
    }
}
