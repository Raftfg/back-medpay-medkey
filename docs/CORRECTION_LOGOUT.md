# Correction de la Route de Déconnexion

## Problème Identifié
- Erreur 404 lors de l'appel à `POST /api/v1/logout`
- La route était commentée dans le fichier de routes

## Corrections Appliquées

### 1. Route Backend (`Modules/Acl/Routes/api.php`)
✅ Route `/api/v1/logout` ajoutée et activée (ligne 31)
✅ Route alternative `/api/v1/auth/logout` également disponible (ligne 46)

### 2. Méthode `logout` (`AuthController.php`)
✅ Méthode améliorée avec gestion d'erreur
✅ Utilise `$request->user()->token()->revoke()` pour Passport
✅ Retourne toujours une réponse de succès

### 3. Cache Laravel
✅ Cache des routes vidé : `php artisan route:clear`
✅ Cache complet vidé : `php artisan optimize:clear`

## Vérification des Routes

Les routes suivantes sont maintenant enregistrées :
```
POST  api/v1/logout ................ logout › AuthController@logout
POST  api/v1/auth/logout .......... auth.logout › AuthController@logout
```

## Étapes pour Résoudre le Problème 404

### Si le problème persiste après les corrections :

1. **Redémarrer le serveur Laravel** :
   ```bash
   # Arrêter le serveur (Ctrl+C)
   # Puis redémarrer
   php artisan serve
   # Ou si vous utilisez un autre serveur (Apache, Nginx), redémarrer le service
   ```

2. **Vérifier que les routes sont bien chargées** :
   ```bash
   php artisan route:list --path=logout
   ```
   Vous devriez voir les deux routes `logout` listées.

3. **Vérifier le baseURL du frontend** :
   - En développement : `http://localhost:8000/api/v1`
   - Le frontend appelle `Axios.post("/logout")` qui devient `http://localhost:8000/api/v1/logout`

4. **Vérifier que le token est envoyé** :
   - Le middleware `auth:api` est requis pour la route `/logout`
   - Vérifier que le header `Authorization: Bearer {token}` est présent dans la requête

5. **Vérifier les logs Laravel** :
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Pour voir les erreurs détaillées si le problème persiste.

## Structure de la Route

```php
Route::group(['prefix' => 'api'], function () {
    $apiVersion = 'v' . config('premier.api_version'); // 'v1'
    Route::group(['prefix' => $apiVersion], function () {
        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            // ...
        });
    });
});
```

**URL complète** : `/api/v1/logout`

## Test de la Route

Pour tester manuellement la route :

```bash
# Avec curl (remplacer {token} par votre token)
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

## Notes Importantes

- ⚠️ **Le serveur Laravel doit être redémarré** pour que les changements prennent effet
- ✅ La route nécessite l'authentification (`auth:api` middleware)
- ✅ Le token Passport sera révoqué lors de la déconnexion
- ✅ Même en cas d'erreur, le frontend nettoie le localStorage et redirige
