<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Handle CORS Preflight Requests (for php built-in server)
|--------------------------------------------------------------------------
|
| Le serveur PHP intégré (php -S) ne gère pas bien les requêtes OPTIONS.
| On les traite ici directement avant même d'initialiser Laravel.
|
*/
// GESTION CORS ABSOLUE - AVANT TOUT
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Récupérer l'origine
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // TOUJOURS autoriser l'origine si elle existe
    if (!empty($origin)) {
        header("Access-Control-Allow-Origin: {$origin}");
    }
    
    // TOUJOURS répondre avec les headers CORS
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, Accept-Language, X-Requested-With, X-Hospital-Id, X-Original-Host, X-Tenant-Domain, Origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
    
    // Répondre immédiatement avec 200 OK
    http_response_code(200);
    exit(0);
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
