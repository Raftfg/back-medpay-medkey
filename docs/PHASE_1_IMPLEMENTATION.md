# Phase 1 : Infrastructure CORE - Documentation

## ✅ État d'Avancement

**Phase 1 : COMPLÉTÉE** ✅

Tous les composants de l'infrastructure CORE ont été créés et sont prêts à être utilisés.

---

## 📁 Fichiers Créés

### Migrations CORE
- ✅ `database/core/migrations/2025_01_20_100000_create_hospitals_table.php`
- ✅ `database/core/migrations/2025_01_20_100001_create_hospital_modules_table.php`
- ✅ `database/core/migrations/2025_01_20_100002_create_system_admins_table.php`

### Modèles CORE
- ✅ `app/Core/Models/Hospital.php`
- ✅ `app/Core/Models/HospitalModule.php`
- ✅ `app/Core/Models/SystemAdmin.php`

### Services
- ✅ `app/Core/Services/TenantConnectionService.php`

### Helpers
- ✅ `app/Core/Helpers/TenantHelper.php`

### Configuration
- ✅ `config/database.php` (modifié : ajout connexions `core` et `tenant`)
- ✅ `config/tenant.php` (nouveau fichier)

---

## 🗄️ Structure de la Base CORE

### Table `hospitals`

Stocke les informations de chaque hôpital (tenant) :

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint | ID unique |
| `name` | string | Nom de l'hôpital |
| `domain` | string | Domaine unique (ex: hopital1.medkey.com) |
| `slug` | string | Slug pour URL |
| `database_name` | string | Nom de la base de données (ex: medkey_hospital_1) |
| `database_host` | string | Host de la base (défaut: 127.0.0.1) |
| `database_port` | string | Port de la base (défaut: 3306) |
| `database_username` | string | Username spécifique (optionnel) |
| `database_password` | string | Password spécifique (optionnel, chiffré) |
| `status` | enum | Statut : active, inactive, suspended, provisioning |
| `address`, `phone`, `email`, `logo`, `description` | string | Informations complémentaires |
| `uuid` | uuid | UUID unique |
| `created_by` | bigint | ID de l'admin système |
| `provisioned_at` | timestamp | Date de provisioning |
| `created_at`, `updated_at`, `deleted_at` | timestamps | Métadonnées |

### Table `hospital_modules`

Stocke les modules activés pour chaque hôpital :

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint | ID unique |
| `hospital_id` | bigint | Référence vers hospitals |
| `module_name` | string | Nom du module (ex: 'Patient', 'Payment') |
| `is_enabled` | boolean | Module activé ou non |
| `config` | json | Configuration spécifique du module |
| `enabled_at`, `disabled_at` | timestamp | Dates d'activation/désactivation |
| `enabled_by` | bigint | Admin qui a activé |
| `notes` | text | Notes |
| `created_at`, `updated_at` | timestamps | Métadonnées |

### Table `system_admins`

Stocke les administrateurs système :

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint | ID unique |
| `name` | string | Nom |
| `email` | string | Email (unique) |
| `password` | string | Mot de passe (hashé) |
| `permissions` | json | Permissions spécifiques |
| `role` | enum | super_admin, admin, support |
| `is_active` | boolean | Actif ou non |
| `last_login_at`, `last_login_ip` | timestamp/string | Dernière connexion |
| `created_at`, `updated_at`, `deleted_at` | timestamps | Métadonnées |

---

## ⚙️ Configuration

### Variables d'Environnement (.env)

Ajoutez ces variables à votre fichier `.env` :

```env
# ============================================
# CORE DATABASE CONNECTION
# ============================================
CORE_DB_HOST=127.0.0.1
CORE_DB_PORT=3306
CORE_DB_DATABASE=medkey_core
CORE_DB_USERNAME=root
CORE_DB_PASSWORD=votre_mot_de_passe

# ============================================
# TENANT DATABASE CONNECTION (Défaut)
# ============================================
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=root
TENANT_DB_PASSWORD=votre_mot_de_passe
TENANT_DB_PREFIX=medkey_

# ============================================
# TENANT CONFIGURATION
# ============================================
TENANT_IDENTIFICATION=domain
TENANT_DOMAIN_PATTERN={tenant}.medkey.com
TENANT_HEADER_NAME=X-Tenant-Domain
TENANT_CACHE_ENABLED=true
TENANT_CACHE_TTL=3600
TENANT_AUTO_MIGRATE=true
TENANT_AUTO_SEED=false
TENANT_DEFAULT_MODULES=Acl,Administration,Patient,Payment
```

---

## 🚀 Utilisation

### 1. Créer la Base CORE

```bash
# Créer la base de données
mysql -u root -p -e "CREATE DATABASE medkey_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Exécuter les migrations CORE
php artisan migrate --database=core --path=database/core/migrations
```

### 2. Utiliser les Modèles CORE

```php
use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use App\Core\Models\SystemAdmin;

// Créer un hôpital
$hospital = Hospital::create([
    'name' => 'Hôpital Central',
    'domain' => 'hopital-central.medkey.com',
    'database_name' => 'medkey_hospital_1',
    'status' => 'provisioning',
]);

// Activer un module
$hospital->modules()->create([
    'module_name' => 'Patient',
    'is_enabled' => true,
]);

// Vérifier si un module est activé
if ($hospital->hasModule('Patient')) {
    // Module activé
}
```

### 3. Utiliser TenantConnectionService

```php
use App\Core\Services\TenantConnectionService;
use App\Core\Models\Hospital;

$service = app(TenantConnectionService::class);

// Connecter à un hôpital
$hospital = Hospital::find(1);
$service->connect($hospital);

// Vérifier la connexion
if ($service->isConnected()) {
    $connection = $service->getCurrentConnection();
    // Utiliser la connexion...
}

// Déconnecter
$service->disconnect();
```

### 4. Utiliser les Helpers

```php
// Récupérer le tenant courant
$hospital = currentTenant();
$hospitalId = currentTenantId();

// Vérifier la connexion
if (isTenantConnected()) {
    $connection = tenantConnection();
}

// Connecter à un tenant
connectTenant($hospital);
// ou
connectTenant(1); // ID de l'hôpital

// Déconnecter
disconnectTenant();
```

---

## 🔧 Méthodes Utiles

### Hospital Model

```php
// Vérifier le statut
$hospital->isActive();
$hospital->isSuspended();
$hospital->isInactive();
$hospital->isProvisioning();

// Modules
$hospital->hasModule('Patient');
$hospital->modules; // Tous les modules
$hospital->enabledModules; // Modules activés uniquement

// Configuration de la base de données
$config = $hospital->getDatabaseConfig();
```

### TenantConnectionService

```php
// Connecter
$service->connect($hospital);

// Déconnecter
$service->disconnect();

// Vérifier la connexion
$service->isConnected();

// Récupérer la connexion
$connection = $service->getCurrentConnection();

// Tester une connexion (sans se connecter)
$isValid = $service->testConnection($hospital);
```

---

## 📝 Notes Importantes

1. **Connexion CORE** : Les modèles CORE utilisent automatiquement la connexion `core`
2. **Connexion Tenant** : La connexion `tenant` est configurée dynamiquement par le middleware
3. **Cache** : Les informations des hôpitaux sont mises en cache pour améliorer les performances
4. **Sécurité** : Les mots de passe des bases de données sont stockés (idéalement chiffrés)

---

## ⚠️ Prochaines Étapes

La Phase 1 est complète. Pour continuer :

1. **Phase 2** : Adapter le TenantMiddleware pour utiliser TenantConnectionService
2. **Phase 3** : Migrer les données existantes vers la nouvelle architecture
3. **Phase 4** : Adapter les modèles pour supprimer hospital_id

---

**Date de création** : 2025-01-20  
**Version** : 1.0
