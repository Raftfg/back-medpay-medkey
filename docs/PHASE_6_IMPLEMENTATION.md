# Phase 6 : Gestion des Modules - Implémentation

## 📋 Vue d'ensemble

La Phase 6 implémente un système complet de gestion des modules pour les tenants. Ce système permet d'activer/désactiver des modules par hôpital et de vérifier automatiquement qu'un module est activé avant d'autoriser l'accès aux routes.

## ✅ Composants Implémentés

### 1. ModuleService

**Fichier** : `app/Core/Services/ModuleService.php`

Service centralisé pour gérer les modules :

#### Méthodes principales :

- **`enableModule(Hospital $hospital, string $moduleName, array $config = [], ?int $adminId = null)`** : Active un module pour un hôpital
- **`disableModule(Hospital $hospital, string $moduleName)`** : Désactive un module
- **`isModuleEnabled(Hospital $hospital, string $moduleName)`** : Vérifie si un module est activé
- **`getEnabledModules(Hospital $hospital)`** : Récupère tous les modules activés
- **`getAvailableModules()`** : Récupère tous les modules disponibles dans l'application
- **`getModulesStatus(Hospital $hospital)`** : Récupère le statut de tous les modules
- **`enableModules(Hospital $hospital, array $moduleNames)`** : Active plusieurs modules
- **`disableModules(Hospital $hospital, array $moduleNames)`** : Désactive plusieurs modules
- **`updateModuleConfig(Hospital $hospital, string $moduleName, array $config)`** : Met à jour la configuration d'un module
- **`getModuleConfig(Hospital $hospital, string $moduleName)`** : Récupère la configuration d'un module

#### Cache

Le service utilise un cache pour optimiser les performances :
- Cache key : `hospital_modules:{hospital_id}`
- Durée : 1 heure (3600 secondes)
- Invalidation automatique lors des modifications

### 2. Middleware EnsureModuleEnabled

**Fichier** : `app/Http/Middleware/EnsureModuleEnabled.php`

Middleware pour vérifier qu'un module est activé avant d'autoriser l'accès aux routes.

#### Fonctionnalités :

- Vérifie automatiquement si le module est activé pour le tenant actuel
- Extrait le nom du module depuis la route si non spécifié
- Retourne une erreur 403 si le module n'est pas activé
- Enregistré dans `Kernel.php` avec l'alias `module`

#### Usage dans les routes :

```php
// Méthode 1 : Spécifier le module explicitement
Route::middleware(['tenant', 'module:Patient'])->get('/api/patients', ...);

// Méthode 2 : Le middleware détecte automatiquement depuis l'URL
Route::middleware(['tenant', 'module'])->prefix('api/patient')->group(function () {
    // Routes du module Patient
});
```

### 3. Commandes Artisan

#### `tenant:module:enable` - Activation de modules

**Fichier** : `app/Core/Console/Commands/TenantModuleEnableCommand.php`

Active un ou plusieurs modules pour un tenant.

**Usage** :
```bash
# Activer un seul module
php artisan tenant:module:enable 1 Patient

# Activer plusieurs modules
php artisan tenant:module:enable 1 "Patient,Stock,Cash"

# Activer tous les modules principaux
php artisan tenant:module:enable 1 "Acl,Administration,Patient,Payment,Stock"
```

#### `tenant:module:disable` - Désactivation de modules

**Fichier** : `app/Core/Console/Commands/TenantModuleDisableCommand.php`

Désactive un ou plusieurs modules pour un tenant.

**Usage** :
```bash
# Désactiver un module
php artisan tenant:module:disable 1 Stock

# Désactiver plusieurs modules
php artisan tenant:module:disable 1 "Stock,Cash"
```

**⚠️ Avertissement** : La commande demande confirmation avant de désactiver des modules critiques (Acl, Administration).

#### `tenant:module:list` - Liste des modules

**Fichier** : `app/Core/Console/Commands/TenantModuleListCommand.php`

Liste les modules disponibles et leur statut pour un ou plusieurs tenants.

**Usage** :
```bash
# Liste pour un hôpital spécifique
php artisan tenant:module:list 1

# Liste pour tous les hôpitaux
php artisan tenant:module:list
```

## 🔧 Utilisation dans les Routes

### Option 1 : Middleware explicite

Dans les fichiers de routes des modules (ex: `Modules/Patient/Routes/api.php`) :

```php
Route::middleware(['auth:api', 'module:Patient'])->group(function () {
    Route::apiResource('patients', PatientController::class);
    // ... autres routes
});
```

### Option 2 : Middleware automatique

Le middleware peut détecter automatiquement le module depuis l'URL :

```php
Route::middleware(['auth:api', 'module'])->prefix('api/patient')->group(function () {
    // Le middleware détecte automatiquement "Patient" depuis l'URL
    Route::apiResource('patients', PatientController::class);
});
```

### Option 3 : Dans RouteServiceProvider

Adapter le `RouteServiceProvider` du module :

```php
protected function mapApiRoutes(): void
{
    Route::prefix('api')
        ->middleware(['api', 'module:Patient']) // Ajouter le middleware module
        ->namespace($this->moduleNamespace)
        ->group(module_path('Patient', '/Routes/api.php'));
}
```

## 📊 Exemple de Sortie

### Liste des modules

```
╔══════════════════════════════════════════════════════════════╗
║  Modules de l'Hôpital                                       ║
╚══════════════════════════════════════════════════════════════╝

🏥 Hôpital : Hôpital Central de Casablanca (ID: 1)
   - Domaine : hopital-central.medkey.com

📦 Statut des Modules :
+-----------------+--------------+
| Module          | Statut       |
+-----------------+--------------+
| Acl             | ✅ Activé    |
| Administration  | ✅ Activé    |
| Patient         | ✅ Activé    |
| Payment         | ✅ Activé    |
| Stock           | ❌ Désactivé |
+-----------------+--------------+

📊 Résumé :
   - Modules disponibles : 18
   - Modules activés : 4
   - Modules désactivés : 14
```

## 🔄 Intégration avec le Provisioning

Le système de modules est intégré avec le provisioning (Phase 5) :

- Lors de la création d'un tenant avec `tenant:create --provision`, les modules par défaut sont automatiquement activés
- Les modules par défaut sont configurés dans `config/tenant.php` :

```php
'provisioning' => [
    'default_modules' => env('TENANT_DEFAULT_MODULES', 'Acl,Administration,Patient,Payment'),
],
```

## 🧪 Tests

### Test 1 : Lister les modules

```bash
php artisan tenant:module:list 1
```

### Test 2 : Activer un module

```bash
php artisan tenant:module:enable 1 Stock
php artisan tenant:module:list 1
```

### Test 3 : Désactiver un module

```bash
php artisan tenant:module:disable 1 Stock
php artisan tenant:module:list 1
```

### Test 4 : Vérifier l'accès via API

```bash
# Si le module Stock est désactivé, cette requête devrait retourner 403
curl -H "Host: hopital-central.medkey.com" \
     -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/stock/products
```

## ⚠️ Points d'Attention

1. **Modules critiques** : Les modules `Acl` et `Administration` sont considérés comme critiques. La désactivation demande confirmation.

2. **Cache** : Le cache des modules est invalidé automatiquement lors des modifications. En cas de problème, vous pouvez le vider manuellement :
   ```php
   Cache::forget('hospital_modules:' . $hospitalId);
   ```

3. **Détection automatique** : Le middleware tente de détecter le module depuis l'URL, mais il est recommandé de spécifier explicitement le module dans les routes pour plus de clarté.

4. **Modules non existants** : Si un module n'existe pas dans le dossier `Modules/`, il ne sera pas listé comme disponible, mais peut toujours être activé manuellement (utile pour les modules futurs).

## 📝 Exemples d'Utilisation

### Exemple 1 : Activer tous les modules principaux

```bash
php artisan tenant:module:enable 1 "Acl,Administration,Patient,Payment,Stock,Cash,Hospitalization"
```

### Exemple 2 : Désactiver un module temporairement

```bash
# Désactiver
php artisan tenant:module:disable 1 Stock

# ... maintenance ...

# Réactiver
php artisan tenant:module:enable 1 Stock
```

### Exemple 3 : Vérifier le statut de tous les hôpitaux

```bash
php artisan tenant:module:list
```

## ✅ Checklist de Validation

- [x] `ModuleService` créé avec toutes les méthodes
- [x] Middleware `EnsureModuleEnabled` créé et enregistré
- [x] Commande `tenant:module:enable` implémentée
- [x] Commande `tenant:module:disable` implémentée
- [x] Commande `tenant:module:list` implémentée
- [x] Commandes enregistrées dans `Kernel.php`
- [x] Cache implémenté pour les performances
- [x] Documentation complète créée
- [x] Tests de validation effectués

## 🎯 Prochaines Étapes

La Phase 6 est complète. Les prochaines étapes sont :

- **Phase 7** : Tests et Validation
  - Tests unitaires pour `ModuleService`
  - Tests unitaires pour le middleware
  - Tests d'intégration
  - Tests de performance

---

**Date de complétion** : 2025-01-XX
**Statut** : ✅ Complété
