<?php

/**
 * Script pour exécuter les migrations DME pour tous les tenants
 * 
 * Usage: php run-dme-migrations.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantProvisioningService;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Exécution des migrations DME pour TOUS les tenants        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$tenantService = app(TenantProvisioningService::class);
$hospitals = Hospital::where('status', 'active')->get();

if ($hospitals->isEmpty()) {
    echo "❌ Aucun hôpital actif trouvé.\n";
    exit(1);
}

echo "📋 Hôpitaux à migrer: " . $hospitals->count() . "\n\n";

$migrations = [
    'Modules/Movment/Database/Migrations/2026_01_25_000001_create_vaccinations_table.php',
    'Modules/Movment/Database/Migrations/2026_01_25_000002_create_prescriptions_table.php',
    'Modules/Movment/Database/Migrations/2026_01_25_000003_create_prescription_items_table.php',
    'Modules/Movment/Database/Migrations/2026_01_25_000004_create_dme_documents_table.php',
];

$results = [];

foreach ($hospitals as $hospital) {
    echo "🏥 Traitement de: {$hospital->name} (ID: {$hospital->id})\n";
    echo "   Base de données: {$hospital->database_name}\n";
    
    try {
        foreach ($migrations as $migration) {
            echo "   → Migration: " . basename($migration) . "\n";
            $tenantService->runMigrations($hospital, $migration);
        }
        
        echo "   ✅ Succès\n\n";
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'database_name' => $hospital->database_name,
            'status' => 'success',
            'message' => 'Migrations exécutées avec succès'
        ];
    } catch (\Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'database_name' => $hospital->database_name,
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Résumé                                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$errorCount = count(array_filter($results, fn($r) => $r['status'] === 'error'));

echo "✅ Succès: {$successCount}\n";
echo "❌ Erreurs: {$errorCount}\n\n";

foreach ($results as $result) {
    $icon = $result['status'] === 'success' ? '✅' : '❌';
    echo "{$icon} {$result['hospital_name']} ({$result['database_name']})\n";
    if ($result['status'] === 'error') {
        echo "   Erreur: {$result['message']}\n";
    }
}

echo "\n✨ Terminé!\n";
