<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();

            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(config('rendezvous.default_slot_duration', 30));

            $table->string('type')->default('consultation');
            $table->string('status')->default('pending');
            $table->string('source')->default('on_site');

            $table->text('notes')->nullable();
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('second_reminder_sent_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['doctor_id', 'scheduled_at']);
            $table->index(['patient_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

