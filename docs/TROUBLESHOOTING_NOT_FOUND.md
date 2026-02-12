# Résolution de l'erreur "Not Found - The requested resource / was not found"

## 🔍 Diagnostic

L'erreur "Not Found - The requested resource / was not found on this server" indique que le serveur web ne trouve pas la route `/`.

## ✅ Solutions

### Solution 1 : Utiliser le serveur Laravel intégré (RECOMMANDÉ)

**Pour le développement local**, utilisez le serveur Laravel intégré :

```bash
cd back-medpay
php artisan serve
```

Le serveur démarrera sur `http://127.0.0.1:8000` ou `http://localhost:8000`.

**Vérification :**
- Ouvrez `http://localhost:8000` dans votre navigateur
- Vous devriez voir la page "Welcome to Laravel"

---

### Solution 2 : Configuration Apache (si vous utilisez Apache)

Si vous utilisez Apache, assurez-vous que :

1. **Le DocumentRoot pointe vers le répertoire `public`** :

```apache
<VirtualHost *:80>
    ServerName medkey.local
    DocumentRoot "E:/Dossier1/Medkey - nouvelle version 2026/medkey/back-medpay/public"
    
    <Directory "E:/Dossier1/Medkey - nouvelle version 2026/medkey/back-medpay/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. **Le module `mod_rewrite` est activé** :
```bash
# Vérifier si mod_rewrite est activé
php -m | grep rewrite
```

3. **Redémarrer Apache** après les modifications

---

### Solution 3 : Configuration Nginx (si vous utilisez Nginx)

Si vous utilisez Nginx, configurez le bloc serveur ainsi :

```nginx
server {
    listen 80;
    server_name medkey.local;
    root "E:/Dossier1/Medkey - nouvelle version 2026/medkey/back-medpay/public";
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

### Solution 4 : Vérifier les permissions (Windows)

Sur Windows, assurez-vous que :

1. **Le répertoire `storage` et `bootstrap/cache` sont accessibles en écriture**
2. **Les permissions sont correctes** (généralement pas de problème sur Windows)

---

## 🧪 Test de Vérification

### Test 1 : Vérifier que le serveur Laravel répond

```bash
# Dans un terminal
curl http://localhost:8000
```

**Résultat attendu :** Code HTTP 200 avec le contenu HTML de la page welcome

### Test 2 : Vérifier les routes

```bash
php artisan route:list --path=/
```

**Résultat attendu :** La route `/` doit apparaître dans la liste

### Test 3 : Vérifier le fichier index.php

```bash
# Vérifier que le fichier existe
ls -la public/index.php
```

---

## 📋 Checklist de Vérification

- [ ] Le serveur Laravel est démarré (`php artisan serve`)
- [ ] L'URL utilisée est correcte (`http://localhost:8000` ou `http://127.0.0.1:8000`)
- [ ] Le DocumentRoot pointe vers `back-medpay/public` (si Apache/Nginx)
- [ ] Le fichier `public/index.php` existe
- [ ] Le fichier `public/.htaccess` existe (si Apache)
- [ ] Les routes sont chargées (`php artisan route:list` fonctionne)
- [ ] Pas d'erreur dans les logs Laravel (`storage/logs/laravel.log`)

---

## 🔧 Commandes Utiles

### Démarrer le serveur Laravel
```bash
cd back-medpay
php artisan serve
```

### Démarrer sur un port spécifique
```bash
php artisan serve --port=8000
```

### Vider les caches
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Vérifier les routes
```bash
php artisan route:list
```

### Vérifier les logs
```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50

# Linux/Mac
tail -f storage/logs/laravel.log
```

---

## ⚠️ Erreurs Courantes

### Erreur : "The requested resource / was not found on this server"

**Causes possibles :**
1. Le serveur web pointe vers `back-medpay` au lieu de `back-medpay/public`
2. Le serveur Laravel n'est pas démarré
3. L'URL utilisée est incorrecte

**Solution :** Utilisez `php artisan serve` pour le développement local

---

### Erreur : "404 Not Found" (avec le template Laravel)

**Cause :** La route n'existe pas ou n'est pas chargée

**Solution :** Vérifiez `routes/web.php` et exécutez `php artisan route:clear`

---

## 📝 Note Importante

**Pour le développement local**, il est **fortement recommandé** d'utiliser le serveur Laravel intégré (`php artisan serve`) plutôt qu'Apache/Nginx, car :

- ✅ Configuration automatique
- ✅ Pas besoin de configuration serveur web
- ✅ Fonctionne immédiatement
- ✅ Parfait pour le développement

**Pour la production**, configurez Apache ou Nginx correctement avec le DocumentRoot pointant vers `public`.

---

**Date** : 2025-01-20
