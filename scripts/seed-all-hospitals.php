<?php

/**
 * Script pour exécuter les seeders pour tous les hôpitaux
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\Artisan;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Exécution des seeders pour tous les hôpitaux               ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$hospitals = Hospital::whereIn('status', ['active', 'provisioning'])->get();

if ($hospitals->isEmpty()) {
    echo "❌ Aucun hôpital actif trouvé.\n";
    exit(1);
}

echo "📊 Nombre d'hôpitaux : {$hospitals->count()}\n\n";

$tenantService = app(TenantConnectionService::class);
$results = [];

// Ordre d'exécution des seeders (important pour les dépendances)
$seedersOrder = [
    // 1. Seeders principaux (DatabaseSeeder)
    'Database\\Seeders\\DatabaseSeeder',
    
    // 2. Modules dans l'ordre des dépendances
    'Modules\\Administration\\Database\\Seeders\\AdministrationDatabaseSeeder',
    'Modules\\Acl\\Database\\Seeders\\AclDatabaseSeeder',
    'Modules\\Stock\\Database\\Seeders\\StockDatabaseSeeder',
    'Modules\\Patient\\Database\\Seeders\\PatientDatabaseSeeder',
    'Modules\\Cash\\Database\\Seeders\\CashDatabaseSeeder',
    'Modules\\Hospitalization\\Database\\Seeders\\HospitalizationDatabaseSeeder',
    'Modules\\Movment\\Database\\Seeders\\MovmentDatabaseSeeder',
    'Modules\\Medicalservices\\Database\\Seeders\\MedicalservicesDatabaseSeeder',
    'Modules\\Absence\\Database\\Seeders\\AbsentTableSeeder',
    'Modules\\Annuaire\\Database\\Seeders\\AnnuaireDatabaseSeeder',
];

foreach ($hospitals as $hospital) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})\n";
    echo "   - Base de données : {$hospital->database_name}\n\n";
    
    try {
        $tenantService->connect($hospital);
        echo "   ✅ Connecté à la base de données tenant\n\n";
        
        $successCount = 0;
        $errorCount = 0;
        
        // Exécuter les seeders dans l'ordre
        foreach ($seedersOrder as $seederClass) {
            echo "   🌱 Exécution : {$seederClass}\n";
            
            try {
                Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
                
                $output = Artisan::output();
                if (strpos($output, 'error') === false && strpos($output, 'Error') === false) {
                    echo "      ✅ Succès\n";
                    $successCount++;
                } else {
                    echo "      ⚠️  Avertissements\n";
                    $successCount++;
                }
            } catch (\Exception $e) {
                // Ignorer certaines erreurs connues (doublons, etc.)
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                    strpos($e->getMessage(), 'already exists') !== false) {
                    echo "      ℹ️  Déjà existant (ignoré)\n";
                    $successCount++;
                } else {
                    echo "      ❌ Erreur : {$e->getMessage()}\n";
                    $errorCount++;
                }
            }
        }
        
        // Vérifier les données créées
        $tenantConnection = $tenantService->getCurrentConnection();
        
        $checks = [
            'users' => 'Utilisateurs',
            'patients' => 'Patients',
            'products' => 'Produits',
            'cash_registers' => 'Caisses',
            'rooms' => 'Chambres',
            'beds' => 'Lits',
        ];
        
        echo "\n   📊 Vérification des données créées :\n";
        foreach ($checks as $table => $label) {
            try {
                if ($tenantConnection->getSchemaBuilder()->hasTable($table)) {
                    $count = $tenantConnection->table($table)->count();
                    echo "      - {$label} : {$count}\n";
                }
            } catch (\Exception $e) {
                // Ignorer
            }
        }
        
        $results[] = [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'success' => $successCount,
            'errors' => $errorCount,
            'status' => $errorCount === 0 ? 'success' : 'warning',
        ];
        
        if ($errorCount === 0) {
            echo "\n   ✅ Seeders exécutés avec succès !\n";
        } else {
            echo "\n   ⚠️  Seeders exécutés avec {$errorCount} erreur(s)\n";
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

$successCount = 0;
$warningCount = 0;
$errorCount = 0;

foreach ($results as $result) {
    if ($result['status'] === 'success') {
        $successCount++;
        echo "✅ {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Seeders réussis : {$result['success']}\n";
    } elseif ($result['status'] === 'warning') {
        $warningCount++;
        echo "⚠️  {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Seeders réussis : {$result['success']}\n";
        echo "   - Erreurs : {$result['errors']}\n";
    } else {
        $errorCount++;
        echo "❌ {$result['hospital_name']} (ID: {$result['hospital_id']})\n";
        echo "   - Erreur : {$result['error']}\n";
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
    echo "✅ Tous les seeders ont été exécutés avec succès !\n";
} elseif ($errorCount === 0) {
    echo "⚠️  Tous les seeders ont été exécutés mais certains ont des avertissements.\n";
} else {
    echo "❌ Certains hôpitaux ont des erreurs.\n";
}

echo "\n";
