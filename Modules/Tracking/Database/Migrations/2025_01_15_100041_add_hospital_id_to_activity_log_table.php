<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Administration\Entities\Hospital;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_log', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        // Assigner hospital_id depuis la relation causer (user)
        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            // Mettre à jour via la relation causer (user) si disponible
            DB::statement("
                UPDATE activity_log al
                INNER JOIN users u ON al.causer_id = u.id AND al.causer_type = 'Modules\\\\Acl\\\\Entities\\\\User'
                SET al.hospital_id = COALESCE(u.hospital_id, {$defaultHospital->id})
                WHERE al.hospital_id IS NULL
            ");
            
            // Pour les autres cas, assigner l'hôpital par défaut
            DB::table('activity_log')
                ->whereNull('hospital_id')
                ->update(['hospital_id' => $defaultHospital->id]);
        }
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            if (Schema::hasColumn('activity_log', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
