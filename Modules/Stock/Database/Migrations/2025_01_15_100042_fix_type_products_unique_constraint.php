<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur type_products
 * 
 * Remplace la contrainte unique sur 'name' seul par une contrainte unique composite
 * sur (hospital_id, name) pour permettre le même nom dans différents hôpitaux.
 * 
 * @package Modules\Stock\Database\Migrations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Vérifier si hospital_id existe avant de modifier les contraintes
        if (!Schema::hasColumn('type_products', 'hospital_id')) {
            // La colonne hospital_id n'existe pas encore, on ne peut pas modifier les contraintes
            return;
        }

        Schema::table('type_products', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte unique sur 'name' seul
            // Laravel crée généralement un index nommé 'type_products_name_unique'
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                // Si l'index n'existe pas ou a un nom différent, on essaie de le trouver
                $indexes = DB::select("SHOW INDEX FROM type_products WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer si l'index n'existe pas
                    }
                }
            }
        });

        Schema::table('type_products', function (Blueprint $table) {
            // Ajouter une contrainte unique composite sur (hospital_id, name)
            // Cela permet le même nom dans différents hôpitaux
            $table->unique(['hospital_id', 'name'], 'type_products_hospital_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('type_products', function (Blueprint $table) {
            // Supprimer la contrainte unique composite
            $table->dropUnique('type_products_hospital_name_unique');
            
            // Restaurer l'ancienne contrainte unique sur 'name' seul
            $table->unique('name');
        });
    }
};
