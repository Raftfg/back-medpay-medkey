<?php
$defaultFrontUrl = 'app.sigal.local';

return [
    'passport' => [
        'pwd_grant_name' => env('PWD_GRANT_NAME')
    ],
    'sms' => [],
    'mail' => [
        'erreur' => env('MAIL_ERREUR', 'ekpotin@gmail.com'),
        'admin' => env('MAIL_ADMIN', 'ekpotin@gmail.com'),
        'test' => env('MAIL_TEST', 'ekpotin@gmail.com'),
    ],
    'db' => [
        'table' => [
            'prefixe' => env('PREFIXE_TABLE', 'al_'),
        ],
    ],
    'frontend' => [
        'url' => [
            'racine' => env('FRONTEND_URL', $defaultFrontUrl),
            'validation_email' => env('FRONTEND_URL', $defaultFrontUrl) . '/auth/email-confirmation',
            'forgot_password' => env('FRONTEND_URL', $defaultFrontUrl) . '/auth/reset-password',
        ],
        'temps' => [
            'inactivite' => env('FRONTEND_TEMPS_INACTIVITE', 25),
            'prompt' => env('FRONTEND_TEMPS_PROMPT', 5),
        ],
    ],
    'seed_test' => env('SEED_TEST', 0),
    'api_version' => env('API_VERSION', 1),
    'duree_url_temporaire' => env('DUREE_URL_TEMPORAIRE', 1440),
    'nombre_pagination' => env('NOMBRE_PAGINATION', 20),
];
