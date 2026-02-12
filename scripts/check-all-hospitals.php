<?php

/**
 * Script pour vérifier les migrations et contraintes pour tous les hôpitaux
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Vérification de tous les hôpitaux                           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Récupérer tous les hôpitaux actifs
$hospitals = Hospital::whereIn('status', ['active', 'provisioning'])->get();

if ($hospitals->isEmpty()) {
    echo "❌ Aucun hôpital actif trouvé.\n";
    exit(1);
}

echo "📊 Nombre d'hôpitaux à vérifier : {$hospitals->count()}\n\n";

$tenantService = app(TenantConnectionService::class);
$results = [];

foreach ($hospitals as $hospital) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})\n";
    echo "   - Base de données : {$hospital->database_name}\n";
    echo "   - Statut : {$hospital->status}\n\n";
    
    $result = [
        'hospital_id' => $hospital->id,
        'hospital_name' => $hospital->name,
        'database_name' => $hospital->database_name,
        'status' => $hospital->status,
        'migrations_count' => 0,
        'constraints_count' => 0,
        'hospital_id_columns' => 0,
        'errors' => [],
        'warnings' => [],
        'success' => false,
    ];
    
    try {
        // Connecter à la base tenant
        $tenantService->connect($hospital);
        $tenantConnection = $tenantService->getCurrentConnection();
        $database = $tenantConnection->getDatabaseName();
        
        echo "   ✅ Connecté à la base de données\n";
        
        // 1. Vérifier les migrations
        try {
            $migrations = $tenantConnection->table('migrations')->get();
            $result['migrations_count'] = $migrations->count();
            echo "   📋 Migrations : {$result['migrations_count']}\n";
        } catch (\Exception $e) {
            $result['errors'][] = "Erreur migrations : " . $e->getMessage();
            echo "   ❌ Erreur migrations : {$e->getMessage()}\n";
        }
        
        // 2. Vérifier les contraintes
        try {
            $constraints = $tenantConnection->select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database]);
            $result['constraints_count'] = $constraints[0]->count ?? 0;
            
            // Vérifier les contraintes vers 'hospitals'
            $hospitalConstraints = $tenantConnection->select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND REFERENCED_TABLE_NAME = 'hospitals'
            ", [$database]);
            $hospitalConstraintsCount = $hospitalConstraints[0]->count ?? 0;
            
            if ($hospitalConstraintsCount > 0) {
                $result['warnings'][] = "{$hospitalConstraintsCount} contrainte(s) vers 'hospitals' trouvée(s)";
                echo "   ⚠️  Contraintes vers 'hospitals' : {$hospitalConstraintsCount}\n";
            }
            
            // Vérifier les contraintes cassées
            $allConstraints = $tenantConnection->select("
                SELECT 
                    kcu.TABLE_NAME,
                    kcu.REFERENCED_TABLE_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                WHERE kcu.TABLE_SCHEMA = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database]);
            
            $brokenConstraints = [];
            foreach ($allConstraints as $constraint) {
                $refTable = $constraint->REFERENCED_TABLE_NAME;
                $tableExists = $tenantConnection->select("
                    SELECT COUNT(*) as count 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = ? 
                    AND TABLE_NAME = ?
                ", [$database, $refTable]);
                
                if ($tableExists[0]->count == 0) {
                    $brokenConstraints[] = "{$constraint->TABLE_NAME} -> {$refTable}";
                }
            }
            
            if (!empty($brokenConstraints)) {
                $result['errors'][] = count($brokenConstraints) . " contrainte(s) cassée(s) : " . implode(', ', array_slice($brokenConstraints, 0, 3));
                echo "   ❌ Contraintes cassées : " . count($brokenConstraints) . "\n";
            } else {
                echo "   ✅ Contraintes : {$result['constraints_count']} (toutes valides)\n";
            }
        } catch (\Exception $e) {
            $result['errors'][] = "Erreur contraintes : " . $e->getMessage();
            echo "   ❌ Erreur contraintes : {$e->getMessage()}\n";
        }
        
        // 3. Vérifier les colonnes hospital_id
        try {
            $tablesWithHospitalId = $tenantConnection->select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ?
                AND COLUMN_NAME = 'hospital_id'
            ", [$database]);
            $result['hospital_id_columns'] = $tablesWithHospitalId[0]->count ?? 0;
            
            if ($result['hospital_id_columns'] > 0) {
                $result['warnings'][] = "{$result['hospital_id_columns']} colonne(s) 'hospital_id' trouvée(s)";
                echo "   ⚠️  Colonnes hospital_id : {$result['hospital_id_columns']}\n";
            } else {
                echo "   ✅ Colonnes hospital_id : 0 (correct)\n";
            }
        } catch (\Exception $e) {
            $result['errors'][] = "Erreur vérification hospital_id : " . $e->getMessage();
            echo "   ❌ Erreur vérification hospital_id : {$e->getMessage()}\n";
        }
        
        // 4. Test de requête
        try {
            $testQuery = $tenantConnection->table('users')->limit(1)->get();
            echo "   ✅ Test de requête : OK\n";
        } catch (\Exception $e) {
            $result['warnings'][] = "Table 'users' non accessible : " . $e->getMessage();
            echo "   ⚠️  Test de requête : Table 'users' non accessible\n";
        }
        
        $result['success'] = empty($result['errors']);
        
    } catch (\Exception $e) {
        $result['errors'][] = "Erreur de connexion : " . $e->getMessage();
        echo "   ❌ Erreur de connexion : {$e->getMessage()}\n";
    }
    
    $results[] = $result;
    echo "\n";
}

// Résumé global
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ GLOBAL                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$successCount = 0;
$errorCount = 0;
$warningCount = 0;

foreach ($results as $result) {
    if ($result['success'] && empty($result['warnings'])) {
        $successCount++;
        echo "✅ {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Migrations : {$result['migrations_count']}\n";
        echo "   - Contraintes : {$result['constraints_count']}\n";
        echo "   - Colonnes hospital_id : {$result['hospital_id_columns']}\n";
    } elseif ($result['success'] && !empty($result['warnings'])) {
        $warningCount++;
        echo "⚠️  {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Migrations : {$result['migrations_count']}\n";
        echo "   - Contraintes : {$result['constraints_count']}\n";
        echo "   - Colonnes hospital_id : {$result['hospital_id_columns']}\n";
        foreach ($result['warnings'] as $warning) {
            echo "   ⚠️  {$warning}\n";
        }
    } else {
        $errorCount++;
        echo "❌ {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        foreach ($result['errors'] as $error) {
            echo "   ❌ {$error}\n";
        }
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Statistiques :\n";
echo "   ✅ Succès complets : {$successCount}\n";
echo "   ⚠️  Avec avertissements : {$warningCount}\n";
echo "   ❌ Avec erreurs : {$errorCount}\n";
echo "   📊 Total : " . count($results) . "\n\n";

if ($errorCount === 0 && $warningCount === 0) {
    echo "✅ Tous les hôpitaux sont en parfait état !\n";
} elseif ($errorCount === 0) {
    echo "⚠️  Tous les hôpitaux sont fonctionnels mais certains ont des avertissements.\n";
} else {
    echo "❌ Certains hôpitaux ont des erreurs nécessitant une attention.\n";
}

echo "\n";
