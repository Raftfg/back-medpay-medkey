<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Référence à l'employé
            $table->unsignedBigInteger('employer_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            
            // Période du planning
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period_type')->default('weekly'); // weekly, monthly, custom
            
            // Statut
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('is_active')->default(true);
            
            // Métadonnées
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['employer_id', 'start_date', 'end_date']);
            $table->index(['service_id', 'start_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
