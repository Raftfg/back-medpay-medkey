<?php

/**
 * Script pour exécuter les seeders adaptés à l'architecture database-per-tenant
 * Les seeders sont exécutés sans hospital_id car on est déjà dans la base tenant
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital as CoreHospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Exécution des seeders (architecture database-per-tenant)   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$hospitals = CoreHospital::whereIn('status', ['active', 'provisioning'])->get();

if ($hospitals->isEmpty()) {
    echo "❌ Aucun hôpital actif trouvé.\n";
    exit(1);
}

echo "📊 Nombre d'hôpitaux : {$hospitals->count()}\n\n";

$tenantService = app(TenantConnectionService::class);

// Ordre d'exécution des seeders (selon les dépendances)
$seedersOrder = [
    // 1. ACL (utilisateurs, rôles, permissions) - en premier
    ['module' => 'Acl', 'seeders' => [
        'PermissionTableSeeder',
        'RoleTableSeeder',
        'RolesAndPermissionsSeeder',
        'UserTableSeeder',
    ]],
    
    // 2. Administration (départements, services, etc.)
    ['module' => 'Administration', 'seeders' => [
        'InsuranceTableSeeder',
        'ProductTypeTableSeeder',
        'PackTableSeeder',
        'DepartmentTableSeeder',
        'ServiceTableSeeder',
        'TypeMedicalActsTableSeeder',
        'MedicalActTableSeeder',
        'HospitalSettingTableSeeder',
    ]],
    
    // 3. Stock (types, unités, catégories, etc.)
    ['module' => 'Stock', 'seeders' => [
        'TypeProductTableSeeder',
        'SaleUnitTableSeeder',
        'ConditioningUnitTableSeeder',
        'AdministrationRouteTableSeeder',
        'CategoryTableSeeder',
        'SupplierTableSeeder',
        'StoreTableSeeder',
        'StockTableSeeder',
        'ProductTableSeeder',
    ]],
    
    // 4. Patient
    ['module' => 'Patient', 'seeders' => [
        'PatientTableSeeder',
    ]],
    
    // 5. Cash
    ['module' => 'Cash', 'seeders' => [
        'CashRegisterTableSeeder',
    ]],
    
    // 6. Hospitalization
    ['module' => 'Hospitalization', 'seeders' => [
        'RoomTableSeeder',
        'BedTableSeeder',
    ]],
    
    // 7. Movment
    ['module' => 'Movment', 'seeders' => [
        'MovmentTableSeeder',
    ]],
    
    // 8. Medicalservices
    ['module' => 'Medicalservices', 'seeders' => [
        'ConsultationRecordTableSeeder',
    ]],
    
    // 9. Absence
    ['module' => 'Absence', 'seeders' => [
        'TypeVacationSeeder',
        'AbsentTableSeeder',
    ]],
    
    // 10. Annuaire
    ['module' => 'Annuaire', 'seeders' => [
        'EmployerTableSeeder',
    ]],
];

foreach ($hospitals as $hospital) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})\n";
    echo "   - Base de données : {$hospital->database_name}\n\n";
    
    try {
        $tenantService->connect($hospital);
        $tenantConnection = $tenantService->getCurrentConnection();
        
        echo "   ✅ Connecté à la base de données\n";
        
        // Vérifier les données existantes
        $usersCount = $tenantConnection->table('users')->count();
        $patientsCount = $tenantConnection->table('patients')->count();
        $categoriesCount = $tenantConnection->table('categories')->count();
        
        echo "   📊 Données existantes :\n";
        echo "      - Utilisateurs : {$usersCount}\n";
        echo "      - Patients : {$patientsCount}\n";
        echo "      - Catégories : {$categoriesCount}\n\n";
        
        // Exécuter les seeders dans l'ordre
        $totalExecuted = 0;
        $totalSkipped = 0;
        
        foreach ($seedersOrder as $moduleConfig) {
            $moduleName = $moduleConfig['module'];
            echo "   📦 Module {$moduleName} :\n";
            
            foreach ($moduleConfig['seeders'] as $seederClass) {
                $seederNamespace = "Modules\\{$moduleName}\\Database\\Seeders\\{$seederClass}";
                
                // Vérifier si la classe existe
                if (!class_exists($seederNamespace)) {
                    echo "      ⏭️  {$seederClass} (classe non trouvée)\n";
                    $totalSkipped++;
                    continue;
                }
                
                try {
                    // Exécuter le seeder
                    Artisan::call('db:seed', [
                        '--database' => 'tenant',
                        '--class' => $seederNamespace,
                        '--force' => true,
                    ]);
                    
                    echo "      ✅ {$seederClass}\n";
                    $totalExecuted++;
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    
                    // Ignorer les erreurs de données déjà existantes
                    if (strpos($errorMsg, 'Duplicate entry') !== false || 
                        strpos($errorMsg, 'already exists') !== false) {
                        echo "      ℹ️  {$seederClass} (données déjà existantes)\n";
                        $totalSkipped++;
                    } elseif (strpos($errorMsg, 'hospitals') !== false) {
                        // Erreur liée à la table hospitals - le seeder doit être adapté
                        echo "      ⚠️  {$seederClass} (nécessite adaptation pour database-per-tenant)\n";
                        $totalSkipped++;
                    } else {
                        echo "      ❌ {$seederClass} : {$errorMsg}\n";
                    }
                }
            }
        }
        
        // Vérifier les données après seeding
        $usersCountAfter = $tenantConnection->table('users')->count();
        $patientsCountAfter = $tenantConnection->table('patients')->count();
        $categoriesCountAfter = $tenantConnection->table('categories')->count();
        
        echo "\n   📊 Données après seeding :\n";
        echo "      - Utilisateurs : {$usersCountAfter} (" . ($usersCountAfter - $usersCount) . " ajouté(s))\n";
        echo "      - Patients : {$patientsCountAfter} (" . ($patientsCountAfter - $patientsCount) . " ajouté(s))\n";
        echo "      - Catégories : {$categoriesCountAfter} (" . ($categoriesCountAfter - $categoriesCount) . " ajouté(s))\n";
        echo "   ✅ {$totalExecuted} seeder(s) exécuté(s), {$totalSkipped} ignoré(s)\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Erreur : {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    TERMINÉ                                   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";
