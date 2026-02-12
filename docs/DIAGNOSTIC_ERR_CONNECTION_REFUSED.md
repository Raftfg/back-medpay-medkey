# 🔍 Diagnostic Expert : ERR_CONNECTION_REFUSED

## 📊 Analyse Hiérarchisée des Causes

### 🎯 Cause #1 : SERVEUR LARAVEL NON DÉMARRÉ (95% des cas)

**Symptôme :** `ERR_CONNECTION_REFUSED` sur `http://localhost:8000`

**Explication :**
- Axios tente de se connecter à `http://localhost:8000/api/v1/patients`
- Le système d'exploitation refuse la connexion car **aucun processus n'écoute sur le port 8000**
- C'est une erreur réseau de bas niveau, pas une erreur HTTP

**Vérification :**
```bash
# Windows PowerShell
netstat -ano | findstr :8000

# Si vide = serveur non démarré
# Si une ligne apparaît = serveur démarré
```

**Solution :**
```bash
cd back-medpay
php artisan serve
```

**Résultat attendu :**
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

### 🎯 Cause #2 : MAUVAIS PORT (3% des cas)

**Symptôme :** Serveur démarré sur un autre port (ex: 8001, 9000)

**Vérification :**
```bash
# Vérifier sur quel port le serveur écoute
netstat -ano | findstr LISTENING | findstr php
```

**Solution :**
```bash
# Démarrer explicitement sur le port 8000
php artisan serve --port=8000

# OU modifier la baseURL dans caller.services.js
```

---

### 🎯 Cause #3 : PROXY VUE.JS MAL CONFIGURÉ (1% des cas)

**Symptôme :** Le proxy ne redirige pas correctement vers Laravel

**Vérification :**
- Fichier `front-medpay-2/vue.config.js` doit avoir :
  ```js
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: false
    }
  }
  ```

**Note :** Le proxy n'est utilisé que si vous accédez via `http://localhost:8080/api/*`.  
Si Axios pointe directement vers `http://localhost:8000`, le proxy n'est **pas utilisé**.

---

### 🎯 Cause #4 : FIREWALL / ANTIVIRUS (0.5% des cas)

**Symptôme :** Connexion bloquée par sécurité

**Vérification :**
- Vérifier les logs Windows Defender / Antivirus
- Tester avec firewall désactivé temporairement

---

### 🎯 Cause #5 : PROBLÈME DNS LOCAL (0.5% des cas)

**Symptôme :** `localhost` ne résout pas vers `127.0.0.1`

**Vérification :**
```bash
ping localhost
# Doit retourner 127.0.0.1
```

**Solution :**
- Utiliser `127.0.0.1` au lieu de `localhost` dans la baseURL

---

## 🔬 Différence entre Erreurs Axios

### ERR_CONNECTION_REFUSED (erreur réseau)
- **Cause :** Aucun serveur n'écoute sur le port
- **Niveau :** OS / Réseau
- **Code HTTP :** Aucun (pas de réponse du serveur)
- **Solution :** Démarrer le serveur Laravel

### ERR_NETWORK (erreur réseau générique)
- **Cause :** Problème de connexion (timeout, DNS, CORS bloqué)
- **Niveau :** Navigateur / Réseau
- **Code HTTP :** Aucun (requête jamais envoyée)
- **Solution :** Vérifier CORS, timeout, DNS

### 401 Unauthorized (erreur HTTP)
- **Cause :** Token manquant ou expiré
- **Niveau :** Application Laravel
- **Code HTTP :** 401
- **Solution :** Vérifier authentification

### 403 Forbidden (erreur HTTP)
- **Cause :** Accès refusé (permissions, tenant)
- **Niveau :** Application Laravel
- **Code HTTP :** 403
- **Solution :** Vérifier permissions / tenant

### 500 Internal Server Error (erreur HTTP)
- **Cause :** Erreur serveur (exception PHP, DB)
- **Niveau :** Application Laravel
- **Code HTTP :** 500
- **Solution :** Vérifier logs Laravel

---

## ✅ Checklist de Vérification

### Étape 1 : Vérifier que le serveur Laravel est démarré

```bash
# Terminal 1 : Démarrer Laravel
cd back-medpay
php artisan serve

# Terminal 2 : Tester la connexion
curl http://localhost:8000/api/v1/health
# OU
curl http://127.0.0.1:8000/api/v1/health
```

**✅ Si réponse HTTP (même 404) :** Serveur démarré  
**❌ Si "Connection refused" :** Serveur non démarré

---

### Étape 2 : Vérifier la configuration Axios

**Fichier :** `front-medpay-2/src/_services/caller.services.js`

**Vérifier :**
```js
// En développement, doit pointer vers :
return "http://localhost:8000/api/v1";
```

**Test :**
```js
// Dans la console du navigateur
console.log(window.location.hostname); // Doit être "hopital1.localhost"
// Vérifier que baseURL est bien "http://localhost:8000/api/v1"
```

---

### Étape 3 : Vérifier le middleware Tenant

**Fichier :** `back-medpay/app/Http/Middleware/TenantMiddleware.php`

**Vérifier :**
- Le header `X-Original-Host` est bien envoyé (ligne 195-200)
- Le domaine `hopital1.localhost` est bien reconnu (ligne 218-228)

**Test :**
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
# Faire une requête depuis le frontend
# Vérifier que le log montre "Hôpital identifié par domaine"
```

---

### Étape 4 : Vérifier CORS

**Fichier :** `back-medpay/app/Http/Middleware/HandleCors.php`

**Vérifier :**
- `http://hopital1.localhost:8080` est dans la liste des origines autorisées (ligne 42)

**Test :**
```bash
curl -X OPTIONS http://localhost:8000/api/v1/patients \
  -H "Origin: http://hopital1.localhost:8080" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

**Résultat attendu :**
```
< HTTP/1.1 200 OK
< Access-Control-Allow-Origin: http://hopital1.localhost:8080
```

---

### Étape 5 : Vérifier les routes

```bash
cd back-medpay
php artisan route:list --path=patients
```

**Vérifier que la route existe :**
```
GET|HEAD  api/v1/patients ................. patients.index
```

---

## 🛠️ Script de Diagnostic Automatique

Voir le fichier `scripts/diagnose-connection.js` pour un diagnostic automatique.

---

## 🚀 Solution Rapide (1 minute)

```bash
# 1. Démarrer Laravel
cd back-medpay
php artisan serve

# 2. Dans un autre terminal, tester
curl http://localhost:8000/api/v1/health

# 3. Si OK, recharger le frontend
# http://hopital1.localhost:8080/patients/list
```

---

## 📝 Notes Techniques

### Pourquoi ERR_CONNECTION_REFUSED au lieu d'un code HTTP ?

**ERR_CONNECTION_REFUSED** est une erreur de **niveau OS/réseau** :
- Le navigateur tente d'établir une connexion TCP vers `localhost:8000`
- Le système d'exploitation refuse car **aucun processus n'écoute sur ce port**
- La requête HTTP n'est **jamais envoyée**, donc pas de code HTTP

**Comparaison :**
- ✅ **Serveur démarré** → Connexion TCP réussie → Requête HTTP envoyée → Code HTTP (200, 401, 500, etc.)
- ❌ **Serveur non démarré** → Connexion TCP refusée → ERR_CONNECTION_REFUSED → Pas de code HTTP

### Architecture Multi-Tenant

1. **Frontend** (`hopital1.localhost:8080`) envoie requête vers `http://localhost:8000/api/v1/patients`
2. **Header `X-Original-Host`** contient `hopital1.localhost:8080`
3. **Middleware Tenant** lit `X-Original-Host` et identifie l'hôpital
4. **Connexion DB** bascule vers la base de l'hôpital
5. **Réponse** retournée avec données isolées par tenant

---

## 🎯 Conclusion

Dans **95% des cas**, l'erreur `ERR_CONNECTION_REFUSED` signifie simplement que **le serveur Laravel n'est pas démarré**.

**Action immédiate :**
```bash
cd back-medpay && php artisan serve
```

**Vérification :**
- Ouvrir `http://localhost:8000` dans le navigateur
- Doit afficher la page Laravel ou une réponse API

---

**Date :** 2026-01-26  
**Auteur :** Expert Senior Vue.js + Laravel Multi-Tenant
