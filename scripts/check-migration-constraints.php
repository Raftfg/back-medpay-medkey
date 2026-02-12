<?php

/**
 * Script pour vérifier en détail les contraintes et identifier les problèmes potentiels
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;

$hospitalId = $argv[1] ?? 1;

$hospital = Hospital::find($hospitalId);
if (!$hospital) {
    echo "❌ Hôpital introuvable\n";
    exit(1);
}

$tenantService = app(TenantConnectionService::class);
$tenantService->connect($hospital);
$tenantConnection = $tenantService->getCurrentConnection();
$database = $tenantConnection->getDatabaseName();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Vérification détaillée des contraintes                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Récupérer toutes les contraintes
$constraints = $tenantConnection->select("
    SELECT 
        kcu.TABLE_NAME,
        kcu.CONSTRAINT_NAME,
        kcu.COLUMN_NAME,
        kcu.REFERENCED_TABLE_NAME,
        kcu.REFERENCED_COLUMN_NAME,
        rc.UPDATE_RULE,
        rc.DELETE_RULE
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
        ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
        AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
    WHERE kcu.TABLE_SCHEMA = ?
    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME
", [$database]);

echo "📊 Total de contraintes de clés étrangères : " . count($constraints) . "\n\n";

// Grouper par table
$constraintsByTable = [];
foreach ($constraints as $constraint) {
    $table = $constraint->TABLE_NAME;
    if (!isset($constraintsByTable[$table])) {
        $constraintsByTable[$table] = [];
    }
    $constraintsByTable[$table][] = $constraint;
}

// Vérifier chaque contrainte
$errors = [];
$warnings = [];

foreach ($constraintsByTable as $table => $tableConstraints) {
    // Vérifier si la table existe
    $tableExists = $tenantConnection->select("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = ?
    ", [$database, $table]);
    
    if ($tableExists[0]->count == 0) {
        $errors[] = "Table '{$table}' n'existe pas mais a des contraintes référencées";
        continue;
    }
    
    foreach ($tableConstraints as $constraint) {
        $refTable = $constraint->REFERENCED_TABLE_NAME;
        
        // Vérifier si la table référencée existe
        $refTableExists = $tenantConnection->select("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ?
        ", [$database, $refTable]);
        
        if ($refTableExists[0]->count == 0) {
            $errors[] = "Table '{$table}' référence '{$refTable}' qui n'existe pas (contrainte: {$constraint->CONSTRAINT_NAME})";
        }
        
        // Vérifier si la colonne référencée existe
        $refColumnExists = $tenantConnection->select("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ", [$database, $refTable, $constraint->REFERENCED_COLUMN_NAME]);
        
        if ($refColumnExists[0]->count == 0 && $refTableExists[0]->count > 0) {
            $errors[] = "Table '{$table}' référence la colonne '{$refTable}.{$constraint->REFERENCED_COLUMN_NAME}' qui n'existe pas";
        }
    }
}

// Afficher les résultats
if (empty($errors) && empty($warnings)) {
    echo "✅ Toutes les contraintes sont valides !\n\n";
    
    // Afficher un résumé par table
    echo "📋 Résumé par table (premières 10) :\n";
    $count = 0;
    foreach ($constraintsByTable as $table => $tableConstraints) {
        if ($count++ >= 10) break;
        echo "   - {$table} : " . count($tableConstraints) . " contrainte(s)\n";
    }
    if (count($constraintsByTable) > 10) {
        echo "   ... et " . (count($constraintsByTable) - 10) . " autre(s) table(s)\n";
    }
} else {
    if (!empty($errors)) {
        echo "❌ ERREURS DÉTECTÉES :\n";
        foreach ($errors as $error) {
            echo "   - {$error}\n";
        }
        echo "\n";
    }
    
    if (!empty($warnings)) {
        echo "⚠️  AVERTISSEMENTS :\n";
        foreach ($warnings as $warning) {
            echo "   - {$warning}\n";
        }
        echo "\n";
    }
}

echo "\n";
