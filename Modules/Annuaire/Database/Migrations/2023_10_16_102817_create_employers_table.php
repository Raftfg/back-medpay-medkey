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
        Schema::create('employers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('sex')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->integer('charge')->nullable();
            $table->string('urgency_phone')->nullable();
            $table->string('urgency_name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('contract_lenght')->nullable();
            $table->string('work_time')->nullable();
            $table->string('contract_type')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('function')->nullable();
            $table->string('ifu')->nullable();
            $table->string('npi')->nullable();
            $table->string('motif')->nullable();
            $table->string('social_security_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('employment_status')->nullable();
            // $table->unsignedBigInteger('assurances_id')->nullable();
            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('departments_id');


            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible
            // $table->foreign('assurances_id')->references('id')->on('insurances')->restrictOnDelete();

            $table->timestamps();
        });

        if (Schema::hasTable('employers')) {
            Schema::table('employers', function (Blueprint $table) {
                if (Schema::hasTable('services')) {
                    $table->foreign('services_id')->references('id')->on('services')->restrictOnDelete();
                }

                if (Schema::hasTable('departments')) {
                    $table->foreign('departments_id')->references('id')->on('departments')->restrictOnDelete();
                }
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
        Schema::dropIfExists('employers');
    }
};
