# Module de Paramètres par Hôpital - Documentation Technique

## Vue d'ensemble

Ce document décrit l'implémentation complète du module de paramètres par hôpital (multi-tenant) pour la plateforme Medkey.

## 1. Architecture Backend

### 1.1 Structure de la base de données

#### Table `hospitals`
- `id` : Identifiant unique
- `name` : Nom de l'hôpital
- `domain` : Domaine unique (ex: hopital1.ma-plateforme.com)
- `slug` : Slug pour URL
- `status` : Statut (active, inactive, suspended)
- Autres champs : address, phone, email, logo, description, etc.

#### Table `hospital_settings`
- `id` : Identifiant unique
- `hospital_id` : Référence vers l'hôpital (foreign key, cascade delete)
- `key` : Clé du paramètre (ex: `logo`, `primary_color`, `modules_enabled`)
- `value` : Valeur du paramètre (peut être JSON pour des valeurs complexes)
- `type` : Type de la valeur (`string`, `integer`, `boolean`, `json`, `array`)
- `group` : Groupe du paramètre (`general`, `appearance`, `modules`, `security`, `medical`)
- `description` : Description du paramètre
- `is_public` : Si `true`, accessible sans authentification
- `created_at` / `updated_at` : Timestamps

**Contraintes** :
- Index unique sur `(hospital_id, key)` pour éviter les doublons
- Foreign key vers `hospitals.id` avec `onDelete('cascade')`

### 1.2 Modèles Eloquent

#### `Hospital` (`Modules\Administration\Entities\Hospital`)
- Utilise `BelongsToHospital` trait (Global Scope)
- Relations : `settings()`, `users()`, `patients()`
- Méthodes : `isActive()`, `isSuspended()`, `isInactive()`
- Scopes : `active()`, `byDomain()`

#### `HospitalSetting` (`Modules\Administration\Entities\HospitalSetting`)
- Utilise `BelongsToHospital` trait (Global Scope automatique)
- Accesseur/Mutateur pour casting automatique selon le type
- Scopes : `byGroup()`, `public()`
- Relation : `hospital()`

### 1.3 Service de gestion

#### `HospitalSettingsService` (`App\Services\HospitalSettingsService`)
Méthodes principales :
- `all(?int $hospitalId = null)` : Récupère tous les paramètres (avec cache)
- `get(string $key, $default = null, ?int $hospitalId = null)` : Récupère un paramètre
- `set(string $key, $value, ...)` : Crée ou met à jour un paramètre
- `delete(string $key, ?int $hospitalId = null)` : Supprime un paramètre
- `getGroup(string $group, ?int $hospitalId = null)` : Récupère les paramètres d'un groupe
- `getPublic(?int $hospitalId = null)` : Récupère les paramètres publics
- `getMany(array $keys, ?int $hospitalId = null)` : Récupère plusieurs paramètres
- `setMany(array $settings, ?int $hospitalId = null)` : Met à jour plusieurs paramètres
- `has(string $key, ?int $hospitalId = null)` : Vérifie si un paramètre existe
- `clearCache(?int $hospitalId = null)` : Invalide le cache

**Cache** : 60 minutes par défaut, clé `hospital_settings_{hospital_id}`

### 1.4 Contrôleur API

#### `HospitalSettingController` (`Modules\Administration\Http\Controllers\Api\V1\HospitalSettingController`)

**Endpoints** :

1. **GET `/api/v1/settings`** ou **GET `/api/v1/hospital-settings`**
   - Liste tous les paramètres de l'hôpital courant
   - Query param `?group=general` pour filtrer par groupe
   - Requiert authentification

2. **GET `/api/v1/settings/{key}`** ou **GET `/api/v1/hospital-settings/{key}`**
   - Récupère un paramètre spécifique
   - Requiert authentification

3. **POST `/api/v1/settings`** ou **POST `/api/v1/hospital-settings`**
   - Crée ou met à jour un paramètre
   - Body : `{ key, value, type?, group?, description?, is_public? }`
   - Requiert authentification

4. **PATCH `/api/v1/settings/{key}`** ou **PATCH `/api/v1/hospital-settings/{key}`**
   - Met à jour un paramètre spécifique
   - Body : `{ value, type?, group?, description?, is_public? }`
   - Requiert authentification

5. **PATCH `/api/v1/settings`** ou **PATCH `/api/v1/hospital-settings`**
   - Met à jour plusieurs paramètres en une seule fois
   - Body : `{ settings: [{ key, value, type?, group?, description?, is_public? }, ...] }`
   - Requiert authentification

6. **DELETE `/api/v1/settings/{key}`** ou **DELETE `/api/v1/hospital-settings/{key}`**
   - Supprime un paramètre
   - Requiert authentification

7. **GET `/api/v1/settings/group/{group}`** ou **GET `/api/v1/hospital-settings/group/{group}`**
   - Récupère les paramètres d'un groupe spécifique
   - Requiert authentification

8. **GET `/api/v1/hospital-settings/public`**
   - Récupère les paramètres publics (accessibles sans authentification)
   - Utilisé pour le branding initial du frontend

**Sécurité** :
- `hospital_id` est **TOUJOURS** injecté automatiquement par le service
- Le frontend ne doit **JAMAIS** envoyer `hospital_id`
- Vérification via `HospitalSettingPolicy` (Gate)
- Isolation multi-tenant garantie par le Global Scope

### 1.5 FormRequests

- `StoreHospitalSettingRequest` : Validation pour création/mise à jour
- `UpdateHospitalSettingRequest` : Validation pour mise à jour d'un paramètre
- `UpdateManyHospitalSettingsRequest` : Validation pour mise à jour multiple

**Règles de validation** :
- `key` : required, string, max:255
- `value` : required
- `type` : sometimes, in:string,integer,boolean,json,array
- `group` : sometimes, string, max:255
- `description` : sometimes, string, nullable, max:1000
- `is_public` : sometimes, boolean
- `hospital_id` : **prohibited** (injecté automatiquement)

### 1.6 Policy

#### `HospitalSettingPolicy` (`Modules\Administration\Policies\HospitalSettingPolicy`)
- Étend `BaseTenantPolicy`
- Méthodes : `viewAny()`, `view()`, `create()`, `update()`, `delete()`, `updateMany()`
- Vérifie que l'utilisateur appartient à l'hôpital courant

### 1.7 Global Scope

#### `HospitalScope` (`App\Scopes\HospitalScope`)
- Appliqué automatiquement via le trait `BelongsToHospital`
- Filtre toutes les requêtes par `hospital_id` du tenant courant
- Peut être désactivé avec `withoutGlobalScope(HospitalScope::class)`

### 1.8 Middleware et Helpers

#### `TenantMiddleware`
- Détecte l'hôpital courant depuis le domaine
- Stocke dans `app('hospital')` et `app('hospital_id')`
- Priorité : domaine → utilisateur authentifié → fallback (dev uniquement)

#### Helpers (`app/Helpers/tenant_helper.php`)
- `currentHospital()` : Récupère l'hôpital courant
- `currentHospitalId()` : Récupère l'ID de l'hôpital courant
- `hospitalSetting($key, $default)` : Récupère un paramètre
- `hospitalSettings()` : Récupère tous les paramètres
- `hospitalSettingsGroup($group)` : Récupère les paramètres d'un groupe
- `hospitalPublicSettings()` : Récupère les paramètres publics

## 2. Architecture Frontend

### 2.1 Service API

#### `settings.service.js` (`src/_services/settings.service.js`)
Méthodes :
- `getAll(group?)` : Récupère tous les paramètres
- `get(key)` : Récupère un paramètre spécifique
- `set(setting)` : Crée ou met à jour un paramètre
- `update(key, data)` : Met à jour un paramètre
- `updateMany(settings)` : Met à jour plusieurs paramètres
- `delete(key)` : Supprime un paramètre
- `getByGroup(group)` : Récupère les paramètres d'un groupe
- `getPublic()` : Récupère les paramètres publics

**Isolation multi-tenant** :
- Le frontend ne gère **JAMAIS** `hospital_id`
- Le backend injecte automatiquement `hospital_id` depuis le tenant détecté
- Le cache frontend est isolé par hôpital via `tenantStorage`

### 2.2 Composants Vue

#### `SettingsIndex.vue` (`src/pages/configuration/settings/index.vue`)
- Page principale avec onglets par groupe
- Groupes : Général, Apparence, Modules, Sécurité, Médical
- Charge les paramètres au montage
- Gère les mises à jour individuelles et groupées

#### `SettingsGroup.vue` (`src/pages/configuration/settings/SettingsGroup.vue`)
- Composant réutilisable pour afficher/éditer un groupe de paramètres
- Gère les différents types (string, integer, boolean, json, array)
- Formulaire avec validation
- Boutons Enregistrer/Réinitialiser

### 2.3 Routes

#### Route ajoutée dans `src/router/stock.js`
```javascript
{
  path: "/configuration/settings",
  component: layoutConfiguration,
  children: [{
    path: "index",
    name: "configuration.settings.index",
    component: () => import("@/pages/configuration/settings/index"),
  }],
}
```

**Accès** : `/configuration/settings/index`

## 3. Isolation Multi-Tenant

### 3.1 Backend

1. **Détection automatique** : `TenantMiddleware` détecte l'hôpital depuis le domaine
2. **Global Scope** : Toutes les requêtes sont filtrées par `hospital_id`
3. **Service** : `HospitalSettingsService` utilise toujours `currentHospitalId()`
4. **Policy** : Vérifie que l'utilisateur appartient à l'hôpital courant
5. **FormRequest** : Interdit explicitement `hospital_id` depuis le frontend

### 3.2 Frontend

1. **Pas de gestion de tenant** : Le frontend ne connaît pas `hospital_id`
2. **Cache isolé** : `tenantStorage` préfixe les clés avec `hospital_id`
3. **API automatique** : Le backend injecte `hospital_id` automatiquement

### 3.3 Tests recommandés

1. **Deux hôpitaux** : Créer deux hôpitaux avec des domaines différents
2. **Paramètres distincts** : Configurer des paramètres différents pour chaque hôpital
3. **Vérification** : S'assurer qu'un hôpital ne voit pas les paramètres de l'autre

## 4. Utilisation

### 4.1 Backend (dans un contrôleur/service)

```php
use App\Services\HospitalSettingsService;

// Récupérer un paramètre
$logo = app(HospitalSettingsService::class)->get('logo', '/default-logo.png');

// Définir un paramètre
app(HospitalSettingsService::class)->set(
    'primary_color',
    '#007bff',
    'string',
    'appearance',
    'Couleur principale de l\'interface',
    true // is_public
);

// Récupérer tous les paramètres d'un groupe
$appearanceSettings = app(HospitalSettingsService::class)->getGroup('appearance');

// Utiliser le helper
$logo = hospitalSetting('logo', '/default-logo.png');
```

### 4.2 Frontend (dans un composant Vue)

```javascript
import settingsService from "@/_services/settings.service";

// Récupérer tous les paramètres
const response = await settingsService.getAll();
const settings = response.data.data;

// Récupérer un paramètre spécifique
const logoResponse = await settingsService.get('logo');
const logo = logoResponse.data.data.value;

// Mettre à jour un paramètre
await settingsService.update('logo', {
  value: '/new-logo.png',
  type: 'string',
  group: 'appearance'
});

// Mettre à jour plusieurs paramètres
await settingsService.updateMany([
  { key: 'logo', value: '/new-logo.png', type: 'string', group: 'appearance' },
  { key: 'primary_color', value: '#007bff', type: 'string', group: 'appearance' }
]);
```

## 5. Exemples de paramètres

### 5.1 Paramètres généraux (`general`)
- `app_name` : Nom de l'application
- `contact_email` : Email de contact
- `contact_phone` : Téléphone de contact
- `address` : Adresse de l'hôpital

### 5.2 Paramètres d'apparence (`appearance`)
- `logo` : URL ou chemin vers le logo
- `primary_color` : Couleur principale (#007bff)
- `secondary_color` : Couleur secondaire (#6c757d)
- `favicon` : URL ou chemin vers le favicon

### 5.3 Paramètres de modules (`modules`)
- `modules_enabled` : Liste des modules activés (JSON array)
- `module_patient_enabled` : Boolean
- `module_pharmacy_enabled` : Boolean
- `module_payment_enabled` : Boolean

### 5.4 Paramètres de sécurité (`security`)
- `session_timeout` : Timeout de session en minutes (integer)
- `password_min_length` : Longueur minimale du mot de passe (integer)
- `two_factor_enabled` : Activation 2FA (boolean)

### 5.5 Paramètres médicaux (`medical`)
- `default_service_id` : ID du service par défaut (integer)
- `consultation_duration` : Durée par défaut d'une consultation en minutes (integer)
- `prescription_template` : Template de prescription (JSON)

## 6. Bonnes pratiques

1. **Ne jamais envoyer `hospital_id` depuis le frontend**
2. **Utiliser les groupes pour organiser les paramètres**
3. **Marquer les paramètres publics uniquement si nécessaire**
4. **Utiliser le cache pour améliorer les performances**
5. **Valider les types de paramètres côté backend**
6. **Documenter chaque paramètre avec une description**

## 7. Maintenance

### 7.1 Ajouter un nouveau paramètre

1. Créer une migration si nécessaire (structure déjà en place)
2. Ajouter le paramètre via l'interface ou directement en base
3. Documenter dans ce fichier

### 7.2 Invalider le cache

```php
app(HospitalSettingsService::class)->clearCache();
```

### 7.3 Migrer des paramètres entre hôpitaux

```php
$sourceHospitalId = 1;
$targetHospitalId = 2;

$settings = app(HospitalSettingsService::class)->all($sourceHospitalId);
foreach ($settings as $setting) {
    app(HospitalSettingsService::class)->set(
        $setting->key,
        $setting->value,
        $setting->type,
        $setting->group,
        $setting->description,
        $setting->is_public,
        $targetHospitalId
    );
}
```

## 8. Sécurité

1. **Isolation garantie** : Global Scope + Policy + Service
2. **Validation stricte** : FormRequests avec règles explicites
3. **Pas de fuite de données** : `hospital_id` jamais exposé au frontend
4. **Cache sécurisé** : Clés isolées par `hospital_id`
5. **Paramètres publics** : Limités aux données non sensibles (branding)

---

**Date de création** : 2025-01-15  
**Dernière mise à jour** : 2025-01-15  
**Auteur** : Équipe Medkey
