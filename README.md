# Medkey - Backend (Système DME Multi-Tenant)

## 📌 Contexte du Projet

**Medkey** est une plateforme backend robuste basée sur **Laravel 10**, dédiée à la gestion des Dossiers Médicaux Électroniques (DME). Sa particularité réside dans son architecture **Multi-Tenant**, permettant à une seule instance de l'application de servir plusieurs établissements hospitaliers de manière isolée.

Chaque "tenant" possède sa propre configuration et ses propres données, garantissant une séparation stricte des informations médicales sensibles.

## 🚀 Mise en route

### 📋 Prérequis Systèmes

- **PHP** : Version **8.1** minimum.
- **Extensions PHP** : `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`.
- **Base de Données** : **MySQL 8.0+** ou MariaDB.
- **Gestionnaire** : Composer 2.x
- **Frontend Assets** : Node.js (v16+) & NPM.

### ⚙️ Installation & Configuration Initiales

1. **Installation des dépendances** :
   ```bash
   composer install
   npm install
   ```

2. **Environnement** :
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Variables Critiques (`.env`)** :
   - Configurez vos accès `DB_*` pour la base de données centrale.
   - Réglez `FRONTEND_URL` pour la redirection CORS.

## 🏢 Gestion du Multi-Tenancy

Le système utilise des commandes personnalisées pour maintenir les schémas de base de données à travers tous les hôpitaux.

- **Valider les schémas de tous les tenants** :
  ```bash
  php artisan tenant:schema-validate --detailed
  ```

- **Synchroniser les schémas (Appliquer les migrations manquantes)** :
  ```bash
  # Simulation (recommandé avant application)
  php artisan tenant:schema-sync --dry-run
  
  # Application réelle
  php artisan tenant:schema-sync --force
  ```

## 🛠 Architecture & Packages Clés

- **Structure Modulaire** : Utilisation de `nwidart/laravel-modules` pour séparer les fonctionnalités (ex: Module DME).
- **Sécurité** : 
  - **Laravel Passport** : Pour l'authentification API.
  - **Spatie Permission** : Gestion fine des rôles et permissions.
- **Gestion de Données** :
  - **Activity Log** : Traçabilité complète des actions médicales.
  - **Media Library** : Gestion des documents patients.
- **Production de Documents** : `Laravel DomPDF` et `Simple QRCode`.

## 💻 Commandes de Développement

- **Serveur local** : `php artisan serve`
- **Tinker** : `php artisan tinker`
- **Logs** : `tail -f storage/logs/laravel.log`


