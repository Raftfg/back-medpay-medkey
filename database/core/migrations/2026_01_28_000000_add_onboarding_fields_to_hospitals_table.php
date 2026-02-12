<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Extension de la table CORE hospitals pour le processus d'onboarding SaaS.
 *
 * Ajoute :
 * - plan (trial / standard / premium)
 * - pays / ville
 * - langue principale
 * - champs d'état d'onboarding et de wizard
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('core')->table('hospitals', function (Blueprint $table) {
            // Informations commerciales / plan
            if (!Schema::connection('core')->hasColumn('hospitals', 'plan')) {
                $table->string('plan')->default('trial')->after('status');
            }

            // Localisation
            if (!Schema::connection('core')->hasColumn('hospitals', 'country')) {
                $table->string('country')->nullable()->after('address');
            }

            if (!Schema::connection('core')->hasColumn('hospitals', 'city')) {
                $table->string('city')->nullable()->after('country');
            }

            // Langue principale
            if (!Schema::connection('core')->hasColumn('hospitals', 'main_language')) {
                $table->string('main_language')->nullable()->after('email');
            }

            // Statut d'onboarding global
            if (!Schema::connection('core')->hasColumn('hospitals', 'onboarding_status')) {
                $table->string('onboarding_status')
                    ->default('pending')
                    ->after('provisioned_at'); // pending | provisioning | provisioned | failed | completed
            }

            // État du setup wizard (JSON stocké côté CORE)
            if (!Schema::connection('core')->hasColumn('hospitals', 'setup_wizard_state')) {
                $table->json('setup_wizard_state')->nullable()->after('onboarding_status');
            }

            if (!Schema::connection('core')->hasColumn('hospitals', 'setup_wizard_completed_at')) {
                $table->timestamp('setup_wizard_completed_at')->nullable()->after('setup_wizard_state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('core')->table('hospitals', function (Blueprint $table) {
            if (Schema::connection('core')->hasColumn('hospitals', 'plan')) {
                $table->dropColumn('plan');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'main_language')) {
                $table->dropColumn('main_language');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'onboarding_status')) {
                $table->dropColumn('onboarding_status');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'setup_wizard_state')) {
                $table->dropColumn('setup_wizard_state');
            }
            if (Schema::connection('core')->hasColumn('hospitals', 'setup_wizard_completed_at')) {
                $table->dropColumn('setup_wizard_completed_at');
            }
        });
    }
};

