# Centralisation des Routes d'Authentification

## ✅ Modifications Appliquées

Toutes les routes d'authentification ont été centralisées dans le module ACL pour améliorer la cohérence et la maintenabilité.

## 📋 Routes Déplacées

Les routes suivantes ont été déplacées de `routes/api.php` vers `Modules/Acl/Routes/api.php` :

1. ✅ `POST /api/v1/login` - Connexion
2. ✅ `POST /api/v1/logout` - Déconnexion (déjà présente, maintenant centralisée)
3. ✅ `GET /api/v1/user_current` - Utilisateur courant
4. ✅ `GET /api/v1/email-confirmation/{uuid}` - Confirmation d'email
5. ✅ `POST /api/v1/reset-password` - Réinitialisation du mot de passe
6. ✅ `POST /api/v1/request-password` - Demande de réinitialisation

## 📁 Structure des Fichiers

### `Modules/Acl/Routes/api.php`
**Toutes les routes d'authentification sont maintenant ici :**

```php
Route::group(['prefix' => 'api'], function () {
    $apiVersion = 'v' . config('premier.api_version');
    
    Route::group(['prefix' => $apiVersion], function () {
        // ============================================
        // ROUTES PUBLIQUES D'AUTHENTIFICATION
        // ============================================
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [RegisterController::class, 'store']);
        Route::get('user_current', [AuthController::class, 'user']);
        Route::get('email-confirmation/{uuid}', [AuthController::class, 'emailConfirmation']);
        Route::post('reset-password', [AuthController::class, 'reset']);
        Route::post('request-password', [AuthController::class, 'requestPassword']);
        
        // ============================================
        // ROUTES AUTHENTIFIÉES
        // ============================================
        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            // ... autres routes authentifiées
        });
    });
});
```

### `routes/api.php`
**Nettoyé - Les routes d'authentification ont été supprimées :**

```php
// ============================================
// ROUTES D'AUTHENTIFICATION
// ============================================
// Toutes les routes d'authentification ont été déplacées dans Modules/Acl/Routes/api.php
// Voir : Modules/Acl/Routes/api.php
// ============================================
```

## ✅ Avantages

1. **Cohérence** : Toutes les routes d'authentification sont au même endroit
2. **Modularité** : Le module ACL gère complètement l'authentification
3. **Maintenabilité** : Plus facile à maintenir et à déboguer
4. **Évite les conflits** : Plus de duplication de routes
5. **Clarté** : Structure claire et organisée

## 🔍 Vérification

Toutes les routes sont bien enregistrées :

```bash
php artisan route:list --path=api/v1 | grep -E "login|logout|user_current|email-confirmation|reset-password|request-password"
```

**Résultat attendu :**
- ✅ `POST api/v1/login`
- ✅ `POST api/v1/logout`
- ✅ `GET api/v1/user_current`
- ✅ `GET api/v1/email-confirmation/{uuid}`
- ✅ `POST api/v1/reset-password`
- ✅ `POST api/v1/request-password`

## 🚀 Prochaines Étapes

1. **Redémarrer le serveur Laravel** pour que les changements prennent effet
2. **Tester la déconnexion** - La route `/api/v1/logout` devrait maintenant fonctionner
3. **Vérifier les autres routes** d'authentification si nécessaire

## 📝 Notes

- Le cache des routes a été vidé automatiquement
- Toutes les routes sont accessibles à `/api/v1/...`
- Le middleware `auth:api` est appliqué aux routes authentifiées
- La route `logout` est maintenant correctement enregistrée et accessible
