<?php

/**
 * Script pour provisionner tous les hôpitaux qui n'ont pas encore de base de données
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Provisionnement de tous les hôpitaux                        ║\n";
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
    
    try {
        // Vérifier si la base existe
        $databaseExists = $tenantService->testConnection($hospital);
        
        if ($databaseExists) {
            echo "   ✅ Base de données existe déjà\n";
            
            // Vérifier les migrations
            $tenantService->connect($hospital);
            $tenantConnection = $tenantService->getCurrentConnection();
            $migrations = $tenantConnection->table('migrations')->count();
            echo "   📋 Migrations : {$migrations}\n";
            
            $results[] = [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'status' => 'exists',
                'migrations' => $migrations,
            ];
        } else {
            echo "   ⚠️  Base de données n'existe pas, création en cours...\n";
            
            // Créer la base de données
            $databaseName = $hospital->database_name;
            $charset = config('database.connections.mysql.charset', 'utf8mb4');
            $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
            
            try {
                DB::connection('mysql')->statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
                echo "   ✅ Base de données créée : {$databaseName}\n";
                
                // Mettre à jour le statut
                $hospital->update(['status' => 'provisioning']);
                
                // Exécuter les migrations
                echo "   📋 Exécution des migrations...\n";
                $tenantService->connect($hospital);
                
                // Migrations principales
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations',
                    '--force' => true,
                ]);
                
                // Migrations des modules
                $modulesPath = base_path('Modules');
                if (is_dir($modulesPath)) {
                    $modules = array_filter(glob($modulesPath . '/*'), 'is_dir');
                    foreach ($modules as $modulePath) {
                        $moduleName = basename($modulePath);
                        $migrationsPath = $modulePath . '/Database/Migrations';
                        
                        if (is_dir($migrationsPath)) {
                            try {
                                Artisan::call('migrate', [
                                    '--database' => 'tenant',
                                    '--path' => "Modules/{$moduleName}/Database/Migrations",
                                    '--force' => true,
                                ]);
                            } catch (\Exception $e) {
                                // Ignorer les erreurs de migrations déjà exécutées
                                if (strpos($e->getMessage(), 'already exists') === false) {
                                    echo "      ⚠️  Erreur module {$moduleName} : {$e->getMessage()}\n";
                                }
                            }
                        }
                    }
                }
                
                // Compter les migrations
                $tenantConnection = $tenantService->getCurrentConnection();
                $migrations = $tenantConnection->table('migrations')->count();
                echo "   ✅ Migrations exécutées : {$migrations}\n";
                
                // Mettre à jour le statut
                $hospital->update([
                    'status' => 'active',
                    'provisioned_at' => now(),
                ]);
                
                echo "   ✅ Hôpital provisionné avec succès !\n";
                
                $results[] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'status' => 'provisioned',
                    'migrations' => $migrations,
                ];
                
            } catch (\Exception $e) {
                echo "   ❌ Erreur lors de la création : {$e->getMessage()}\n";
                $results[] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Erreur : {$e->getMessage()}\n";
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }
    
    echo "\n";
}

// Résumé
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ                                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$provisionedCount = 0;
$existsCount = 0;
$errorCount = 0;

foreach ($results as $result) {
    if ($result['status'] === 'provisioned') {
        $provisionedCount++;
        echo "✅ {$result['hospital_name']} (ID: {$result['hospital_id']}) - Provisionné\n";
        echo "   - Migrations : {$result['migrations']}\n";
    } elseif ($result['status'] === 'exists') {
        $existsCount++;
        echo "ℹ️  {$result['hospital_name']} (ID: {$result['hospital_id']}) - Existe déjà\n";
        echo "   - Migrations : {$result['migrations']}\n";
    } else {
        $errorCount++;
        echo "❌ {$result['hospital_name']} (ID: {$result['hospital_id']}) - Erreur\n";
        echo "   - Erreur : {$result['error']}\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Statistiques :\n";
echo "   ✅ Provisionnés : {$provisionedCount}\n";
echo "   ℹ️  Existants : {$existsCount}\n";
echo "   ❌ Erreurs : {$errorCount}\n";
echo "   📊 Total : " . count($results) . "\n\n";

if ($errorCount === 0) {
    echo "✅ Tous les hôpitaux sont prêts !\n";
} else {
    echo "⚠️  Certains hôpitaux ont des erreurs.\n";
}

echo "\n";
