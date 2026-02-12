<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur suppliers
 * 
 * Remplace les contraintes uniques sur 'email' et 'phone_number' par des contraintes
 * uniques composites sur (hospital_id, email) et (hospital_id, phone_number)
 * pour permettre le même email/téléphone dans différents hôpitaux.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('suppliers', 'hospital_id')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            // Supprimer les contraintes uniques sur 'email' et 'phone_number'
            try {
                $table->dropUnique(['email']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM suppliers WHERE Column_name = 'email' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
            
            try {
                $table->dropUnique(['phone_number']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM suppliers WHERE Column_name = 'phone_number' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // Ajouter des contraintes uniques composites
            // Note: Pour email, on ne peut pas créer une contrainte unique composite avec NULL
            // On crée un index unique partiel ou on accepte que plusieurs NULL soient possibles
            // Pour l'instant, on crée la contrainte composite qui fonctionnera pour les valeurs non-null
            $table->unique(['hospital_id', 'phone_number'], 'suppliers_hospital_phone_unique');
            
            // Pour email, on crée un index unique composite mais MySQL permet plusieurs NULL
            // Si vous voulez vraiment forcer l'unicité même pour NULL, il faudrait utiliser un trigger
            // Pour l'instant, on crée la contrainte qui fonctionnera pour les valeurs non-null
            $table->unique(['hospital_id', 'email'], 'suppliers_hospital_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique('suppliers_hospital_phone_unique');
            $table->dropUnique('suppliers_hospital_email_unique');
            $table->unique('phone_number');
            $table->unique('email');
        });
    }
};
