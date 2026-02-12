# Phase 2 : Adaptation du Middleware - Documentation

## ✅ État d'Avancement

**Phase 2 : COMPLÉTÉE** ✅

Tous les composants de la Phase 2 ont été créés et sont opérationnels.

---

## 📁 Modifications Effectuées

### 1. TenantMiddleware (Modifié) ✅

**Fichier** : `app/Http/Middleware/TenantMiddleware.php`

**Changements** :
- ✅ Utilise maintenant `App\Core\Models\Hospital` au lieu de `Modules\Administration\Entities\Hospital`
- ✅ Intègre `TenantConnectionService` pour basculer automatiquement la connexion DB
- ✅ Gère les erreurs de connexion avec `handleConnectionError()`
- ✅ Logs détaillés pour le débogage

**Fonctionnalités ajoutées** :
```php
// Après identification de l'hôpital, bascule automatique de la connexion DB
$tenantService = app(TenantConnectionService::class);
$tenantService->connect($hospital);
```

### 2. EnsureTenantConnection (Nouveau) ✅

**Fichier** : `app/Http/Middleware/EnsureTenantConnection.php`

**Rôle** :
- Vérifie que la connexion tenant est active avant de continuer
- Bloque l'accès si la connexion n'est pas établie
- Teste la validité de la connexion avec une requête simple

**Ordre d'exécution** :
1. `TenantMiddleware` : Identifie le tenant et bascule la connexion DB
2. `EnsureTenantConnection` : Vérifie que la connexion est active
3. Autres middlewares...

### 3. Kernel.php (Modifié) ✅

**Fichier** : `app/Http/Kernel.php`

**Changement** :
- Ajout de `EnsureTenantConnection` dans le groupe `api` après `TenantMiddleware`

**Ordre des middlewares** :
```php
'api' => [
    \App\Http\Middleware\HandleCors::class,              // 1. CORS
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // 2. Bindings
    \App\Http\Middleware\TenantMiddleware::class,         // 3. Identification tenant + bascule DB
    \App\Http\Middleware\EnsureTenantConnection::class,   // 4. Vérification connexion (PHASE 2)
    \App\Http\Middleware\EnsureUserBelongsToHospital::class, // 5. Vérification utilisateur
    \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class, // 6. Token API
],
```

---

## 🔄 Flux d'Exécution

### Requête API Entrante

```
1. Requête arrive
   ↓
2. HandleCors (gère OPTIONS)
   ↓
3. TenantMiddleware
   ├─ Identifie l'hôpital (domaine, header, etc.)
   ├─ Vérifie que l'hôpital est actif
   └─ BASCULE LA CONNEXION DB vers la base tenant ✅ NOUVEAU
   ↓
4. EnsureTenantConnection ✅ NOUVEAU
   ├─ Vérifie que la connexion tenant est active
   ├─ Teste la connexion avec getPdo()
   └─ Bloque si la connexion échoue
   ↓
5. EnsureUserBelongsToHospital
   └─ Vérifie que l'utilisateur appartient au bon hôpital
   ↓
6. Route handler
   └─ Utilise automatiquement la connexion tenant
```

---

## 🎯 Fonctionnalités Clés

### 1. Bascule Automatique de Connexion DB

Le `TenantMiddleware` bascule maintenant automatiquement la connexion DB vers la base du tenant :

```php
// Dans TenantMiddleware::handle()
$tenantService = app(TenantConnectionService::class);
$tenantService->connect($hospital);
```

**Résultat** :
- Tous les modèles utilisent automatiquement la connexion `tenant`
- Les requêtes Eloquent vont vers la bonne base de données
- Isolation complète des données entre hôpitaux

### 2. Vérification de Connexion

Le middleware `EnsureTenantConnection` vérifie que :
- La connexion tenant est active
- La connexion est valide (test avec `getPdo()`)
- Bloque l'accès si la connexion échoue

### 3. Gestion des Erreurs

**Erreurs de connexion** :
- En développement : Message détaillé avec le nom de la base et l'erreur
- En production : Message générique pour la sécurité

**Exemple de réponse d'erreur (dev)** :
```json
{
  "message": "Impossible de se connecter à la base de données de l'hôpital.",
  "hospital": "Hôpital Central",
  "database": "medkey_hopital_central",
  "error": "SQLSTATE[HY000] [1049] Unknown database 'medkey_hopital_central'",
  "hint": "Vérifiez que la base de données existe et est accessible."
}
```

---

## 🧪 Tests

### Test 1 : Vérifier la bascule de connexion

```php
// Dans tinker ou un contrôleur
$hospital = \App\Core\Models\Hospital::find(1);
$service = app(\App\Core\Services\TenantConnectionService::class);
$service->connect($hospital);

// Vérifier la connexion
$connection = $service->getCurrentConnection();
echo "Base de données: " . $connection->getDatabaseName();
```

### Test 2 : Vérifier que les modèles utilisent la bonne connexion

```php
// Après connexion au tenant
$user = \Modules\Acl\Entities\User::first();
echo "Connexion: " . $user->getConnectionName(); // Devrait retourner 'tenant'
```

### Test 3 : Tester une requête API

```bash
curl -X GET http://localhost:8000/api/v1/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Tenant-Domain: hopital-central.medkey.com"
```

---

## ⚠️ Points d'Attention

### 1. Routes Exclues

Les routes suivantes sont exclues de la détection tenant (pas de bascule DB) :
- `api/v1/login`
- `api/v1/register`
- `api/v1/request-password`
- `api/v1/reset-password`
- `api/v1/email-confirmation`

Ces routes utilisent la connexion par défaut (base principale).

### 2. Base de Données Tenant

**Important** : La base de données tenant doit exister avant de pouvoir se connecter.

Si la base n'existe pas :
1. Créez-la avec `php artisan tenant:migrate {hospital_id}`
2. Ou créez-la manuellement dans MySQL

### 3. Connexion CORE vs TENANT

- **CORE** : Utilisé pour `App\Core\Models\*` (Hospital, HospitalModule, SystemAdmin)
- **TENANT** : Utilisé pour tous les autres modèles (User, Patient, Payment, etc.)

---

## 📝 Prochaines Étapes

La Phase 2 est complète. Pour continuer :

1. **Phase 3** : Migration des données existantes
   - Créer les bases tenant pour chaque hôpital
   - Migrer les données filtrées par `hospital_id`
   - Supprimer les colonnes `hospital_id`

2. **Phase 4** : Adaptation des modèles
   - Supprimer le trait `BelongsToHospital`
   - Supprimer `HospitalScope`
   - Adapter les policies

---

## 🔧 Commandes Utiles

### Vérifier la connexion tenant active

```php
php artisan tinker
$service = app(\App\Core\Services\TenantConnectionService::class);
echo $service->isConnected() ? "Connecté" : "Non connecté";
```

### Lister les hôpitaux

```bash
php artisan tenant:list
```

### Créer un hôpital

```bash
php artisan hospital:create
```

### Migrer une base tenant

```bash
php artisan tenant:migrate {hospital_id}
```

---

**Date de complétion** : 2025-01-20  
**Version** : 1.0
