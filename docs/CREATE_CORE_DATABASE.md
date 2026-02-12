# Guide : Créer la Base de Données CORE

## 🎯 Objectif

Créer la base de données `medkey_core` qui contiendra les informations sur les hôpitaux (tenants), modules et administrateurs système.

---

## 📋 Méthodes Disponibles

### Méthode 1 : Commande Artisan (Recommandée) ✅

Utilisez la commande Artisan personnalisée :

```bash
php artisan core:create-database
```

**Options disponibles :**
- `--database=nom` : Spécifier un nom de base différent (défaut: `medkey_core`)
- `--force` : Forcer la création même si la base existe déjà

**Exemple :**
```bash
php artisan core:create-database --database=medkey_core --force
```

---

### Méthode 2 : Script SQL

1. **Ouvrez votre client MySQL** :
   - phpMyAdmin (http://localhost/phpmyadmin)
   - MySQL Workbench
   - HeidiSQL
   - Ou tout autre client MySQL

2. **Exécutez le script SQL** :
   - Ouvrez le fichier : `database/core/create_core_database.sql`
   - Copiez-collez le contenu dans votre client MySQL
   - Exécutez le script

**Ou exécutez directement cette commande SQL :**
```sql
CREATE DATABASE IF NOT EXISTS `medkey_core` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;
```

---

### Méthode 3 : Ligne de Commande MySQL (si MySQL est dans le PATH)

Si MySQL est installé et dans votre PATH :

```bash
# Windows (si MySQL est dans le PATH)
mysql -u root -p -e "CREATE DATABASE medkey_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Linux/Mac
mysql -u root -p -e "CREATE DATABASE medkey_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## ✅ Vérification

Après avoir créé la base, vérifiez qu'elle existe :

### Via Artisan :
```bash
php artisan db:show --database=core
```

### Via SQL :
```sql
SHOW DATABASES LIKE 'medkey_core';
```

### Via phpMyAdmin :
- Ouvrez phpMyAdmin
- Vérifiez que `medkey_core` apparaît dans la liste des bases de données

---

## ⚙️ Configuration .env

Après avoir créé la base, mettez à jour votre fichier `.env` :

```env
# ============================================
# CORE DATABASE CONNECTION
# ============================================
CORE_DB_HOST=127.0.0.1
CORE_DB_PORT=3306
CORE_DB_DATABASE=medkey_core
CORE_DB_USERNAME=root
CORE_DB_PASSWORD=votre_mot_de_passe
```

---

## 🚀 Prochaines Étapes

Une fois la base créée :

1. **Exécuter les migrations CORE** :
   ```bash
   php artisan migrate --database=core --path=database/core/migrations
   ```

2. **Vérifier les tables créées** :
   ```bash
   php artisan db:table hospitals --database=core
   ```

---

## ❓ Problèmes Courants

### Erreur : "Access denied for user"
- Vérifiez les identifiants dans votre `.env`
- Assurez-vous que l'utilisateur MySQL a les droits de création de base

### Erreur : "Database already exists"
- Utilisez l'option `--force` : `php artisan core:create-database --force`
- Ou supprimez d'abord la base existante

### MySQL n'est pas dans le PATH
- Utilisez la **Méthode 1** (Artisan) ou **Méthode 2** (Script SQL)
- Ces méthodes ne nécessitent pas MySQL dans le PATH

---

**Date de création** : 2025-01-20
