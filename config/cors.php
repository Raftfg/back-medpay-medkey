<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'oauth/*', // Routes Passport
    ],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Origines autorisées - IMPORTANT: Ne pas utiliser '*' avec supports_credentials=true
    'allowed_origins' => [
        // Développement local
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://hopital1.localhost:8080',
        'http://hopital2.localhost:8080',
        'http://hopital3.localhost:8080',
        'http://hopital4.localhost:8080',
        // Production (à adapter selon vos domaines)
        'https://api-medkey.akasigroup.net',
    ],

    // Patterns regex pour les sous-domaines dynamiques (ex: hopital1.localhost:8080)
    'allowed_origins_patterns' => [
        '/^https?:\/\/(localhost|127\.0\.0\.1|.*\.localhost)(:\d+)?$/i',
        '/^https?:\/\/[a-zA-Z0-9\-]+\.ma-plateforme\.com(:\d+)?$/',
        '/^https?:\/\/[a-zA-Z0-9\-]+\.akasigroup\.net(:\d+)?$/',
    ],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'Accept-Language',
        'X-Requested-With',
        'X-Hospital-Id',
        'X-Original-Host',
        'X-Tenant-Domain',
        'Origin',
    ],

    'exposed_headers' => ['Authorization', 'Content-Type'],

    'max_age' => 86400,

    // Credentials = true permet l'envoi de cookies/tokens d'authentification
    // IMPORTANT: requires explicit origins, not '*'
    'supports_credentials' => true,
];
