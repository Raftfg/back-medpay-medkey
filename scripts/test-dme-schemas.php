<?php
/**
 * Script de test pour valider les schémas DME
 * 
 * Usage: php scripts/test-dme-schemas.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Test de Validation des Schémas DME                        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "Étape 1: Validation des schémas...\n";
echo str_repeat("━", 60) . "\n";

try {
    Artisan::call('tenant:schema-validate', [
        '--detailed' => true,
    ]);
    echo Artisan::output();
    echo "\n✅ Validation terminée\n\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de la validation: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Étape 2: Synchronisation en mode simulation...\n";
echo str_repeat("━", 60) . "\n";

try {
    Artisan::call('tenant:schema-sync', [
        '--dry-run' => true,
    ]);
    echo Artisan::output();
    echo "\n✅ Simulation terminée\n\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de la simulation: " . $e->getMessage() . "\n";
    exit(1);
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Tests terminés avec succès                                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
