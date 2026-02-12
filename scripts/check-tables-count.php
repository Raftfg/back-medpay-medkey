<?php

/**
 * Script pour vérifier le nombre de tables dans chaque base tenant
 * et comparer avec le nombre initial (99 tables)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Vérification du nombre de tables par hôpital                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Récupérer tous les hôpitaux actifs
$hospitals = Hospital::whereIn('status', ['active', 'provisioning'])->get();

if ($hospitals->isEmpty()) {
    echo "❌ Aucun hôpital actif trouvé.\n";
    exit(1);
}

$tenantService = app(TenantConnectionService::class);
$expectedTables = 99; // Nombre initial de tables
$results = [];

// Récupérer la liste complète des tables de la base principale pour comparaison
echo "📊 Récupération de la liste des tables de la base principale...\n";
$mainDatabase = config('database.connections.mysql.database');
$mainTables = DB::connection('mysql')->select("
    SELECT TABLE_NAME 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = ?
    AND TABLE_TYPE = 'BASE TABLE'
    ORDER BY TABLE_NAME
", [$mainDatabase]);

$mainTableNames = array_map(function($table) {
    return $table->TABLE_NAME;
}, $mainTables);

// Exclure les tables CORE et système
$excludedTables = [
    'hospitals',
    'hospital_modules',
    'system_admins',
    'migrations',
    'password_reset_tokens',
    'failed_jobs',
    'personal_access_tokens',
    'oauth_access_tokens',
    'oauth_auth_codes',
    'oauth_clients',
    'oauth_personal_access_clients',
    'oauth_refresh_tokens',
];

$expectedTableNames = array_filter($mainTableNames, function($table) use ($excludedTables) {
    return !in_array($table, $excludedTables) && !str_starts_with($table, 'medkey_core_');
});

$expectedCount = count($expectedTableNames);
echo "   📋 Tables attendues (hors CORE/système) : {$expectedCount}\n\n";

foreach ($hospitals as $hospital) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})\n";
    echo "   - Base de données : {$hospital->database_name}\n\n";
    
    try {
        $tenantService->connect($hospital);
        $tenantConnection = $tenantService->getCurrentConnection();
        $database = $tenantConnection->getDatabaseName();
        
        // Récupérer toutes les tables
        $tables = $tenantConnection->select("
            SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [$database]);
        
        $tableNames = array_map(function($table) {
            return $table->TABLE_NAME;
        }, $tables);
        
        $tableCount = count($tableNames);
        
        echo "   📊 Nombre de tables : {$tableCount}\n";
        
        // Comparer avec les tables attendues
        $missingTables = array_diff($expectedTableNames, $tableNames);
        $extraTables = array_diff($tableNames, $expectedTableNames);
        
        if (!empty($missingTables)) {
            echo "   ⚠️  Tables manquantes : " . count($missingTables) . "\n";
            if (count($missingTables) <= 20) {
                foreach ($missingTables as $table) {
                    echo "      - {$table}\n";
                }
            } else {
                $firstMissing = array_slice($missingTables, 0, 10);
                foreach ($firstMissing as $table) {
                    echo "      - {$table}\n";
                }
                echo "      ... et " . (count($missingTables) - 10) . " autre(s)\n";
            }
        } else {
            echo "   ✅ Toutes les tables attendues sont présentes\n";
        }
        
        if (!empty($extraTables)) {
            echo "   ℹ️  Tables supplémentaires : " . count($extraTables) . "\n";
            foreach ($extraTables as $table) {
                echo "      - {$table}\n";
            }
        }
        
        // Comparaison avec le nombre initial
        if ($tableCount < $expectedCount) {
            $diff = $expectedCount - $tableCount;
            echo "   ⚠️  Manque {$diff} table(s) par rapport aux {$expectedCount} attendues\n";
        } elseif ($tableCount > $expectedCount) {
            $diff = $tableCount - $expectedCount;
            echo "   ℹ️  {$diff} table(s) supplémentaire(s)\n";
        } else {
            echo "   ✅ Nombre de tables correct ({$tableCount})\n";
        }
        
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'table_count' => $tableCount,
            'expected_count' => $expectedCount,
            'missing_tables' => count($missingTables),
            'missing_table_names' => $missingTables,
        ];
        
    } catch (\Exception $e) {
        echo "   ❌ Erreur : {$e->getMessage()}\n";
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'error' => $e->getMessage(),
        ];
    }
    
    echo "\n";
}

// Résumé global
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ GLOBAL                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Comparaison avec la base principale :\n";
echo "   - Tables dans la base principale : " . count($mainTableNames) . "\n";
echo "   - Tables attendues (hors CORE/système) : {$expectedCount}\n\n";

foreach ($results as $result) {
    if (isset($result['error'])) {
        echo "❌ {$result['hospital_name']} (ID: {$result['hospital_id']}) : Erreur\n";
        echo "   - {$result['error']}\n";
    } else {
        $status = $result['table_count'] === $result['expected_count'] ? '✅' : '⚠️';
        echo "{$status} {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Tables : {$result['table_count']} / {$result['expected_count']}\n";
        if ($result['missing_tables'] > 0) {
            echo "   - Tables manquantes : {$result['missing_tables']}\n";
        }
    }
    echo "\n";
}

// Identifier les tables manquantes communes
if (count($results) > 1) {
    $allMissing = [];
    foreach ($results as $result) {
        if (isset($result['missing_table_names'])) {
            foreach ($result['missing_table_names'] as $table) {
                if (!isset($allMissing[$table])) {
                    $allMissing[$table] = 0;
                }
                $allMissing[$table]++;
            }
        }
    }
    
    if (!empty($allMissing)) {
        $commonMissing = array_filter($allMissing, function($count) use ($results) {
            return $count === count($results);
        });
        
        if (!empty($commonMissing)) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "⚠️  Tables manquantes dans TOUS les hôpitaux :\n";
            foreach ($commonMissing as $table => $count) {
                echo "   - {$table}\n";
            }
            echo "\n";
        }
    }
}

echo "\n";
