<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employer_performances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Lien RH : employé + service
            $table->unsignedBigInteger('employers_id')->nullable();
            $table->unsignedBigInteger('services_id')->nullable();

            // Période d'évaluation
            $table->string('period_type')->default('custom'); // monthly, quarterly, annual, custom
            $table->date('start_date');
            $table->date('end_date');

            // Scores globaux
            $table->decimal('overall_score', 5, 2)->nullable(); // 0-100 ou 0-5 * 20

            // Critères détaillés (JSON) : ponctualité, assiduité, qualité soins, travail en équipe, etc.
            $table->json('criteria_scores')->nullable();

            // Commentaires & remarques
            $table->text('comments')->nullable();

            // Évaluateur (utilisateur connecté, médecin chef, cadre de santé, RH...)
            $table->unsignedBigInteger('evaluator_user_id')->nullable();

            $table->timestamps();

            $table->index(['employers_id', 'start_date', 'end_date']);
            $table->index(['services_id', 'start_date', 'end_date']);

            // Les contraintes FK sont optionnelles ici, car les tables sont dans d'autres modules
            // et la multi-tenancy est déjà assurée par la connexion 'tenant'.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_performances');
    }
};
