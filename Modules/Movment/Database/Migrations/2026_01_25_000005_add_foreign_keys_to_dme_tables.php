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
        // Ajouter la contrainte pour prescriptions.clinical_observation_id
        // seulement si la table clinical_observations existe
        if (Schema::hasTable('clinical_observations')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                // Vérifier si la contrainte n'existe pas déjà
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('prescriptions');
                
                $constraintExists = false;
                foreach ($foreignKeys as $foreignKey) {
                    if ($foreignKey->getColumns()[0] === 'clinical_observation_id') {
                        $constraintExists = true;
                        break;
                    }
                }
                
                if (!$constraintExists) {
                    $table->foreign('clinical_observation_id')
                        ->references('id')
                        ->on('clinical_observations')
                        ->onUpdate('cascade')
                        ->onDelete('set null');
                }
            });
            
            // Ajouter la contrainte pour dme_documents.clinical_observation_id
            if (Schema::hasTable('dme_documents')) {
                Schema::table('dme_documents', function (Blueprint $table) {
                    // Vérifier si la contrainte n'existe pas déjà
                    $foreignKeys = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableForeignKeys('dme_documents');
                    
                    $constraintExists = false;
                    foreach ($foreignKeys as $foreignKey) {
                        if ($foreignKey->getColumns()[0] === 'clinical_observation_id') {
                            $constraintExists = true;
                            break;
                        }
                    }
                    
                    if (!$constraintExists) {
                        $table->foreign('clinical_observation_id')
                            ->references('id')
                            ->on('clinical_observations')
                            ->onUpdate('cascade')
                            ->onDelete('set null');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer les contraintes si elles existent
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropForeign(['clinical_observation_id']);
            });
        }
        
        if (Schema::hasTable('dme_documents')) {
            Schema::table('dme_documents', function (Blueprint $table) {
                $table->dropForeign(['clinical_observation_id']);
            });
        }
    }
};
