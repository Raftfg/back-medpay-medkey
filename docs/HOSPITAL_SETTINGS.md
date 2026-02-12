# Paramètres par Hôpital

## Vue d'ensemble

Ce document décrit le système de paramètres par hôpital, permettant à chaque hôpital (tenant) de personnaliser sa configuration (logo, couleurs, modules activés, etc.).

---

## 1. Structure de la base de données

### Table `hospital_settings`

La table `hospital_settings` stocke les paramètres de configuration pour chaque hôpital.

**Colonnes principales** :
- `id` : Identifiant unique
- `hospital_id` : Référence vers l'hôpital (foreign key)
- `key` : Clé du paramètre (ex: `logo`, `primary_color`, `modules_enabled`)
- `value` : Valeur du paramètre (peut être JSON pour des valeurs complexes)
- `type` : Type de la valeur (`string`, `integer`, `boolean`, `json`, `array`)
- `group` : Groupe du paramètre (`general`, `appearance`, `modules`, etc.)
- `description` : Description du paramètre
- `is_public` : Si `true`, accessible sans authentification
- `created_at` / `updated_at` : Timestamps

**Contraintes** :
- Index unique sur `(hospital_id, key)` pour éviter les doublons
- Foreign key vers `hospitals.id` avec `onDelete('cascade')`

---

## 2. Modèle Eloquent

### HospitalSetting

**Localisation** : `Modules/Administration/Entities/HospitalSetting.php`

**Fonctionnalités** :
- Cast automatique des valeurs selon le type
- Accesseur et mutateur pour gérer les types JSON/array
- Scopes : `byGroup()`, `public()`
- Relation vers `Hospital`

**Exemple d'utilisation** :
```php
use Modules\Administration\Entities\HospitalSetting;

// Créer un paramètre
$setting = HospitalSetting::create([
    'hospital_id' => 1,
    'key' => 'primary_color',
    'value' => '#007bff',
    'type' => 'string',
    'group' => 'appearance',
    'description' => 'Couleur principale de l\'interface',
    'is_public' => true,
]);

// Récupérer un paramètre
$color = HospitalSetting::where('hospital_id', 1)
    ->where('key', 'primary_color')
    ->first()
    ->value; // '#007bff'
```

---

## 3. Service HospitalSettingsService

### Vue d'ensemble

Le service `HospitalSettingsService` centralise la gestion des paramètres avec mise en cache.

**Localisation** : `app/Services/HospitalSettingsService.php`

### Méthodes principales

#### `all(?int $hospitalId = null): Collection`

Récupère tous les paramètres de l'hôpital courant (ou spécifié).

```php
$settings = app(HospitalSettingsService::class)->all();
// Retourne une Collection keyBy('key')
```

#### `get(string $key, $default = null, ?int $hospitalId = null)`

Récupère un paramètre spécifique.

```php
$logo = app(HospitalSettingsService::class)->get('logo', '/default-logo.png');
```

#### `set(string $key, $value, string $type = 'string', ...): HospitalSetting`

Définit un paramètre (crée ou met à jour).

```php
$setting = app(HospitalSettingsService::class)->set(
    'primary_color',
    '#007bff',
    'string',
    'appearance',
    'Couleur principale',
    true // is_public
);
```

#### `delete(string $key, ?int $hospitalId = null): bool`

Supprime un paramètre.

```php
app(HospitalSettingsService::class)->delete('old_setting');
```

#### `getGroup(string $group, ?int $hospitalId = null): Collection`

Récupère tous les paramètres d'un groupe.

```php
$appearanceSettings = app(HospitalSettingsService::class)->getGroup('appearance');
```

#### `getPublic(?int $hospitalId = null): Collection`

Récupère les paramètres publics (accessibles sans authentification).

```php
$publicSettings = app(HospitalSettingsService::class)->getPublic();
```

#### `setMany(array $settings, ?int $hospitalId = null): void`

Définit plusieurs paramètres en une seule fois.

```php
app(HospitalSettingsService::class)->setMany([
    'logo' => ['value' => '/logo.png', 'type' => 'string', 'group' => 'appearance'],
    'primary_color' => ['value' => '#007bff', 'type' => 'string', 'group' => 'appearance'],
]);
```

### Mise en cache

Les paramètres sont mis en cache pendant 60 minutes par défaut. Le cache est automatiquement invalidé lors de la modification d'un paramètre.

---

## 4. Helpers globaux

Des fonctions helper sont disponibles pour faciliter l'accès aux paramètres :

### `hospitalSetting(string $key, $default = null)`

Récupère un paramètre de l'hôpital courant.

```php
$logo = hospitalSetting('logo', '/default-logo.png');
$primaryColor = hospitalSetting('primary_color', '#007bff');
```

### `hospitalSettings(): Collection`

Récupère tous les paramètres de l'hôpital courant.

```php
$allSettings = hospitalSettings();
```

### `hospitalSettingsGroup(string $group): Collection`

Récupère les paramètres d'un groupe.

```php
$appearance = hospitalSettingsGroup('appearance');
```

### `hospitalPublicSettings(): Collection`

Récupère les paramètres publics.

```php
$public = hospitalPublicSettings();
```

---

## 5. API REST

### Contrôleur

**Localisation** : `Modules/Administration/Http/Controllers/Api/V1/HospitalSettingController.php`

### Routes

```php
// Dans routes/api.php ou le fichier de routes du module Administration
Route::prefix('hospital-settings')->middleware(['auth:api', 'ensure.user.hospital'])->group(function () {
    Route::get('/', [HospitalSettingController::class, 'index']); // Liste tous les paramètres
    Route::get('/public', [HospitalSettingController::class, 'public']); // Paramètres publics (sans auth)
    Route::get('/group/{group}', [HospitalSettingController::class, 'group']); // Par groupe
    Route::get('/{key}', [HospitalSettingController::class, 'show']); // Un paramètre
    Route::post('/', [HospitalSettingController::class, 'store']); // Créer/mettre à jour
    Route::post('/bulk', [HospitalSettingController::class, 'updateMany']); // Mettre à jour plusieurs
    Route::delete('/{key}', [HospitalSettingController::class, 'destroy']); // Supprimer
});
```

### Exemples de requêtes

#### Récupérer tous les paramètres
```http
GET /api/hospital-settings
Authorization: Bearer {token}
```

#### Récupérer un paramètre spécifique
```http
GET /api/hospital-settings/logo
Authorization: Bearer {token}
```

#### Récupérer les paramètres publics (sans authentification)
```http
GET /api/hospital-settings/public
```

#### Créer ou mettre à jour un paramètre
```http
POST /api/hospital-settings
Authorization: Bearer {token}
Content-Type: application/json

{
    "key": "primary_color",
    "value": "#007bff",
    "type": "string",
    "group": "appearance",
    "description": "Couleur principale",
    "is_public": true
}
```

#### Mettre à jour plusieurs paramètres
```http
POST /api/hospital-settings/bulk
Authorization: Bearer {token}
Content-Type: application/json

{
    "settings": [
        {
            "key": "logo",
            "value": "/logo.png",
            "type": "string",
            "group": "appearance"
        },
        {
            "key": "primary_color",
            "value": "#007bff",
            "type": "string",
            "group": "appearance"
        }
    ]
}
```

---

## 6. Exemples d'utilisation

### Logo de l'hôpital

```php
// Définir le logo
hospitalSetting('logo', '/storage/hospitals/1/logo.png');

// Récupérer le logo
$logo = hospitalSetting('logo', '/default-logo.png');
```

### Couleurs personnalisées

```php
// Définir les couleurs
app(HospitalSettingsService::class)->setMany([
    'primary_color' => [
        'value' => '#007bff',
        'type' => 'string',
        'group' => 'appearance',
        'description' => 'Couleur principale',
        'is_public' => true,
    ],
    'secondary_color' => [
        'value' => '#6c757d',
        'type' => 'string',
        'group' => 'appearance',
        'description' => 'Couleur secondaire',
        'is_public' => true,
    ],
]);

// Récupérer les couleurs
$primaryColor = hospitalSetting('primary_color', '#007bff');
$secondaryColor = hospitalSetting('secondary_color', '#6c757d');
```

### Modules activés

```php
// Définir les modules activés
app(HospitalSettingsService::class)->set(
    'modules_enabled',
    ['appointment', 'billing', 'inventory'],
    'json',
    'modules',
    'Liste des modules activés pour cet hôpital'
);

// Récupérer les modules activés
$modules = hospitalSetting('modules_enabled', []);
if (in_array('appointment', $modules)) {
    // Le module rendez-vous est activé
}
```

### Paramètres complexes (JSON)

```php
// Définir un paramètre complexe
$config = [
    'timezone' => 'Africa/Abidjan',
    'date_format' => 'd/m/Y',
    'time_format' => 'H:i',
    'currency' => 'XOF',
    'language' => 'fr',
];

app(HospitalSettingsService::class)->set(
    'localization',
    $config,
    'json',
    'general',
    'Paramètres de localisation'
);

// Récupérer le paramètre
$localization = hospitalSetting('localization', []);
$timezone = $localization['timezone'] ?? 'UTC';
```

---

## 7. Groupes de paramètres recommandés

### `appearance`
Paramètres d'apparence de l'interface :
- `logo` : Logo de l'hôpital
- `primary_color` : Couleur principale
- `secondary_color` : Couleur secondaire
- `favicon` : Favicon
- `theme` : Thème (light/dark)

### `modules`
Modules activés/désactivés :
- `modules_enabled` : Liste des modules activés (JSON array)

### `general`
Paramètres généraux :
- `timezone` : Fuseau horaire
- `date_format` : Format de date
- `time_format` : Format d'heure
- `currency` : Devise
- `language` : Langue

### `notifications`
Paramètres de notifications :
- `email_notifications_enabled` : Notifications email activées
- `sms_notifications_enabled` : Notifications SMS activées
- `notification_email` : Email pour les notifications

### `billing`
Paramètres de facturation :
- `tax_rate` : Taux de taxe
- `invoice_prefix` : Préfixe des factures
- `payment_methods` : Méthodes de paiement acceptées (JSON array)

---

## 8. Bonnes pratiques

1. **Utilisez des groupes** : Organisez les paramètres par groupe pour faciliter la gestion
2. **Types appropriés** : Utilisez le bon type (`string`, `integer`, `boolean`, `json`) pour faciliter le cast
3. **Paramètres publics** : Marquez comme `is_public = true` uniquement les paramètres qui peuvent être exposés sans authentification (logo, couleurs, etc.)
4. **Descriptions** : Ajoutez des descriptions pour faciliter la maintenance
5. **Valeurs par défaut** : Toujours fournir une valeur par défaut lors de la récupération
6. **Cache** : Le service gère automatiquement le cache, mais vous pouvez le vider manuellement si nécessaire

---

## 9. Migration et Seeder

### Migration

La migration est déjà créée : `2025_01_15_100012_create_hospital_settings_table.php`

Pour l'exécuter :
```bash
php artisan migrate
```

### Seeder exemple

```php
use Modules\Administration\Entities\Hospital;
use App\Services\HospitalSettingsService;

// Dans un seeder
$hospital = Hospital::first();
$settingsService = app(HospitalSettingsService::class);

// Paramètres d'apparence
$settingsService->setMany([
    'logo' => [
        'value' => '/storage/hospitals/default-logo.png',
        'type' => 'string',
        'group' => 'appearance',
        'description' => 'Logo de l\'hôpital',
        'is_public' => true,
    ],
    'primary_color' => [
        'value' => '#007bff',
        'type' => 'string',
        'group' => 'appearance',
        'description' => 'Couleur principale',
        'is_public' => true,
    ],
], $hospital->id);
```

---

## 10. Intégration Frontend (Vue.js)

### Exemple de composant Vue.js

```vue
<template>
  <div>
    <img :src="logo" alt="Logo" />
    <div :style="{ color: primaryColor }">
      Contenu avec couleur personnalisée
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logo = ref('/default-logo.png');
const primaryColor = ref('#007bff');

onMounted(async () => {
  try {
    // Récupérer les paramètres publics
    const response = await axios.get('/api/hospital-settings/public');
    const settings = response.data.data;
    
    logo.value = settings.logo || '/default-logo.png';
    primaryColor.value = settings.primary_color || '#007bff';
  } catch (error) {
    console.error('Erreur lors du chargement des paramètres', error);
  }
});
</script>
```

---

## 11. Troubleshooting

### Problème : Les paramètres ne sont pas mis à jour

**Solution** : Vérifiez que le cache est bien invalidé. Vous pouvez le vider manuellement :
```php
app(HospitalSettingsService::class)->clearCache();
```

### Problème : Erreur "Aucun hôpital défini"

**Solution** : Assurez-vous que le middleware `TenantMiddleware` est bien exécuté avant d'accéder aux paramètres.

### Problème : Les paramètres JSON ne sont pas décodés

**Solution** : Vérifiez que le type est bien défini à `json` ou `array` lors de la création.

---

## 12. Évolutions futures

- [ ] Interface d'administration pour gérer les paramètres
- [ ] Validation des paramètres selon leur type
- [ ] Historique des modifications
- [ ] Import/Export des paramètres
- [ ] Templates de paramètres par défaut
