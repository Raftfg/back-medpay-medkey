# Dépannage CORS - Guide Complet

## 🔍 Problème : Erreur CORS lors des requêtes API

```
Access to XMLHttpRequest at 'http://localhost:8000/api/v1/login' from origin 'http://localhost:8080' 
has been blocked by CORS policy: Response to preflight request doesn't pass access control check: 
No 'Access-Control-Allow-Origin' header is present on the requested resource.
```

---

## ✅ Solutions Appliquées

### 1. Middleware CORS Personnalisé

Un middleware CORS personnalisé a été créé : `app/Http/Middleware/HandleCors.php`

**Fonctionnalités :**
- ✅ Gère les requêtes preflight (OPTIONS)
- ✅ Ajoute les headers CORS nécessaires
- ✅ Supporte les credentials
- ✅ Gère les origines multiples

### 2. Configuration dans Kernel

Le middleware CORS est placé **en premier** dans le groupe `api` :

```php
'api' => [
    \App\Http\Middleware\HandleCors::class, // EN PREMIER
    // ... autres middlewares
],
```

### 3. Middlewares Tenant

Les middlewares `TenantMiddleware` et `EnsureUserBelongsToHospital` laissent passer les requêtes OPTIONS sans traitement.

---

## 🧪 Tests et Vérifications

### 1. Vérifier que le serveur Laravel est démarré

```bash
php artisan serve
# Le serveur doit être accessible sur http://localhost:8000
```

### 2. Tester une requête OPTIONS (preflight)

```bash
curl -X OPTIONS http://localhost:8000/api/v1/login \
  -H "Origin: http://localhost:8080" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type,Authorization" \
  -v
```

**Résultat attendu :**
```
< HTTP/1.1 200 OK
< Access-Control-Allow-Origin: http://localhost:8080
< Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
< Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Tenant-Domain, Accept, Origin
< Access-Control-Allow-Credentials: true
< Access-Control-Max-Age: 86400
```

### 3. Tester une requête POST réelle

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Origin: http://localhost:8080" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  -v
```

**Résultat attendu :**
```
< HTTP/1.1 200 OK (ou 401 Unauthorized si credentials invalides)
< Access-Control-Allow-Origin: http://localhost:8080
< Access-Control-Allow-Credentials: true
```

---

## 🔧 Actions de Dépannage

### 1. Vider les caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 2. Vérifier la configuration CORS

Vérifiez que `config/cors.php` contient bien `http://localhost:8080` dans `allowed_origins`.

### 3. Vérifier les middlewares

Vérifiez que `app/Http/Kernel.php` contient bien :

```php
'api' => [
    \App\Http\Middleware\HandleCors::class, // EN PREMIER
    // ...
],
```

### 4. Vérifier les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

Cherchez les erreurs liées à CORS ou aux middlewares.

### 5. Tester avec Postman ou Insomnia

Ces outils permettent de tester les requêtes API sans problème CORS (ils ne sont pas soumis aux restrictions CORS du navigateur).

---

## 🐛 Problèmes Courants

### Problème 1 : "No 'Access-Control-Allow-Origin' header"

**Cause :** Le middleware CORS n'est pas exécuté ou ne fonctionne pas correctement.

**Solution :**
1. Vérifier que le middleware est bien enregistré dans `Kernel.php`
2. Vider les caches : `php artisan config:clear`
3. Redémarrer le serveur Laravel

### Problème 2 : "Preflight request doesn't pass"

**Cause :** La requête OPTIONS n'est pas correctement gérée.

**Solution :**
1. Vérifier que `TenantMiddleware` laisse passer les requêtes OPTIONS
2. Tester manuellement une requête OPTIONS avec curl

### Problème 3 : "Credentials not supported"

**Cause :** Le header `Access-Control-Allow-Credentials` n'est pas présent ou l'origine utilise `*`.

**Solution :**
1. Vérifier que `supports_credentials` est à `true` dans `config/cors.php`
2. S'assurer que l'origine n'est pas `*` mais une origine spécifique

---

## 📝 Configuration Frontend (Vue.js)

Assurez-vous que votre configuration Axios inclut les credentials :

```javascript
axios.defaults.withCredentials = true;
```

Ou pour une requête spécifique :

```javascript
axios.post('http://localhost:8000/api/v1/login', data, {
  withCredentials: true
});
```

---

## 🔍 Debug Avancé

### Activer les logs CORS

Ajoutez dans `app/Http/Middleware/HandleCors.php` :

```php
public function handle(Request $request, Closure $next)
{
    \Log::info('CORS Request', [
        'method' => $request->method(),
        'origin' => $request->header('Origin'),
        'path' => $request->path(),
    ]);
    
    // ... reste du code
}
```

### Vérifier les headers envoyés

Dans le navigateur (DevTools > Network) :
1. Ouvrez l'onglet Network
2. Faites une requête
3. Vérifiez les headers de la requête OPTIONS (preflight)
4. Vérifiez les headers de la réponse

---

## ✅ Checklist de Vérification

- [ ] Le serveur Laravel est démarré sur `http://localhost:8000`
- [ ] Le middleware CORS est en premier dans le groupe `api`
- [ ] Les caches sont vidés (`php artisan config:clear`)
- [ ] `http://localhost:8080` est dans `allowed_origins` de `config/cors.php`
- [ ] Les middlewares tenant laissent passer les requêtes OPTIONS
- [ ] Le frontend envoie `withCredentials: true` dans les requêtes Axios
- [ ] Les headers CORS sont présents dans les réponses (vérifier avec curl ou DevTools)

---

**Date de création** : 2025-01-20
