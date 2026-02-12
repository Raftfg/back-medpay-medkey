<?php
/**
 * Router script for PHP built-in server
 * 
 * Usage: php -S 127.0.0.1:8000 .htrouter.php
 * 
 * Ce script force toutes les requêtes à passer par index.php
 * sauf pour les fichiers statiques existants (css, js, images, etc.)
 */

// Chemin du fichier demandé
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si un fichier existe physiquement (assets, images, etc.), le servir directement
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Laisser le serveur PHP servir le fichier directement
}

// Sinon, router vers index.php (Laravel)
require_once __DIR__ . '/index.php';
