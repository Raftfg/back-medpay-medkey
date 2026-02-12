<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_shifts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Référence au planning
            $table->unsignedBigInteger('work_schedule_id');
            
            // Référence à l'employé
            $table->unsignedBigInteger('employer_id');
            
            // Informations du shift
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('shift_type')->default('normal'); // normal, guard, on_call, overtime
            $table->string('rotation_type')->nullable(); // morning, afternoon, night
            
            // Service et poste
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('position')->nullable();
            
            // Statut
            $table->string('status')->default('scheduled'); // scheduled, confirmed, cancelled, completed
            $table->boolean('is_swap')->default(false);
            $table->unsignedBigInteger('swapped_with_id')->nullable();
            
            // Validation légale
            $table->integer('duration_hours')->nullable(); // Durée en heures
            $table->boolean('respects_legal_duration')->default(true);
            $table->boolean('respects_rest_period')->default(true);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['work_schedule_id', 'shift_date']);
            $table->index(['employer_id', 'shift_date']);
            $table->index(['service_id', 'shift_date']);
            $table->index('status');
            
            $table->foreign('work_schedule_id')->references('id')->on('work_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_shifts');
    }
};
