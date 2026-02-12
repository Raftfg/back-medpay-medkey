# Guide de Démarrage Rapide - Phase 1

## 🚀 Créer la Base de Données CORE

### Option 1 : Commande Artisan (Recommandée) ✅

```bash
cd back-medpay
php artisan core:create-database
```

Cette commande :
- ✅ Crée automatiquement la base `medkey_core`
- ✅ Configure le charset et collation corrects
- ✅ Vérifie si la base existe déjà
- ✅ Fonctionne même si MySQL n'est pas dans le PATH

### Option 2 : Via phpMyAdmin

1. Ouvrez phpMyAdmin (généralement : http://localhost/phpmyadmin)
2. Cliquez sur l'onglet "SQL"
3. Copiez-collez cette commande :
   ```sql
   CREATE DATABASE IF NOT EXISTS `medkey_core` 
       CHARACTER SET utf8mb4 
       COLLATE utf8mb4_unicode_ci;
   ```
4. Cliquez sur "Exécuter"

### Option 3 : Script SQL

Ouvrez le fichier `database/core/create_core_database.sql` et exécutez-le dans votre client MySQL.

---

## ⚙️ Configurer le .env

Ajoutez ces lignes à votre fichier `.env` :

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

**Remplacez `votre_mot_de_passe` par votre mot de passe MySQL réel.**

---

## 📦 Exécuter les Migrations CORE

Une fois la base créée et le .env configuré :

```bash
php artisan migrate --database=core --path=database/core/migrations
```

Cette commande crée les tables :
- `hospitals` - Informations sur les hôpitaux (tenants)
- `hospital_modules` - Modules activés par hôpital
- `system_admins` - Administrateurs système

---

## ✅ Vérification

Vérifiez que tout fonctionne :

```bash
# Lister les tables créées
php artisan db:table hospitals --database=core

# Ou via Artisan
php artisan tinker
>>> \App\Core\Models\Hospital::count()
```

---

## 🎯 Créer un Hôpital de Test

Une fois la base CORE créée et les migrations exécutées, créez un hôpital de test :

```bash
php artisan hospital:create "Hôpital Central"
```

Cette commande :
- ✅ Crée l'hôpital dans la base CORE
- ✅ Génère automatiquement le domaine et le nom de la base
- ✅ Propose d'activer des modules par défaut

**Exemple complet :**
```bash
php artisan hospital:create "Hôpital Central" \
    --domain="hopital-central.medkey.com" \
    --database="medkey_hospital_1" \
    --status="provisioning"
```

Voir `docs/CREATE_HOSPITAL_EXAMPLE.md` pour plus d'exemples.

---

## 🎯 Prochaines Étapes

Une fois la Phase 1 terminée, vous pouvez :

1. **Créer un hôpital de test** (commande ci-dessus)
2. **Créer la base de données tenant** pour cet hôpital
3. **Passer à la Phase 2** : Adapter le middleware

---

## ❓ Besoin d'Aide ?

Consultez la documentation complète :
- `docs/PHASE_1_IMPLEMENTATION.md` - Documentation détaillée
- `docs/CREATE_CORE_DATABASE.md` - Guide de création de la base

---

**Bonne continuation ! 🚀**
