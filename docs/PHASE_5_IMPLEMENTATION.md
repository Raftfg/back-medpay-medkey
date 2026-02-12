# Phase 5 : Système d'Onboarding - Implémentation

## 📋 Vue d'ensemble

La Phase 5 implémente un système complet d'onboarding pour les nouveaux tenants (hôpitaux). Ce système automatise la création et le provisioning des hôpitaux, rendant le processus simple et rapide.

## ✅ Composants Implémentés

### 1. TenantProvisioningService

**Fichier** : `app/Core/Services/TenantProvisioningService.php`

Service centralisé qui gère toutes les opérations de provisioning :

#### Méthodes principales :

- **`provision(Hospital $hospital, array $options)`** : Provisionne un hôpital complet
  - Crée la base de données
  - Exécute les migrations
  - Active les modules par défaut
  - (Optionnel) Exécute les seeders

- **`createDatabase(Hospital $hospital, bool $force)`** : Crée la base de données MySQL

- **`runMigrations(Hospital $hospital)`** : Exécute toutes les migrations (principales + modules)

- **`activateModules(Hospital $hospital, array $modules)`** : Active des modules pour un hôpital

- **`deactivateModules(Hospital $hospital, array $modules)`** : Désactive des modules

- **`seed(Hospital $hospital, ?string $seederClass)`** : Exécute les seeders

- **`isProvisioned(Hospital $hospital)`** : Vérifie si un hôpital est complètement provisionné

- **`getProvisioningStatus(Hospital $hospital)`** : Retourne le statut détaillé du provisioning

### 2. Commandes Artisan

#### `tenant:create` - Création avec provisioning automatique

**Fichier** : `app/Core/Console/Commands/TenantCreateCommand.php`

Crée un nouveau tenant et le provisionne automatiquement.

**Usage** :
```bash
# Création simple (sans provisioning)
php artisan tenant:create "Hôpital Test" "hopital-test.medkey.com"

# Création avec provisioning automatique
php artisan tenant:create "Hôpital Test" "hopital-test.medkey.com" --provision

# Création avec provisioning et seeders
php artisan tenant:create "Hôpital Test" "hopital-test.medkey.com" --provision --seed

# Avec modules personnalisés
php artisan tenant:create "Hôpital Test" "hopital-test.medkey.com" --provision --modules="Acl,Administration,Patient,Stock"
```

**Options** :
- `--database=` : Nom de la base de données (auto-généré si non fourni)
- `--host=` : Host de la base de données (défaut: 127.0.0.1)
- `--port=` : Port de la base de données (défaut: 3306)
- `--provision` : Provisionner automatiquement
- `--seed` : Exécuter les seeders après le provisioning
- `--force` : Forcer la création même si la base existe
- `--modules=` : Modules à activer (séparés par virgule)

#### `tenant:provision` - Provisioning d'un tenant existant

**Fichier** : `app/Core/Console/Commands/TenantProvisionCommand.php`

Provisionne un hôpital existant (créer DB, migrations, modules).

**Usage** :
```bash
# Provisionnement simple
php artisan tenant:provision 1

# Avec seeders
php artisan tenant:provision 1 --seed

# Avec modules personnalisés
php artisan tenant:provision 1 --modules="Acl,Administration,Patient"
```

**Options** :
- `--seed` : Exécuter les seeders après le provisioning
- `--force` : Forcer la création même si la base existe
- `--modules=` : Modules à activer (séparés par virgule)

#### `tenant:status` - Statut de provisioning

**Fichier** : `app/Core/Console/Commands/TenantStatusCommand.php`

Affiche le statut de provisioning d'un ou plusieurs tenants.

**Usage** :
```bash
# Statut d'un hôpital spécifique
php artisan tenant:status 1

# Statut de tous les hôpitaux
php artisan tenant:status
```

#### Commandes existantes améliorées

**`hospital:create`** : Améliorée pour proposer le provisioning automatique

**`tenant:migrate`** : Existe déjà (Phase 2)

**`tenant:seed`** : Existe déjà (Phase 2)

## 🔄 Processus d'Onboarding

### Processus Automatique (Recommandé)

1. **Création du tenant** :
   ```bash
   php artisan tenant:create "Nouvel Hôpital" "nouvel-hopital.medkey.com" --provision --seed
   ```

2. **Vérification** :
   ```bash
   php artisan tenant:status 1
   ```

### Processus Manuel (Étape par étape)

1. **Créer l'hôpital** :
   ```bash
   php artisan hospital:create "Nouvel Hôpital" --domain="nouvel-hopital.medkey.com"
   ```

2. **Provisionner** :
   ```bash
   php artisan tenant:provision 1 --seed
   ```

3. **Vérifier** :
   ```bash
   php artisan tenant:status 1
   ```

## 📊 Statut de Provisioning

Le statut de provisioning inclut :

- **Base de données** : Existe ou non
- **Migrations** : Nombre de migrations exécutées
- **Modules** : Nombre de modules activés
- **Provisionné** : Oui ou non (basé sur la présence de migrations)

## 🔧 Configuration

La configuration du provisioning se trouve dans `config/tenant.php` :

```php
'provisioning' => [
    'auto_migrate' => env('TENANT_AUTO_MIGRATE', true),
    'auto_seed' => env('TENANT_AUTO_SEED', false),
    'default_modules' => env('TENANT_DEFAULT_MODULES', 'Acl,Administration,Patient,Payment'),
],
```

## 📝 Exemples d'Utilisation

### Exemple 1 : Création complète d'un nouveau tenant

```bash
php artisan tenant:create "Hôpital Régional" "hopital-regional.medkey.com" \
    --provision \
    --seed \
    --modules="Acl,Administration,Patient,Payment,Stock,Hospitalization"
```

### Exemple 2 : Provisioning d'un tenant existant

```bash
# Créer d'abord l'hôpital
php artisan hospital:create "Hôpital Test" --domain="test.medkey.com"

# Puis provisionner
php artisan tenant:provision 1 --seed
```

### Exemple 3 : Vérification du statut

```bash
# Vérifier un hôpital spécifique
php artisan tenant:status 1

# Vérifier tous les hôpitaux
php artisan tenant:status
```

## ⚠️ Points d'Attention

1. **Permissions MySQL** : L'utilisateur MySQL doit avoir les permissions `CREATE DATABASE`

2. **Base existante** : Utiliser `--force` pour recréer une base existante (⚠️ **ATTENTION** : supprime toutes les données)

3. **Modules** : Les modules doivent exister dans le dossier `Modules/` pour que les migrations fonctionnent

4. **Seeders** : Les seeders doivent être adaptés pour le multi-tenant (sans `hospital_id`)

5. **Statut** : Le statut de l'hôpital passe automatiquement de `provisioning` à `active` après un provisioning réussi

## 🧪 Tests

Pour tester le système d'onboarding :

```bash
# 1. Créer un nouveau tenant avec provisioning
php artisan tenant:create "Hôpital Test" "test.medkey.com" --provision --seed

# 2. Vérifier le statut
php artisan tenant:status

# 3. Tester l'accès via le domaine (si configuré)
curl -H "Host: test.medkey.com" http://localhost:8000/api/v1/health
```

## 📚 Commandes Disponibles

| Commande | Description |
|----------|-------------|
| `tenant:create` | Crée un nouveau tenant avec provisioning optionnel |
| `tenant:provision` | Provisionne un tenant existant |
| `tenant:status` | Affiche le statut de provisioning |
| `tenant:migrate` | Exécute les migrations pour un tenant |
| `tenant:seed` | Exécute les seeders pour un tenant |
| `tenant:list` | Liste tous les tenants |
| `hospital:create` | Crée un hôpital (sans provisioning automatique) |

## ✅ Checklist de Validation

- [x] `TenantProvisioningService` créé avec toutes les méthodes
- [x] Commande `tenant:create` implémentée
- [x] Commande `tenant:provision` implémentée
- [x] Commande `tenant:status` implémentée
- [x] Commande `hospital:create` améliorée
- [x] Commandes enregistrées dans `Kernel.php`
- [x] Documentation complète

## 🎯 Prochaines Étapes

La Phase 5 est complète. Les prochaines étapes sont :

- **Phase 6** : Gestion des Modules (activation/désactivation, middleware de vérification)
- **Phase 7** : Tests et Validation (tests unitaires, tests d'intégration, tests de performance)

---

**Date de complétion** : 2025-01-XX
**Statut** : ✅ Complété
