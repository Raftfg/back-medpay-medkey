<?php
/**
 * Script de Diagnostic de Connexion
 * 
 * Vérifie que le serveur Laravel est correctement configuré et accessible
 * 
 * Usage: php scripts/diagnose-connection.php
 */

echo "🔍 DIAGNOSTIC DE CONNEXION LARAVEL API\n";
echo str_repeat("=", 50) . "\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Vérifier que le serveur écoute sur le port 8000
echo "1️⃣  Vérification du port 8000...\n";
$port = 8000;
$connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 2);

if ($connection) {
    fclose($connection);
    $success[] = "✅ Port 8000 accessible";
    echo "   ✅ Port 8000 est accessible\n\n";
} else {
    $errors[] = "Port 8000 non accessible";
    echo "   ❌ Port 8000 n'est PAS accessible\n";
    echo "   💡 Solution: Démarrer le serveur avec 'php artisan serve'\n\n";
}

// 2. Tester une requête HTTP vers l'API
if ($connection) {
    echo "2️⃣  Test de requête HTTP vers l'API...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/v1/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        $errors[] = "Erreur CURL: $curlError";
        echo "   ❌ Erreur lors de la requête: $curlError\n\n";
    } elseif ($httpCode > 0) {
        $success[] = "API répond avec code HTTP $httpCode";
        echo "   ✅ API répond (Code HTTP: $httpCode)\n";
        
        if ($httpCode === 404) {
            $warnings[] = "Route /api/v1/health non trouvée (normal si route n'existe pas)";
            echo "   ⚠️  Route /api/v1/health non trouvée (peut être normal)\n";
        }
        echo "\n";
    } else {
        $errors[] = "Aucune réponse HTTP";
        echo "   ❌ Aucune réponse HTTP reçue\n\n";
    }
}

// 3. Tester une requête OPTIONS (CORS preflight)
if ($connection) {
    echo "3️⃣  Test de requête CORS (OPTIONS)...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/v1/patients");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Origin: http://hopital1.localhost:8080",
        "Access-Control-Request-Method: GET",
        "Access-Control-Request-Headers: Content-Type,Authorization"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 204) {
        $success[] = "CORS preflight fonctionne";
        echo "   ✅ CORS preflight fonctionne (Code: $httpCode)\n\n";
    } else {
        $warnings[] = "CORS preflight retourne code $httpCode";
        echo "   ⚠️  CORS preflight retourne code $httpCode\n\n";
    }
}

// 4. Vérifier les routes
echo "4️⃣  Vérification des routes API...\n";
if (file_exists(__DIR__ . '/../artisan')) {
    $routes = shell_exec('cd ' . __DIR__ . '/.. && php artisan route:list --path=patients 2>&1');
    
    if (strpos($routes, 'patients') !== false) {
        $success[] = "Route /api/v1/patients existe";
        echo "   ✅ Route /api/v1/patients existe\n\n";
    } else {
        $warnings[] = "Route /api/v1/patients non trouvée";
        echo "   ⚠️  Route /api/v1/patients non trouvée dans la liste\n";
        echo "   💡 Vérifier avec: php artisan route:list --path=patients\n\n";
    }
} else {
    $warnings[] = "Fichier artisan non trouvé";
    echo "   ⚠️  Fichier artisan non trouvé\n\n";
}

// 5. Vérifier la configuration CORS
echo "5️⃣  Vérification de la configuration CORS...\n";
$corsFile = __DIR__ . '/../app/Http/Middleware/HandleCors.php';
if (file_exists($corsFile)) {
    $corsContent = file_get_contents($corsFile);
    if (strpos($corsContent, 'hopital1.localhost:8080') !== false) {
        $success[] = "CORS configuré pour hopital1.localhost:8080";
        echo "   ✅ CORS configuré pour hopital1.localhost:8080\n\n";
    } else {
        $warnings[] = "hopital1.localhost:8080 non trouvé dans HandleCors.php";
        echo "   ⚠️  hopital1.localhost:8080 non trouvé dans HandleCors.php\n\n";
    }
} else {
    $errors[] = "HandleCors.php non trouvé";
    echo "   ❌ HandleCors.php non trouvé\n\n";
}

// Résumé
echo str_repeat("=", 50) . "\n";
echo "📊 RÉSUMÉ DU DIAGNOSTIC\n";
echo str_repeat("=", 50) . "\n\n";

if (count($success) > 0) {
    echo "✅ SUCCÈS:\n";
    foreach ($success as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  AVERTISSEMENTS:\n";
    foreach ($warnings as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERREURS:\n";
    foreach ($errors as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
    echo "🚀 ACTION REQUISE:\n";
    echo "   1. Démarrer le serveur Laravel:\n";
    echo "      cd back-medpay\n";
    echo "      php artisan serve\n";
    echo "\n";
    echo "   2. Vérifier que le serveur répond:\n";
    echo "      curl http://localhost:8000/api/v1/health\n";
    echo "\n";
    exit(1);
} else {
    echo "✅ Tous les tests sont passés avec succès!\n";
    echo "\n";
    echo "💡 Si vous avez toujours des erreurs dans le navigateur:\n";
    echo "   1. Vérifier la console du navigateur (F12)\n";
    echo "   2. Vérifier les logs Laravel: tail -f storage/logs/laravel.log\n";
    echo "   3. Vérifier que le token d'authentification est valide\n";
    exit(0);
}
