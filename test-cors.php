<?php
/**
 * Script de test CORS
 * 
 * Teste si les headers CORS sont correctement renvoyés
 * Usage: php test-cors.php
 */

$url = 'http://localhost:8000/api/v1/login';
$origin = 'http://hopital1.localhost:8080';

echo "=== TEST CORS ===\n\n";
echo "URL: {$url}\n";
echo "Origin: {$origin}\n\n";

// Test OPTIONS (preflight)
echo "1. Test requête OPTIONS (preflight):\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'OPTIONS',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        "Origin: {$origin}",
        "Access-Control-Request-Method: POST",
        "Access-Control-Request-Headers: Content-Type,Authorization"
    ],
    CURLOPT_VERBOSE => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "Code HTTP: {$httpCode}\n";
echo "Headers:\n{$headers}\n";

if (strpos($headers, 'Access-Control-Allow-Origin') !== false) {
    echo "✅ SUCCESS: Headers CORS présents!\n";
} else {
    echo "❌ ERREUR: Headers CORS ABSENTS!\n";
}

echo "\n";

// Test POST
echo "2. Test requête POST:\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        "Origin: {$origin}",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode(['email' => 'test', 'password' => 'test']),
    CURLOPT_VERBOSE => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);

curl_close($ch);

echo "Code HTTP: {$httpCode}\n";
echo "Headers:\n{$headers}\n";

if (strpos($headers, 'Access-Control-Allow-Origin') !== false) {
    echo "✅ SUCCESS: Headers CORS présents!\n";
} else {
    echo "❌ ERREUR: Headers CORS ABSENTS!\n";
}

echo "\n=== FIN DU TEST ===\n";
