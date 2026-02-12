# Analyse des Routes - Problème de Déconnexion

## Problème Identifié

Il y a **deux fichiers de routes** qui définissent des routes similaires :

1. **`routes/api.php`** - Routes principales de l'application
2. **`Modules/Acl/Routes/api.php`** - Routes du module ACL

## Structure Actuelle

### `routes/api.php`
- Le préfixe `api` est **commenté** (ligne 20)
- Mais le `RouteServiceProvider` ajoute automatiquement le préfixe `api` (ligne 41)
- Donc les routes sont accessibles à `/api/v1/...`
- La route `logout` est **commentée** (ligne 33) - déplacée dans le module ACL

### `Modules/Acl/Routes/api.php`
- Le préfixe `api` est **actif** (ligne 22)
- Les routes sont accessibles à `/api/v1/...`
- La route `logout` est **active** (ligne 31)

## Routes Enregistrées

D'après `php artisan route:list` :
- ✅ `POST api/v1/logout` existe (depuis `Modules/Acl/Routes/api.php`)
- ✅ `GET api/v1/user_current` existe (depuis `routes/api.php`)

## Solution Recommandée

### Option 1 : Centraliser toutes les routes d'authentification dans le module ACL (RECOMMANDÉ)

**Avantages :**
- Cohérence : toutes les routes d'authentification sont au même endroit
- Modularité : le module ACL gère toutes les routes liées à l'authentification
- Évite les conflits

**Actions :**
1. Déplacer `user_current` de `routes/api.php` vers `Modules/Acl/Routes/api.php`
2. Commenter ou supprimer les routes d'authentification dans `routes/api.php`

### Option 2 : Garder la séparation actuelle

**Avantages :**
- Séparation claire entre routes principales et routes de modules
- Flexibilité

**Actions :**
1. S'assurer que les routes ne se chevauchent pas
2. Documenter clairement quelle route est où

## Recommandation

**Option 1** est recommandée car :
- Le module ACL est responsable de l'authentification
- Toutes les routes d'authentification seront au même endroit
- Plus facile à maintenir

## Structure Proposée

### `Modules/Acl/Routes/api.php`
```php
Route::group(['prefix' => 'api'], function () {
    $apiVersion = 'v' . config('premier.api_version');
    
    Route::group(['prefix' => $apiVersion], function () {
        // Routes publiques
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [RegisterController::class, 'store']);
        Route::get('user_current', [AuthController::class, 'user']); // Déplacé depuis routes/api.php
        Route::get('email-confirmation/{uuid}', [AuthController::class, 'emailConfirmation']);
        Route::post('reset-password', [AuthController::class, 'reset']);
        Route::post('request-password', [AuthController::class, 'requestPassword']);
        
        // Routes authentifiées
        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            // ... autres routes authentifiées
        });
    });
});
```

### `routes/api.php`
```php
// Routes d'authentification déplacées dans Modules/Acl/Routes/api.php
// Ce fichier peut contenir d'autres routes globales si nécessaire
```
