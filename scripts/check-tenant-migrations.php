<?php

/**
 * Script pour vérifier l'état des migrations et des contraintes dans la base tenant
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Vérification des migrations et contraintes tenant          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$hospitalId = $argv[1] ?? 1;

// Récupérer l'hôpital
$hospital = Hospital::find($hospitalId);

if (!$hospital) {
    echo "❌ Hôpital avec l'ID {$hospitalId} introuvable.\n";
    exit(1);
}

echo "🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})\n";
echo "   - Base de données : {$hospital->database_name}\n\n";

// Connecter à la base tenant
$tenantService = app(TenantConnectionService::class);
try {
    $tenantService->connect($hospital);
    echo "✅ Connecté à la base de données tenant\n\n";
} catch (\Exception $e) {
    echo "❌ Erreur de connexion : {$e->getMessage()}\n";
    exit(1);
}

$tenantConnection = $tenantService->getCurrentConnection();
$database = $tenantConnection->getDatabaseName();

// 1. Vérifier les migrations
echo "📋 1. Vérification des migrations...\n";
try {
    $migrations = $tenantConnection->table('migrations')->get();
    echo "   ✅ Table 'migrations' existe\n";
    echo "   📊 Nombre de migrations exécutées : " . $migrations->count() . "\n";
    
    if ($migrations->count() > 0) {
        echo "   📝 Dernières migrations :\n";
        $lastMigrations = $migrations->sortByDesc('id')->take(5);
        foreach ($lastMigrations as $migration) {
            echo "      - {$migration->migration}\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur : {$e->getMessage()}\n";
}
echo "\n";

// 2. Vérifier les contraintes de clés étrangères
echo "🔗 2. Vérification des contraintes de clés étrangères...\n";
try {
    $foreignKeys = $tenantConnection->select("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = ?
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME
    ", [$database]);
    
    echo "   📊 Nombre de contraintes de clés étrangères : " . count($foreignKeys) . "\n";
    
    // Vérifier s'il y a des contraintes vers 'hospitals' (ne devrait pas y en avoir)
    $hospitalConstraints = array_filter($foreignKeys, function($fk) {
        return strtolower($fk->REFERENCED_TABLE_NAME) === 'hospitals';
    });
    
    if (count($hospitalConstraints) > 0) {
        echo "   ⚠️  ATTENTION : " . count($hospitalConstraints) . " contrainte(s) vers 'hospitals' trouvée(s) :\n";
        foreach ($hospitalConstraints as $constraint) {
            echo "      - Table: {$constraint->TABLE_NAME}, Colonne: {$constraint->COLUMN_NAME}\n";
        }
    } else {
        echo "   ✅ Aucune contrainte vers 'hospitals' (correct)\n";
    }
    
    // Vérifier les contraintes cassées (référençant des tables inexistantes)
    $brokenConstraints = [];
    foreach ($foreignKeys as $fk) {
        $refTable = $fk->REFERENCED_TABLE_NAME;
        $tableExists = $tenantConnection->select("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ?
        ", [$database, $refTable]);
        
        if ($tableExists[0]->count == 0) {
            $brokenConstraints[] = $fk;
        }
    }
    
    if (count($brokenConstraints) > 0) {
        echo "   ⚠️  ATTENTION : " . count($brokenConstraints) . " contrainte(s) cassée(s) trouvée(s) :\n";
        foreach ($brokenConstraints as $constraint) {
            echo "      - Table: {$constraint->TABLE_NAME}, Référence: {$constraint->REFERENCED_TABLE_NAME} (n'existe pas)\n";
        }
    } else {
        echo "   ✅ Toutes les contraintes référencent des tables existantes\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur : {$e->getMessage()}\n";
}
echo "\n";

// 3. Vérifier les colonnes hospital_id restantes
echo "🏥 3. Vérification des colonnes hospital_id...\n";
try {
    $tablesWithHospitalId = $tenantConnection->select("
        SELECT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ?
        AND COLUMN_NAME = 'hospital_id'
        ORDER BY TABLE_NAME
    ", [$database]);
    
    if (count($tablesWithHospitalId) > 0) {
        echo "   ⚠️  ATTENTION : " . count($tablesWithHospitalId) . " table(s) avec colonne 'hospital_id' :\n";
        foreach ($tablesWithHospitalId as $table) {
            echo "      - {$table->TABLE_NAME}\n";
        }
    } else {
        echo "   ✅ Aucune colonne 'hospital_id' trouvée (correct)\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur : {$e->getMessage()}\n";
}
echo "\n";

// 4. Vérifier quelques tables importantes
echo "📊 4. Vérification de quelques tables importantes...\n";
$importantTables = ['users', 'patients', 'products', 'stocks', 'cash_registers'];
foreach ($importantTables as $table) {
    try {
        if ($tenantConnection->getSchemaBuilder()->hasTable($table)) {
            $count = $tenantConnection->table($table)->count();
            echo "   ✅ Table '{$table}' : {$count} enregistrement(s)\n";
        } else {
            echo "   ⚠️  Table '{$table}' n'existe pas\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Erreur pour '{$table}' : {$e->getMessage()}\n";
    }
}
echo "\n";

// 5. Test de requête simple
echo "🧪 5. Test de requête simple...\n";
try {
    $testQuery = $tenantConnection->table('users')->limit(1)->get();
    echo "   ✅ Requête test réussie\n";
    if ($testQuery->count() > 0) {
        echo "   📝 Exemple d'utilisateur : ID = {$testQuery->first()->id}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors du test : {$e->getMessage()}\n";
}
echo "\n";

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    VÉRIFICATION TERMINÉE                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
