# Solution CORS - Instructions Finales

## 🔍 Diagnostic

Le test montre que le serveur Laravel retourne **404** pour la route `/api/v1/login`. Cela signifie que :

1. ✅ La route existe bien (vérifiée avec `php artisan route:list`)
2. ❌ Le serveur Laravel **n'est pas démarré** ou n'est pas accessible sur `http://localhost:8000`

---

## ✅ Solution : Démarrer le Serveur Laravel

### Étape 1 : Démarrer le serveur

Ouvrez un **nouveau terminal** et exécutez :

```bash
cd back-medpay
php artisan serve
```

Vous devriez voir :
```
INFO  Server running on [http://127.0.0.1:8000]
```

### Étape 2 : Vérifier que le serveur répond

Dans un autre terminal, testez :

```bash
cd back-medpay
php test-cors.php
```

Vous devriez voir :
- ✅ Code HTTP: 200 (pour OPTIONS)
- ✅ Headers CORS présents

### Étape 3 : Tester depuis le frontend

Une fois le serveur démarré, rechargez la page de login et essayez de vous connecter.

---

## 📋 Configuration CORS Actuelle

### Middleware CORS

Le middleware CORS est configuré à **deux niveaux** :

1. **Niveau global** (`app/Http/Kernel.php`) :
   ```php
   protected $middleware = [
       \Illuminate\Http\Middleware\HandleCors::class, // ✅ ICI
       // ...
   ];
   ```

2. **Niveau API** (`app/Http/Kernel.php`) :
   ```php
   'api' => [
       \Illuminate\Http\Middleware\HandleCors::class, // ✅ ET ICI
       // ...
   ],
   ```

### Configuration CORS

Fichier : `config/cors.php`

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', '*/api/*'],
'allowed_origins' => [
    'http://localhost:8080',
    // ...
],
'supports_credentials' => true,
'max_age' => 86400,
```

---

## 🧪 Tests de Vérification

### Test 1 : Vérifier que le serveur est démarré

```bash
curl http://localhost:8000/api/v1/login
```

**Résultat attendu :** Code HTTP 405 (Method Not Allowed) ou 422 (Validation Error)
**❌ Si 404 :** Le serveur n'est pas démarré

### Test 2 : Test CORS complet

```bash
cd back-medpay
php test-cors.php
```

**Résultat attendu :**
```
✅ Test OPTIONS RÉUSSI
✅ Test POST RÉUSSI
```

---

## ⚠️ Problèmes Courants

### Problème 1 : "404 Not Found"

**Cause :** Le serveur Laravel n'est pas démarré

**Solution :**
```bash
php artisan serve
```

### Problème 2 : "Connection refused"

**Cause :** Le serveur n'écoute pas sur le bon port

**Solution :**
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Problème 3 : Headers CORS manquants

**Cause :** Le middleware CORS n'est pas exécuté

**Solution :**
1. Vérifier que le middleware est bien dans `Kernel.php`
2. Vider les caches : `php artisan config:clear`
3. Redémarrer le serveur

---

## 📝 Checklist de Vérification

- [ ] Serveur Laravel démarré (`php artisan serve`)
- [ ] Serveur accessible sur `http://localhost:8000`
- [ ] Route `/api/v1/login` existe (vérifiée avec `php artisan route:list`)
- [ ] Middleware CORS dans `Kernel.php` (niveau global ET api)
- [ ] Configuration CORS correcte dans `config/cors.php`
- [ ] Caches vidés (`php artisan config:clear`)
- [ ] Test CORS réussi (`php test-cors.php`)

---

## 🚀 Commandes Rapides

```bash
# Démarrer le serveur
php artisan serve

# Vérifier les routes
php artisan route:list --path=login

# Tester CORS
php test-cors.php

# Vider les caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

**Date** : 2025-01-20  
**Statut** : ✅ Configuration complète - Serveur à démarrer
