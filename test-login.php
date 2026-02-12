<?php

/**
 * Script de test pour diagnostiquer les problèmes de connexion
 * 
 * Usage: php test-login.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTIC DE CONNEXION ===\n\n";

// Test 1: Vérifier la connexion à la base de données
echo "1. Test de connexion à la base de données...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Connexion à la base de données OK\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Vérifier qu'il y a des utilisateurs
echo "\n2. Vérification des utilisateurs...\n";
$userCount = User::withoutGlobalScopes()->count();
echo "   Nombre d'utilisateurs: $userCount\n";

if ($userCount === 0) {
    echo "   ⚠️  Aucun utilisateur trouvé dans la base de données\n";
    echo "   💡 Créez un utilisateur avec:\n";
    echo "      php artisan tinker\n";
    echo "      \$user = new \\Modules\\Acl\\Entities\\User();\n";
    echo "      \$user->email = 'test@exemple.com';\n";
    echo "      \$user->password = \\Hash::make('password123');\n";
    echo "      \$user->name = 'Test User';\n";
    echo "      \$user->hospital_id = 1;\n";
    echo "      \$user->is_active = true;\n";
    echo "      \$user->save();\n";
} else {
    echo "   ✅ Utilisateurs trouvés\n";
    
    // Test 3: Lister les utilisateurs
    echo "\n3. Liste des utilisateurs (5 premiers):\n";
    $users = User::withoutGlobalScopes()->take(5)->get(['id', 'email', 'name', 'hospital_id']);
    foreach ($users as $user) {
        echo "   - ID: {$user->id}, Email: {$user->email}, Hospital ID: " . ($user->hospital_id ?? 'NULL') . "\n";
    }
}

// Test 4: Vérifier les utilisateurs sans hospital_id
echo "\n4. Vérification des utilisateurs sans hospital_id...\n";
$usersWithoutHospital = User::withoutGlobalScopes()->whereNull('hospital_id')->count();
if ($usersWithoutHospital > 0) {
    echo "   ⚠️  $usersWithoutHospital utilisateur(s) sans hospital_id\n";
    echo "   💡 Ces utilisateurs ne pourront pas se connecter\n";
} else {
    echo "   ✅ Tous les utilisateurs ont un hospital_id\n";
}

// Test 5: Test de connexion avec un utilisateur
echo "\n5. Test de connexion (si un utilisateur existe)...\n";
$testUser = User::withoutGlobalScopes()->whereNotNull('hospital_id')->first();

if ($testUser) {
    echo "   Utilisateur de test: {$testUser->email}\n";
    echo "   Hospital ID: {$testUser->hospital_id}\n";
    
    // Vérifier l'hôpital
    try {
        $hospital = $testUser->hospital;
        if ($hospital) {
            echo "   Hôpital: {$hospital->name}\n";
            echo "   Statut: {$hospital->status}\n";
            echo "   Actif: " . ($hospital->isActive() ? 'Oui' : 'Non') . "\n";
        } else {
            echo "   ⚠️  Hôpital non trouvé (hospital_id: {$testUser->hospital_id})\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠️  Erreur lors de la récupération de l'hôpital: " . $e->getMessage() . "\n";
    }
    
    // Test du mot de passe (si vous voulez tester)
    echo "\n   💡 Pour tester le mot de passe, utilisez:\n";
    echo "      php artisan tinker\n";
    echo "      \$user = \\Modules\\Acl\\Entities\\User::find({$testUser->id});\n";
    echo "      \\Hash::check('votre-mot-de-passe', \$user->password);\n";
} else {
    echo "   ⚠️  Aucun utilisateur avec hospital_id trouvé\n";
}

// Test 6: Vérifier la fonction currentHospitalId
echo "\n6. Test de la fonction currentHospitalId()...\n";
if (function_exists('currentHospitalId')) {
    $hospitalId = currentHospitalId();
    echo "   Hospital ID courant: " . ($hospitalId ?? 'NULL') . "\n";
    if ($hospitalId === null) {
        echo "   ℹ️  C'est normal si aucun tenant n'est défini (route de login exclue)\n";
    }
} else {
    echo "   ⚠️  La fonction currentHospitalId() n'existe pas\n";
}

// Test 7: Vérifier les routes
echo "\n7. Vérification de la route de login...\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $loginRoute = $routes->getByAction('Modules\Acl\Http\Controllers\Api\V1\AuthController@login');
    if ($loginRoute) {
        echo "   ✅ Route de login trouvée\n";
        echo "   URI: " . $loginRoute->uri() . "\n";
        echo "   Méthode: " . implode('|', $loginRoute->methods()) . "\n";
    } else {
        echo "   ⚠️  Route de login non trouvée\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Erreur lors de la vérification des routes: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
echo "\n💡 Pour tester la connexion avec curl:\n";
echo "   curl -X POST http://localhost:8000/api/v1/login \\\n";
echo "     -H \"Content-Type: application/json\" \\\n";
echo "     -H \"Origin: http://localhost:8080\" \\\n";
echo "     -d '{\"email\":\"votre-email@exemple.com\",\"password\":\"votre-mot-de-passe\"}'\n";
