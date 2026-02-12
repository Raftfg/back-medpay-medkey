<?php
/**
 * Test simple pour vérifier les headers CORS
 * 
 * Utilisation:
 * php -S localhost:8001 test-cors-simple.php
 * 
 * Puis dans le navigateur: http://localhost:8001
 */

header('Access-Control-Allow-Origin: http://hopital1.localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Tenant-Domain, X-Hospital-Id, X-Original-Host, Accept, Origin, Accept-Language');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'CORS test successful',
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'No origin header',
    'method' => $_SERVER['REQUEST_METHOD'],
]);
