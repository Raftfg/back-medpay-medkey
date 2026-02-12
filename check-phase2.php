<?php

/**
 * Script de vérification de la Phase 2
 * 
 * Vérifie que tous les composants de la Phase 2 sont opérationnels
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     VÉRIFICATION PHASE 2 - ADAPTATION DU MIDDLEWARE          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$success = [];

// ============================================
// 1. VÉRIFICATION DES FICHIERS
// ============================================
echo "📁 1. Vérification des fichiers...\n";

$requiredFiles = [
    'app/Http/Middleware/TenantMiddleware.php',
    'app/Http/Middleware/EnsureTenantConnection.php',
];

foreach ($requiredFiles as $file) {
    if (File::exists(base_path($file))) {
        $success[] = "✅ Fichier existe: $file";
    } else {
        $errors[] = "❌ Fichier manquant: $file";
    }
}

// ============================================
// 2. VÉRIFICATION DU TENANTMIDDLEWARE
// ============================================
echo "\n🔧 2. Vérification du TenantMiddleware...\n";

$tenantMiddlewareContent = File::get(base_path('app/Http/Middleware/TenantMiddleware.php'));

// Vérifier l'utilisation du modèle CORE
if (strpos($tenantMiddlewareContent, 'App\\Core\\Models\\Hospital') !== false) {
    $success[] = "✅ Utilise App\\Core\\Models\\Hospital";
} else {
    $errors[] = "❌ N'utilise pas App\\Core\\Models\\Hospital";
}

// Vérifier l'utilisation de TenantConnectionService
if (strpos($tenantMiddlewareContent, 'TenantConnectionService') !== false) {
    $success[] = "✅ Utilise TenantConnectionService";
} else {
    $errors[] = "❌ N'utilise pas TenantConnectionService";
}

// Vérifier la méthode handleConnectionError
if (strpos($tenantMiddlewareContent, 'handleConnectionError') !== false) {
    $success[] = "✅ Méthode handleConnectionError() présente";
} else {
    $errors[] = "❌ Méthode handleConnectionError() manquante";
}

// ============================================
// 3. VÉRIFICATION DU MIDDLEWARE ENSURE TENANT CONNECTION
// ============================================
echo "\n🛡️  3. Vérification du middleware EnsureTenantConnection...\n";

$ensureTenantContent = File::get(base_path('app/Http/Middleware/EnsureTenantConnection.php'));

// Vérifier la classe
if (strpos($ensureTenantContent, 'class EnsureTenantConnection') !== false) {
    $success[] = "✅ Classe EnsureTenantConnection existe";
} else {
    $errors[] = "❌ Classe EnsureTenantConnection manquante";
}

// Vérifier l'utilisation de TenantConnectionService
if (strpos($ensureTenantContent, 'TenantConnectionService') !== false) {
    $success[] = "✅ Utilise TenantConnectionService";
} else {
    $errors[] = "❌ N'utilise pas TenantConnectionService";
}

// Vérifier la vérification de connexion
if (strpos($ensureTenantContent, 'isConnected') !== false) {
    $success[] = "✅ Vérifie isConnected()";
} else {
    $errors[] = "❌ Ne vérifie pas isConnected()";
}

// ============================================
// 4. VÉRIFICATION DU KERNEL
// ============================================
echo "\n⚙️  4. Vérification du Kernel...\n";

$kernelContent = File::get(base_path('app/Http/Kernel.php'));

// Vérifier que EnsureTenantConnection est dans le groupe api
if (strpos($kernelContent, 'EnsureTenantConnection') !== false) {
    $success[] = "✅ EnsureTenantConnection enregistré dans Kernel";
    
    // Vérifier l'ordre (doit être après TenantMiddleware)
    $tenantMiddlewarePos = strpos($kernelContent, 'TenantMiddleware');
    $ensureTenantPos = strpos($kernelContent, 'EnsureTenantConnection');
    
    if ($tenantMiddlewarePos !== false && $ensureTenantPos !== false && $ensureTenantPos > $tenantMiddlewarePos) {
        $success[] = "✅ Ordre correct : TenantMiddleware avant EnsureTenantConnection";
    } else {
        $warnings[] = "⚠️  Vérifiez l'ordre des middlewares dans Kernel.php";
    }
} else {
    $errors[] = "❌ EnsureTenantConnection non enregistré dans Kernel";
}

// ============================================
// 5. VÉRIFICATION DES SERVICES
// ============================================
echo "\n🔌 5. Vérification des services...\n";

try {
    $service = app(\App\Core\Services\TenantConnectionService::class);
    $success[] = "✅ TenantConnectionService accessible";
    
    // Vérifier les méthodes
    $methods = ['connect', 'disconnect', 'getCurrentConnection', 'isConnected'];
    foreach ($methods as $method) {
        if (method_exists($service, $method)) {
            $success[] = "✅ Méthode $method() disponible";
        } else {
            $errors[] = "❌ Méthode $method() manquante";
        }
    }
} catch (\Exception $e) {
    $errors[] = "❌ Erreur lors du chargement du service: " . $e->getMessage();
}

// ============================================
// 6. VÉRIFICATION DES MODÈLES
// ============================================
echo "\n📦 6. Vérification des modèles...\n";

try {
    $hospital = new \App\Core\Models\Hospital();
    $success[] = "✅ Modèle Hospital CORE chargé";
    
    // Vérifier la connexion
    if ($hospital->getConnectionName() === 'core') {
        $success[] = "✅ Hospital utilise la connexion 'core'";
    } else {
        $warnings[] = "⚠️  Hospital n'utilise pas la connexion 'core'";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Erreur lors du chargement du modèle Hospital: " . $e->getMessage();
}

// ============================================
// RÉSUMÉ
// ============================================
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ                                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if (count($success) > 0) {
    echo "✅ SUCCÈS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

// Conclusion
echo "╔══════════════════════════════════════════════════════════════╗\n";
if (count($errors) === 0) {
    if (count($warnings) === 0) {
        echo "║  ✅ PHASE 2 : OPÉRATIONNELLE - Prêt pour la Phase 3        ║\n";
    } else {
        echo "║  ⚠️  PHASE 2 : FONCTIONNELLE avec avertissements          ║\n";
        echo "║     Vérifiez les avertissements ci-dessus                   ║\n";
    }
} else {
    echo "║  ❌ PHASE 2 : NON OPÉRATIONNELLE                            ║\n";
    echo "║     Corrigez les erreurs avant de continuer                   ║\n";
}
echo "╚══════════════════════════════════════════════════════════════╝\n";

exit(count($errors) > 0 ? 1 : 0);
