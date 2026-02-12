<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système multi-tenant database-per-tenant.
    | Chaque hôpital (tenant) dispose de sa propre base de données MySQL.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Core Database Connection
    |--------------------------------------------------------------------------
    |
    | Nom de la connexion à la base CORE qui contient les informations
    | sur les hôpitaux, modules, et administrateurs système.
    |
    */
    'core_connection' => env('CORE_DB_CONNECTION', 'core'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection
    |--------------------------------------------------------------------------
    |
    | Nom de la connexion dynamique aux bases de données des tenants.
    | Cette connexion sera configurée automatiquement par le middleware.
    |
    */
    'tenant_connection' => env('TENANT_DB_CONNECTION', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Default Database Prefix
    |--------------------------------------------------------------------------
    |
    | Préfixe par défaut pour les noms de bases de données des tenants.
    | Exemple: 'medkey_' -> bases nommées 'medkey_hospital_1', 'medkey_hospital_2', etc.
    |
    */
    'database_prefix' => env('TENANT_DB_PREFIX', 'medkey_'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Identification
    |--------------------------------------------------------------------------
    |
    | Méthodes d'identification du tenant (hôpital) :
    | - 'domain' : Par sous-domaine (ex: hopital1.medkey.com)
    | - 'header' : Par header HTTP (X-Tenant-Domain)
    | - 'subdomain' : Par sous-domaine uniquement
    |
    */
    'identification_method' => env('TENANT_IDENTIFICATION', 'domain'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Domain Pattern
    |--------------------------------------------------------------------------
    |
    | Pattern pour extraire le tenant depuis le domaine.
    | Exemple: '{tenant}.medkey.com' ou '{tenant}.localhost'
    |
    */
    'domain_pattern' => env('TENANT_DOMAIN_PATTERN', '{tenant}.medkey.com'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Header Name
    |--------------------------------------------------------------------------
    |
    | Nom du header HTTP utilisé pour identifier le tenant.
    |
    */
    'header_name' => env('TENANT_HEADER_NAME', 'X-Tenant-Domain'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration du cache pour les informations des tenants.
    |
    */
    'cache' => [
        'enabled' => env('TENANT_CACHE_ENABLED', true),
        'prefix' => env('TENANT_CACHE_PREFIX', 'tenant_'),
        'ttl' => env('TENANT_CACHE_TTL', 3600), // 1 heure
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les migrations des bases tenant.
    |
    */
    'migrations' => [
        'path' => database_path('tenant/migrations'),
        'table' => 'migrations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisioning Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le provisioning automatique des nouveaux tenants.
    |
    */
    'provisioning' => [
        'auto_migrate' => env('TENANT_AUTO_MIGRATE', true),
        'auto_seed' => env('TENANT_AUTO_SEED', false),
        'default_modules' => env('TENANT_DEFAULT_MODULES', 'Acl,Administration,Patient,Payment'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes exclues de la détection automatique du tenant.
    | Ces routes n'ont pas besoin d'un tenant (ex: health checks, admin global).
    |
    */
    'excluded_routes' => [
        'health',
        'api/health',
        'admin/*',
        'core/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection Pooling
    |--------------------------------------------------------------------------
    |
    | Configuration pour le pooling de connexions (performance).
    |
    */
    'connection_pooling' => [
        'enabled' => env('TENANT_CONNECTION_POOLING', false),
        'max_connections' => env('TENANT_MAX_CONNECTIONS', 10),
    ],

];
